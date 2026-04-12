<?php
//* ===============================================
//# パフォーマンス最適化
//* ===============================================
//
// 【このファイルの特徴】
// PageSpeed Insightsでの高スコア獲得やCore Web Vitals対策を目指した表示速度改善機能をまとめています。
//
// 1. メインビジュアル Preload / 画像最適化 / 不要機能削除（旧 speed.php）
//    - LCP改善のためにMVP画像をPreloadし､未使用のWordPressコア機能（emoji等）を無効化。
// 2. Critical CSS インライン出力（旧 critical-css.php）
//    - レンダリングブロックを防ぐため､クリティカルCSSをhead内に展開。
// 3. リソースヒント dns-prefetch / preconnect / prefetch（旧 resource-hints.php）
//    - 外部ドメイン（Google Fonts等）への事前接続を行いロード時間を短縮。
// 4. Web Vitals 計測 LCP / CLS / INP（旧 web-vitals.php）
//    - ユーザーが体感するパフォーマンス指標をコンソール等で計測。
// 5. Google Fonts 最速読み込み（旧 fonts.php）
//    - フォントファイルの非同期読み込みに最適化。
//

//* ===============================================
//# 1. メインビジュアル Preload（LCP 改善）
//* ===============================================

function helixia_preload_front_page_mv()
{
    // トップページ以外では何もしない
    if (!is_front_page()) {
        return;
    }

    // front-page.php で定義したグローバル変数を呼び出す
    global $my_mv_image_path;
    global $my_mv_image_path_sp;

    if (empty($my_mv_image_path)) {
        return;
    }

    // PC用画像のPreload（768px以上）
    $pc_url = get_theme_file_uri($my_mv_image_path);
    echo "\n";
    echo '<link rel="preload" as="image" href="' . esc_url($pc_url) . '" media="(min-width: 768px)" fetchpriority="high">' . "\n";

    // SP用画像のPreload（767px以下）
    if (!empty($my_mv_image_path_sp)) {
        $sp_url = get_theme_file_uri($my_mv_image_path_sp);
        echo '<link rel="preload" as="image" href="' . esc_url($sp_url) . '" media="(max-width: 767px)" fetchpriority="high">' . "\n";
    }
}
add_action('wp_head', 'helixia_preload_front_page_mv', 1);

//* ===============================================
//# 画像の最適化（WebP変換・Lazy Load調整・非同期デコード）
//* ===============================================

// アップロードした画像を自動的にWebP形式で保存する
function helixia_upload_image_as_webp($default_mime_type, $attachment_ext)
{
    if ('jpg' === $attachment_ext || 'jpeg' === $attachment_ext || 'png' === $attachment_ext) {
        return 'image/webp';
    }
    return $default_mime_type;
}
add_filter('image_editor_default_mime_type', 'helixia_upload_image_as_webp', 10, 2);


// 先頭の画像（ファーストビュー）のLazy Loadを解除してLCPスコアを改善する
function helixia_adjust_lazy_load_threshold()
{
    return 2; // 先頭から2枚の画像には loading="lazy" を付けない
}
add_filter('wp_omit_loading_attr_threshold', 'helixia_adjust_lazy_load_threshold');


// すべての画像に decoding="async" を付与してブラウザの描画ストップを防ぐ
function helixia_add_async_decoding_to_img($attr)
{
    if (!isset($attr['decoding'])) {
        $attr['decoding'] = 'async';
    }
    return $attr;
}
add_filter('wp_get_attachment_image_attributes', 'helixia_add_async_decoding_to_img');

//* ===============================================
//# WordPressの不要なデフォルト機能の停止（HTML・通信の軽量化）
//* ===============================================

// 1. 絵文字（Emoji）用の不要なJSとCSSを削除
function helixia_disable_emojis()
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}
add_action('init', 'helixia_disable_emojis');

//oEmbed（埋め込み機能）のJSと<head>内の不要なリンクを完全削除
function helixia_disable_embeds()
{
    wp_deregister_script('wp-embed');
}
add_action('wp_footer', 'helixia_disable_embeds');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');

//グローバルスタイルやブロックCSSの最適化（必要なページ以外では削除）
function helixia_optimize_block_css()
{
    // 投稿(single)や固定ページ(page)などの個別ページ「以外」では不要なCSSを解除
    if (!is_singular()) {
        wp_dequeue_style('global-styles');
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('classic-theme-styles');
    }
}
add_action('wp_enqueue_scripts', 'helixia_optimize_block_css', 200);


