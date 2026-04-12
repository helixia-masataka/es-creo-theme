<?php
//* ===============================================
//# アクセス解析（GA4 / GTM / イベント計測）
//* ===============================================
//
// 【このファイルの特徴】
// GA4 や GTM などのトラッキングコードを管理し､ユーザーの行動分析を容易にします。
//
// 1. WordPress カスタマイザーからGA4 / GTM IDをノーコード設定
// 2. GA4 / GTM の排他制御（二重出力防止）
// 3. 管理者ログイン中のトラッキング除外（データ汚染防止）
// 4. GTM の noscript タグを wp_body_open で自動出力
// 5. スクロール深度（読了率）と特定のCTAボタンのクリックイベントをGA4に送信
//
//      GA4 測定ID（G-XXXXXXXXXX）や GTM コンテナID（GTM-XXXXXXX）を入力するだけ
//
// 2. GA4 / GTM の排他制御
//    - GTM が設定されている場合は GA4 の直接出力を自動停止（GTM内で管理する前提）
//    - 両方入力されても二重出力にならない設計
//
// 3. 管理者ログイン中のトラッキング除外
//    - 「管理者除外」チェックボックスをON（デフォルト）にすると
//      管理者ログイン中はトラッキングコードを出力しない → 計測データ汚染を防ぐ
//
// 4. GTM の noscript タグを wp_body_open で出力
//    - GTM 仕様に沿って <body> 直後に <noscript> タグを自動挿入
//
// 【設定方法】管理画面 → 外観 → カスタマイズ → アナリティクス設定




