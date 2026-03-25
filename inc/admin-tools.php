<?php
//* ===============================================
//# 管理画面の拡張（運用支援ツール）
//* ===============================================
//
// 【このファイルの特徴】
// 運用者の利便性を高めるための管理画面（ダッシュボード）側の機能拡張を担います。
//
// 1. 投稿一覧「文字数」カラム（ソート対応）
// 2. リビジョン数を制限（DB軽量化）
// 3. 下書きプレビュー共有リンク（ログイン不要URL発行）
// 4. 構造化データチェック表示（管理バーショートカット）
// 5. カテゴリーのカラー設定機能
// 6. ダッシュボードにお知らせウィジェットを追加
// 7. All-in-One WP Migration から不要なファイル（cache等）を除外設定
//
//
// 【詳細】
//
// 1. 投稿一覧に「文字数」カラムを追加（ソート対応）
//    - メディアサイトで記事のボリュームを一覧で確認できる
//
// 2. リビジョン数を5件に制限（DB軽量化）
//    - WordPress のデフォルト（無限）から制限して DB 肥大化を防ぐ
//
// 3. 下書きプレビュー共有リンク
//    - ログイン不要でプレビューできるURL（48時間有効トークン）
//    - クライアントへの確認依頼が楽になる
//
// 4. 構造化データチェック表示
//    - 管理バーに JSON-LD の出力状態をアイコンで表示（管理者のみ）
//
// 5. カテゴリーカラー
//    - カテゴリー編集画面にカラーピッカー追加
//    - helixia_category_color($cat_id) でカラーコードを取得


//* ===============================================
//# 1. 投稿一覧に「文字数」カラムを追加
//* ===============================================

// カラムヘッダーの追加
function helixia_add_word_count_column($columns)
{
    $columns['word_count'] = '文字数';
    return $columns;
}
add_filter('manage_posts_columns', 'helixia_add_word_count_column');

// カラム内容の出力
function helixia_word_count_column_content($column_name, $post_id)
{
    if ($column_name !== 'word_count') {
        return;
    }

    $content = get_post_field('post_content', $post_id);
    $content = wp_strip_all_tags($content);
    $count = mb_strlen($content, 'UTF-8');

    // 文字数に応じて色分け（SEO目安）
    $color = '#999';
    if ($count >= 3000) {
        $color = '#16a34a'; // 緑: 十分
    } elseif ($count >= 1500) {
        $color = '#2563eb'; // 青: OK
    } elseif ($count >= 500) {
        $color = '#d97706'; // 黄: 少なめ
    } else {
        $color = '#dc2626'; // 赤: 短すぎ
    }

    echo '<span style="color:' . esc_attr($color) . ';font-weight:bold">' . number_format($count) . '</span>';
}
add_action('manage_posts_custom_column', 'helixia_word_count_column_content', 10, 2);

// ソート対応
function helixia_word_count_sortable($columns)
{
    $columns['word_count'] = 'word_count';
    return $columns;
}
add_filter('manage_edit-post_sortable_columns', 'helixia_word_count_sortable');

function helixia_word_count_orderby($query)
{
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->get('orderby') === 'word_count') {
        $query->set('orderby', 'content');  // 近似ソート
    }
}
add_action('pre_get_posts', 'helixia_word_count_orderby');


//* ===============================================
//# 2. リビジョン数を5件に制限
//* ===============================================

if (!defined('WP_POST_REVISIONS')) {
    define('WP_POST_REVISIONS', 5);
}


//* ===============================================
//# 3. 下書きプレビュー共有リンク
//* ===============================================

/**
 * 下書き記事にログイン不要でアクセスできる共有リンクを生成する。
 * トークンは投稿メタに保存し、48時間で期限切れ。
 */

// 投稿編集画面にプレビューリンクを表示
function helixia_preview_link_meta_box()
{
    add_meta_box(
        'helixia_preview_link',
        'プレビュー共有リンク',
        'helixia_preview_link_meta_box_content',
        'post',
        'side',
        'low'
    );
}
add_action('add_meta_boxes', 'helixia_preview_link_meta_box');