function helixia_remove_extra_bloat()
{
    remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
    remove_action('in_admin_header', 'wp_global_styles_render_svg_filters');
}
add_action('init', 'helixia_remove_extra_bloat');


//* ===============================================
//# 2. Critical CSS インライン出力（FOUC回避型）
//* ===============================================

/**
 * css/critical.css をファイルから読み込み､<style> タグとして
 * <head> 内にインライン出力する。
 *
 * ※ フルCSS（style.css）は同期読み込みのまま維持するため､
 *   FOUCは発生しない。Critical CSSが先にあることでFCPが改善される。
 */
function helixia_inline_critical_css()
{
    // 定数による切り替え判定
    $is_enabled = HELIXIA_CRITICAL_CSS;

    // 定数が null の場合は環境を見て自動判定 (デバッグモードなら OFF)
    if (null === $is_enabled) {
        $env_type = function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'production';

        // ホスト名から開発環境か判定 (.local や localhost)
        $is_dev_host = isset($_SERVER['HTTP_HOST']) && (
            strpos($_SERVER['HTTP_HOST'], 'localhost') !== false ||
            strpos($_SERVER['HTTP_HOST'], '.local') !== false ||
            strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false ||
            strpos($_SERVER['HTTP_HOST'], '0.0.0.0') !== false
        );

        // 本番環境､かつデバッグモードが OFF､かつローカルホストでない場合のみ ON
        $is_enabled = ($env_type === 'production') && (!defined('WP_DEBUG') || !WP_DEBUG) && !$is_dev_host;
    }

    // デバッグ用のヒントを出力 (検証ツール > ページソース で確認可能)
    $debug_info = "Env:" . (function_exists('wp_get_environment_type') ? wp_get_environment_type() : 'N/A') . " / Debug:" . (defined('WP_DEBUG') && WP_DEBUG ? 'ON' : 'OFF') . " / Host:" . ($_SERVER['HTTP_HOST'] ?? 'Unknown');
    echo "<!-- Critical CSS: " . ($is_enabled ? "ON" : "OFF") . " (" . $debug_info . ") -->\n";

    if (!$is_enabled) {
        return;
    }

    $critical_path = get_theme_file_path('/css/critical.css');

    // ファイルが存在しない場合は何もしない（グレースフル）
    if (!file_exists($critical_path)) {
        return;
    }

    $critical_css = file_get_contents($critical_path);

    // 空ファイルなら出力しない
    if (empty(trim($critical_css))) {
        return;
    }

    // ソースマップ参照を除去（インラインには不要）
    $critical_css = preg_replace('/\/\*#\s*sourceMappingURL=.*?\*\//', '', $critical_css);

    echo '<style id="critical-css">' . "\n";
    echo $critical_css . "\n";
    echo '</style>' . "\n";
}
add_action('wp_head', 'helixia_inline_critical_css', 0);


//* ===============================================
//# 3. リソースヒント（dns-prefetch / preconnect / prefetch）
//* ===============================================

// dns-prefetch / preconnect（外部CDNへの事前接続）
function helixia_resource_hints()
{
    // Swiper CDN（トップページでのみ使用）
    if (is_front_page() || is_home()) {
        echo '<link rel="dns-prefetch" href="//cdn.jsdelivr.net">' . "\n";
        echo '<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>' . "\n";
    }
}
add_action('wp_head', 'helixia_resource_hints', 0);


// prefetch（次のページ遷移を先読み）
function helixia_prefetch_navigation()
{
    // 検索ページ・404・管理画面では不要
    if (is_search() || is_404() || is_admin()) {
        return;
    }

    $prefetch_urls = array();

    // トップページではよくアクセスされるページを先読み
    if (is_front_page() || is_home()) {
        // お問い合わせページ
        $contact = get_page_by_path('contact');
        if ($contact) {
            $prefetch_urls[] = get_permalink($contact->ID);
        }
    }

    // 個別記事ではアーカイブ（一覧）ページを先読み
    if (is_singular('post')) {
        $archive_url = get_post_type_archive_link('post');
        if ($archive_url) {
            $prefetch_urls[] = $archive_url;
        }
    }

    // 重複排除して出力
    $prefetch_urls = array_unique($prefetch_urls);
    foreach ($prefetch_urls as $url) {
        echo '<link rel="prefetch" href="' . esc_url($url) . '">' . "\n";
    }
}
add_action('wp_head', 'helixia_prefetch_navigation', 99);


//* ===============================================
//# 4. Web Vitals 計測（LCP / CLS / INP → GA4送信）
//* ===============================================