// ─── カスタマイザー設定の登録 ───
function helixia_analytics_customizer($wp_customize)
{
    // セクション追加
    $wp_customize->add_section('helixia_analytics', array(
        'title' => 'アナリティクス設定',
        'priority' => 200,
    ));

    // --- GA4 測定ID ---
    $wp_customize->add_setting('helixia_ga4_id', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('helixia_ga4_id', array(
        'label' => 'GA4 測定ID',
        'description' => '例: G-XXXXXXXXXX',
        'section' => 'helixia_analytics',
        'type' => 'text',
    ));

    // --- GTM コンテナID ---
    $wp_customize->add_setting('helixia_gtm_id', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('helixia_gtm_id', array(
        'label' => 'GTM コンテナID',
        'description' => '例: GTM-XXXXXXX（GA4と併用する場合はGTM側で管理するため､GA4 IDは空にしてください）',
        'section' => 'helixia_analytics',
        'type' => 'text',
    ));

    // --- 管理者除外オプション ---
    $wp_customize->add_setting('helixia_analytics_exclude_admin', array(
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('helixia_analytics_exclude_admin', array(
        'label' => '管理者ログイン中は計測しない',
        'section' => 'helixia_analytics',
        'type' => 'checkbox',
    ));
}
add_action('customize_register', 'helixia_analytics_customizer');


// ─── GA4 スクリプト出力（<head>内） ───
function helixia_output_ga4()
{
    // 管理者除外チェック
    if (get_theme_mod('helixia_analytics_exclude_admin', true) && current_user_can('manage_options')) {
        return;
    }

    $ga4_id = get_theme_mod('helixia_ga4_id', '');
    $gtm_id = get_theme_mod('helixia_gtm_id', '');

    // GTMが設定されている場合はGTMを優先（GA4はGTM内で管理）
    if (!empty($gtm_id)) {
        return;
    }

    if (empty($ga4_id)) {
        return;
    }

    $ga4_id_escaped = esc_attr($ga4_id);
    ?>
    <!-- Google Analytics (GA4) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $ga4_id_escaped; ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
        gtag('js', new Date());
        gtag('config', '<?php echo $ga4_id_escaped; ?>');
    </script>
    <?php
}
add_action('wp_head', 'helixia_output_ga4', 1);


// ─── GTM スクリプト出力（<head>内） ───
function helixia_output_gtm_head()
{
    if (get_theme_mod('helixia_analytics_exclude_admin', true) && current_user_can('manage_options')) {
        return;
    }

    $gtm_id = get_theme_mod('helixia_gtm_id', '');
    if (empty($gtm_id)) {
        return;
    }

    $gtm_id_escaped = esc_attr($gtm_id);
    ?>
    <!-- Google Tag Manager -->
    <script>(function (w, d, s, l, i) {
            w[l] = w[l] || []; w[l].push({
                'gtm.start':
                    new Date().getTime(), event: 'gtm.js'
            }); var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s), dl = l != 'dataLayer' ? '&l=' + l : ''; j.async = true; j.src =
                    'https://www.googletagmanager.com/gtm.js?id=' + i + dl; f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', '<?php echo $gtm_id_escaped; ?>');</script>
    <!-- End Google Tag Manager -->
    <?php
}
add_action('wp_head', 'helixia_output_gtm_head', 1);


// ─── GTM noscript 出力（<body>直後） ───
function helixia_output_gtm_body()
{
    if (get_theme_mod('helixia_analytics_exclude_admin', true) && current_user_can('manage_options')) {
        return;
    }

    $gtm_id = get_theme_mod('helixia_gtm_id', '');
    if (empty($gtm_id)) {
        return;
    }

    $gtm_id_escaped = esc_attr($gtm_id);
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $gtm_id_escaped; ?>" height="0" width="0"
            style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
}
add_action('wp_body_open', 'helixia_output_gtm_body', 1);


//* ===============================================
//# 2. GA4 イベント計測（スクロール深度 + CTAクリック）
//* ===============================================

/**
 * スクロール深度 + CTA クリック計測のJSを出力
 * GA4/GTM 設定に依存
 */
function helixia_tracking_scripts()
{
    // 管理者除外チェック（analytics.php と同じ判定）
    if (get_theme_mod('helixia_exclude_admin', true) && current_user_can('manage_options')) {
        return;
    }

    $ga4_id = get_theme_mod('helixia_ga4_id', '');
    $gtm_id = get_theme_mod('helixia_gtm_id', '');

    // GA4 も GTM も未設定なら何も出力しない
    if (empty($ga4_id) && empty($gtm_id)) {
        return;
    }

    $use_gtm = !empty($gtm_id);
    ?>

    <script>
        (function () {
            // --- 共通: イベント送信関数 ---
            function sendEvent(eventName, params) {
                <?php if ($use_gtm): ?>
                    // GTM 経由
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push(Object.assign({ event: eventName }, params));
                <?php else: ?>
                    // GA4 直接
                    if (typeof gtag === 'function') {
                        gtag('event', eventName, params);
                    }
                <?php endif; ?>
            }

            // --- 1. スクロール深度トラッキング ---
            var idle = window.requestIdleCallback || function (cb) { setTimeout(cb, 100); };

            idle(function () {
                var thresholds = [25, 50, 75, 100];
                var fired = {};
                var ticking = false;

                function checkScroll() {
                    var h = document.documentElement;
                    var scrollTop = window.pageYOffset || h.scrollTop;
                    var scrollHeight = h.scrollHeight - h.clientHeight;
                    if (scrollHeight <= 0) return;

                    var pct = Math.round((scrollTop / scrollHeight) * 100);

                    for (var i = 0; i < thresholds.length; i++) {
                        var t = thresholds[i];
                        if (pct >= t && !fired[t]) {
                            fired[t] = true;
                            sendEvent('scroll_depth', {
                                depth_threshold: t,
                                page_path: location.pathname
                            });
                        }
                    }
                }

                window.addEventListener('scroll', function () {
                    if (!ticking) {
                        requestAnimationFrame(function () {
                            checkScroll();
                            ticking = false;
                        });
                        ticking = true;
                    }
                });

                // 初期チェック（短いページで既に100%の場合）
                checkScroll();
            });

            // --- 2. CTA クリック計測 ---
            document.addEventListener('click', function (e) {
                var el = e.target.closest('[data-track]');
                if (!el) return;

                var ctaName = el.getAttribute('data-track');
                var ctaUrl = el.getAttribute('href') || '';

                sendEvent('cta_click', {
                    cta_name: ctaName,
                    cta_url: ctaUrl,
                    page_path: location.pathname
                });
            });

        })();
    </script>

    <?php
}
add_action('wp_footer', 'helixia_tracking_scripts', 30);