function helixia_preview_link_meta_box_content($post)
{
    if ($post->post_status === 'publish') {
        echo '<p style="color:#999">公開済みの記事です。</p>';
        return;
    }

    // トークンを生成または取得
    $token = get_post_meta($post->ID, '_helixia_preview_token', true);
    $expires = get_post_meta($post->ID, '_helixia_preview_expires', true);

    if (empty($token) || (int)$expires < time()) {
        $token = wp_generate_password(32, false);
        $expires = time() + (48 * 3600); // 48時間
        update_post_meta($post->ID, '_helixia_preview_token', $token);
        update_post_meta($post->ID, '_helixia_preview_expires', $expires);
    }

    $preview_url = add_query_arg(array(
        'p'             => $post->ID,
        'preview'       => 'true',
        'preview_token' => $token,
    ), home_url('/'));

    $remaining = human_time_diff(time(), (int)$expires);

    echo '<p><input type="text" value="' . esc_url($preview_url) . '" readonly style="width:100%" onclick="this.select()"></p>';
    echo '<p style="color:#666;font-size:12px">有効期限: あと約' . esc_html($remaining) . '（48時間）</p>';
    echo '<p style="color:#666;font-size:12px">更新ボタンを押すとリンクが再生成されます。</p>';
}

// トークンによるプレビューアクセスを許可
function helixia_allow_preview_access($posts, $query)
{
    if (is_admin() || !$query->is_main_query()) {
        return $posts;
    }

    if (!isset($_GET['preview_token']) || !isset($_GET['p'])) {
        return $posts;
    }

    $post_id = absint($_GET['p']);
    $token = sanitize_text_field($_GET['preview_token']);

    $saved_token = get_post_meta($post_id, '_helixia_preview_token', true);
    $expires = get_post_meta($post_id, '_helixia_preview_expires', true);

    if ($token === $saved_token && (int)$expires > time()) {
        // トークンが有効 → 下書き記事を返す
        return array(get_post($post_id));
    }

    return $posts;
}
add_filter('the_posts', 'helixia_allow_preview_access', 10, 2);


//* ===============================================
//# 4. 構造化データチェック表示
//* ===============================================

/**
 * 管理バーに構造化データの出力状態を表示（管理者ログイン時のみ）
 */
function helixia_json_ld_admin_bar($wp_admin_bar)
{
    if (!current_user_can('manage_options') || is_admin()) {
        return;
    }

    $schemas = array();

    // 現在のページで出力されるスキーマタイプを判定
    if (is_front_page() || is_home()) {
        $schemas[] = 'WebSite';
        $schemas[] = 'Organization';
    }

    if (is_singular()) {
        $schemas[] = is_single() ? 'Article' : 'WebPage';

        // FAQスキーマ（details ブロックがあれば）
        global $post;
        if ($post && preg_match('/<details/', $post->post_content)) {
            $schemas[] = 'FAQPage';
        }

        // パンくず
        if (!is_front_page()) {
            $schemas[] = 'BreadcrumbList';
        }
    }

    $label = empty($schemas) ? '⚠️ JSON-LD: なし' : '✅ JSON-LD: ' . implode(', ', $schemas);

    $wp_admin_bar->add_node(array(
        'id'    => 'helixia-jsonld-check',
        'title' => $label,
        'href'  => 'https://search.google.com/test/rich-results?url=' . urlencode(home_url($_SERVER['REQUEST_URI'])),
        'meta'  => array(
            'target' => '_blank',
            'title'  => 'Google リッチリザルトテストで確認',
        ),
    ));
}
add_action('admin_bar_menu', 'helixia_json_ld_admin_bar', 999);


//* ===============================================
//# 5. カテゴリーカラー
//* ===============================================

/**
 * カテゴリー編集画面にカラーピッカーを追加し、
 * カテゴリーごとのテーマカラーを設定可能にする。
 */