function helixia_web_vitals()
{
    // 管理者除外チェック
    if (get_theme_mod('helixia_analytics_exclude_admin', true) && current_user_can('manage_options')) {
        return;
    }

    // GA4 IDが設定されていなければ何もしない
    $ga4_id = get_theme_mod('helixia_ga4_id', '');
    $gtm_id = get_theme_mod('helixia_gtm_id', '');

    // GA4もGTMも設定されていなければスキップ
    if (empty($ga4_id) && empty($gtm_id)) {
        return;
    }
    ?>
    <!-- Web Vitals (LCP, CLS, INP) -->
    <script>
        (function () {
            'use strict';

            // gtag関数の存在チェック（GTM使用時はdataLayerに直接push）
            function sendToAnalytics(name, value) {
                var eventData = {
                    event_category: 'Web Vitals',
                    event_label: name,
                    value: Math.round(name === 'CLS' ? value * 1000 : value),
                    non_interaction: true
                };

                if (typeof gtag === 'function') {
                    gtag('event', 'web_vitals', eventData);
                } else if (window.dataLayer) {
                    eventData.event = 'web_vitals';
                    eventData.metric_name = name;
                    eventData.metric_value = value;
                    window.dataLayer.push(eventData);
                }
            }

            // ブラウザが暇な時に実行（速度影響ゼロ）
            var idle = window.requestIdleCallback || function (cb) { setTimeout(cb, 100); };

            idle(function () {
                // LCP（Largest Contentful Paint）
                try {
                    new PerformanceObserver(function (list) {
                        var entries = list.getEntries();
                        var last = entries[entries.length - 1];
                        if (last) sendToAnalytics('LCP', last.startTime);
                    }).observe({ type: 'largest-contentful-paint', buffered: true });
                } catch (e) { }

                // CLS（Cumulative Layout Shift）
                try {
                    var clsValue = 0;
                    new PerformanceObserver(function (list) {
                        list.getEntries().forEach(function (entry) {
                            if (!entry.hadRecentInput) {
                                clsValue += entry.value;
                            }
                        });
                    }).observe({ type: 'layout-shift', buffered: true });

                    // ページ離脱時にCLSを送信
                    addEventListener('visibilitychange', function () {
                        if (document.visibilityState === 'hidden') {
                            sendToAnalytics('CLS', clsValue);
                        }
                    }, { once: true });
                } catch (e) { }

                // INP（Interaction to Next Paint）
                try {
                    var inpValue = 0;
                    new PerformanceObserver(function (list) {
                        list.getEntries().forEach(function (entry) {
                            if (entry.duration > inpValue) {
                                inpValue = entry.duration;
                            }
                        });
                    }).observe({ type: 'event', buffered: true, durationThreshold: 40 });

                    addEventListener('visibilitychange', function () {
                        if (document.visibilityState === 'hidden' && inpValue > 0) {
                            sendToAnalytics('INP', inpValue);
                        }
                    }, { once: true });
                } catch (e) { }
            });
        })();
    </script>
    <?php
}
add_action('wp_footer', 'helixia_web_vitals', 99);


//* ===============================================
//# 5. Google Fonts 最速読み込み
//* ===============================================

function helixia_optimized_google_fonts()
{
    // 1. preconnect と dns-prefetch
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    echo '<link rel="dns-prefetch" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="dns-prefetch" href="https://fonts.gstatic.com">' . "\n";
    echo '<link rel="preconnect" href="https://cdn.jsdelivr.net">' . "\n";

    // 2. 読み込みたいフォントを配列で登録
    $fonts = array(
        'Noto+Sans+JP:wght@100..900', // Font A
        'Ysabeau+SC:wght@1..1000',      // Font B
        'Sacramento',      // Font B
    );

    // 3. フォント配列を「&family=」で合体させて､1つのURLを生成
    $base_url = 'https://fonts.googleapis.com/css2?family=';
    $joined_fonts = implode('&family=', $fonts);
    $font_url = $base_url . $joined_fonts . '&display=swap';

    // 4. printハックを利用した非同期読み込み
    echo '<link rel="stylesheet" href="' . esc_url($font_url) . '" media="print" onload="this.media=\'all\'">' . "\n";

    // 5. JavaScriptが無効な環境向けのフォールバック
    echo '<noscript>' . "\n";
    echo '<link rel="stylesheet" href="' . esc_url($font_url) . '">' . "\n";
    echo '</noscript>' . "\n";
}
add_action('wp_head', 'helixia_optimized_google_fonts', 1);