// カテゴリー追加画面のフィールド
function helixia_category_color_add_field()
{
    echo '<div class="form-field">';
    echo '<label for="helixia_cat_color">カテゴリーカラー</label>';
    echo '<input type="color" name="helixia_cat_color" id="helixia_cat_color" value="#2563eb">';
    echo '<p>カテゴリーラベルの背景色に使用されます。</p>';
    echo '</div>';
}
add_action('category_add_form_fields', 'helixia_category_color_add_field');

// カテゴリー編集画面のフィールド
function helixia_category_color_edit_field($term)
{
    $color = get_term_meta($term->term_id, 'helixia_cat_color', true);
    if (empty($color)) {
        $color = '#2563eb';
    }

    echo '<tr class="form-field">';
    echo '<th><label for="helixia_cat_color">カテゴリーカラー</label></th>';
    echo '<td><input type="color" name="helixia_cat_color" id="helixia_cat_color" value="' . esc_attr($color) . '">';
    echo '<p class="description">カテゴリーラベルの背景色に使用されます。</p></td>';
    echo '</tr>';
}
add_action('category_edit_form_fields', 'helixia_category_color_edit_field');

// カラーの保存
function helixia_save_category_color($term_id)
{
    if (isset($_POST['helixia_cat_color'])) {
        update_term_meta($term_id, 'helixia_cat_color', sanitize_hex_color($_POST['helixia_cat_color']));
    }
}
add_action('created_category', 'helixia_save_category_color');
add_action('edited_category', 'helixia_save_category_color');

/**
 * カテゴリーのカラーコードを取得するヘルパー関数
 * テンプレートで使用: <?php echo helixia_category_color($cat_id); ?>
 *
 * @param int $cat_id カテゴリーID
 * @return string HEXカラーコード（例: #2563eb）
 */
function helixia_category_color($cat_id)
{
    $color = get_term_meta($cat_id, 'helixia_cat_color', true);
    return !empty($color) ? $color : '#2563eb';
}


//* ===============================================
//# 6. テーマ設定ダッシュボードウィジェット
//* ===============================================

/**
 * 管理画面ダッシュボードにテーマの機能一覧と設定状況を表示
 * 外注コーダーやクライアントが「何が有効で何が無効か」を一目で確認できる
 */
function helixia_dashboard_widget()
{
    wp_add_dashboard_widget(
        'helixia_theme_status',
        '🎨 テーマ機能ステータス',
        'helixia_dashboard_widget_content'
    );
}
add_action('wp_dashboard_setup', 'helixia_dashboard_widget');

function helixia_dashboard_widget_content()
{
    $ga4_id = get_theme_mod('helixia_ga4_id', '');
    $gtm_id = get_theme_mod('helixia_gtm_id', '');
    $cookie_enabled = get_theme_mod('helixia_cookie_enabled', false);
    $biz_name = get_theme_mod('helixia_biz_name', '');
    $biz_region = get_theme_mod('helixia_biz_region', '');

    // チェック項目一覧
    $checks = array(
        array(
            'label'  => 'GA4 トラッキング',
            'status' => !empty($ga4_id),
            'detail' => !empty($ga4_id) ? $ga4_id : '未設定',
            'link'   => admin_url('customize.php?autofocus[section]=helixia_analytics'),
        ),
        array(
            'label'  => 'GTM コンテナ',
            'status' => !empty($gtm_id),
            'detail' => !empty($gtm_id) ? $gtm_id : '未設定',
            'link'   => admin_url('customize.php?autofocus[section]=helixia_analytics'),
        ),
        array(
            'label'  => 'Cookie同意バナー',
            'status' => $cookie_enabled,
            'detail' => $cookie_enabled ? '有効' : '無効',
            'link'   => admin_url('customize.php?autofocus[section]=helixia_cookie_consent'),
        ),
        array(
            'label'  => 'LocalBusiness 構造化データ',
            'status' => !empty($biz_region),
            'detail' => !empty($biz_name) ? $biz_name : '未設定',
            'link'   => admin_url('customize.php?autofocus[section]=helixia_local_business'),
        ),
        array(
            'label'  => 'Web Vitals 計測',
            'status' => !empty($ga4_id),
            'detail' => !empty($ga4_id) ? 'GA4連携中' : 'GA4未設定のため無効',
            'link'   => '',
        ),
        array(
            'label'  => 'Critical CSS',
            'status' => file_exists(get_theme_file_path('/css/critical.css')),
            'detail' => file_exists(get_theme_file_path('/css/critical.css')) ? 'ファイル検出済み' : 'critical.css 未生成',
            'link'   => '',
        ),
        array(
            'label'  => 'カスタムロゴ',
            'status' => (bool)get_theme_mod('custom_logo'),
            'detail' => get_theme_mod('custom_logo') ? '設定済み' : '未設定',
            'link'   => admin_url('customize.php?autofocus[section]=title_tagline'),
        ),
        array(
            'label'  => 'サイトアイコン',
            'status' => has_site_icon(),
            'detail' => has_site_icon() ? '設定済み' : '未設定',
            'link'   => admin_url('customize.php?autofocus[section]=title_tagline'),
        ),
    );

    // テーマ内機能（ファイル存在チェック）
    $features = array(
        'ai-search.php'       => 'AI検索最適化',
        'media.php'           => 'メディアサイト機能',
        'analytics.php'       => 'GA4 / イベント計測',
        'schema-business.php' => 'ビジネス構造化データ',
        'seo.php'             => 'SEO / OGP / JSON-LD',
        'security.php'        => 'セキュリティ強化',
        'performance.php'     => 'パフォーマンス最適化',
        'helper-frontend.php' => 'フロントエンド補助機能',
    );

    echo '<div style="font-size:13px">';

    // 設定ステータス
    echo '<h4 style="margin:8px 0 4px">📋 設定ステータス</h4>';
    echo '<table style="width:100%;border-collapse:collapse">';
    foreach ($checks as $check) {
        $icon = $check['status'] ? '✅' : '⚠️';
        $color = $check['status'] ? '#16a34a' : '#d97706';
        echo '<tr style="border-bottom:1px solid #f0f0f0">';
        echo '<td style="padding:6px 4px">' . $icon . ' ' . esc_html($check['label']) . '</td>';
        echo '<td style="padding:6px 4px;color:' . $color . '">';
        if (!empty($check['link'])) {
            echo '<a href="' . esc_url($check['link']) . '">' . esc_html($check['detail']) . '</a>';
        } else {
            echo esc_html($check['detail']);
        }
        echo '</td></tr>';
    }
    echo '</table>';

    // テーマ内機能一覧
    echo '<h4 style="margin:16px 0 4px">🔧 テーマ内蔵機能</h4>';
    echo '<div style="display:flex;flex-wrap:wrap;gap:4px">';
    foreach ($features as $file => $label) {
        $exists = file_exists(get_theme_file_path('/inc/' . $file));
        $bg = $exists ? '#dcfce7' : '#fef3c7';
        $fg = $exists ? '#166534' : '#92400e';
        $icon = $exists ? '✅' : '❌';
        echo '<span style="background:' . $bg . ';color:' . $fg . ';padding:2px 8px;border-radius:4px;font-size:12px">' . $icon . ' ' . esc_html($label) . '</span>';
    }
    echo '</div>';

    // 投稿統計
    $post_count = wp_count_posts('post');
    $page_count = wp_count_posts('page');
    echo '<h4 style="margin:16px 0 4px">📊 コンテンツ統計</h4>';
    echo '<p style="margin:0">投稿: <strong>' . (int)$post_count->publish . '</strong> 件 / 固定ページ: <strong>' . (int)$page_count->publish . '</strong> 件</p>';

    echo '</div>';
}


//* ===============================================
//# 7. All-in-One WP Migration 除外設定
//* ===============================================

$_theme_name = basename(get_template_directory());
add_filter(
    'ai1wm_exclude_themes_from_export',
    function ($exclude_filters) use ($_theme_name) {
        $new_exclusions = array(
            "{$_theme_name}/node_modules",
            // "{$_theme_name}/src",
            "{$_theme_name}/package-lock.json",
            "{$_theme_name}/package.json",
            "{$_theme_name}/README.md"
        );
        return array_merge($exclude_filters, $new_exclusions);
    }
);
