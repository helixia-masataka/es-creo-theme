<?php
/**
 * トップページ高速化設定 (Main Visual Preload)
 */

function my_seo_preload_front_page_mv() {
    // トップページ以外では何もしない
    if (!is_front_page()) {
        return;
    }

    // front-page.php で定義したグローバル変数を呼び出す
    global $my_mv_image_path;


    if (empty($my_mv_image_path)) {
        return;
    }

    // 変数のパスを使って完全なURLを生成
    $image_url = get_theme_file_uri($my_mv_image_path);

    // Preloadタグの出力
    echo "\n";
    echo '<link rel="preload" as="image" href="' . esc_url($image_url) . '" fetchpriority="high">' . "\n";
}
add_action('wp_head', 'my_seo_preload_front_page_mv', 1);

//* ===============================================
//# 画像の最適化（WebP変換・Lazy Load調整・非同期デコード）
//* ===============================================

// アップロードした画像を自動的にWebP形式で保存する
// ※JPEGやPNGをアップロードした際、サーバー側で自動的にWebPに変換して保存（WordPress 5.8以降の機能）
function my_theme_upload_image_as_webp( $default_mime_type, $attachment_ext ) {
    if ( 'jpg' === $attachment_ext || 'jpeg' === $attachment_ext || 'png' === $attachment_ext ) {
        return 'image/webp';
    }
    return $default_mime_type;
}
add_filter( 'image_editor_default_mime_type', 'my_theme_upload_image_as_webp', 10, 2 );


// 先頭の画像（ファーストビュー）のLazy Loadを解除してLCPスコアを改善する
//「最初の2枚」は遅延読み込みから除外
function my_theme_adjust_lazy_load_threshold() {
    return 2; // 先頭から2枚の画像には loading="lazy" を付けない
}
add_filter( 'wp_omit_loading_attr_threshold', 'my_theme_adjust_lazy_load_threshold' );


// すべての画像に decoding="async" を付与してブラウザの描画ストップを防ぐ
function my_theme_add_async_decoding_to_images( $attr ) {
    if ( ! isset( $attr['decoding'] ) ) {
        $attr['decoding'] = 'async';
    }
    return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'my_theme_add_async_decoding_to_images' );

//* ===============================================
//# WordPressの不要なデフォルト機能の停止（HTML・通信の軽量化）
//* ===============================================

// 1. 絵文字（Emoji）用の不要なJSとCSSを強力に削除
function my_theme_disable_emojis() {
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
}
add_action( 'init', 'my_theme_disable_emojis' );

//oEmbed（埋め込み機能）のJSと<head>内の不要なリンクを完全削除
function my_theme_disable_embeds() {
    wp_deregister_script( 'wp-embed' );
}
add_action( 'wp_footer', 'my_theme_disable_embeds' );
remove_action( 'wp_head', 'wp_oembed_add_discovery_links' ); // oEmbedの<link>タグを削除
remove_action( 'wp_head', 'wp_oembed_add_host_js' );

//グローバルスタイルやブロックCSSの最適化（必要なページ以外では削除）
function my_theme_optimize_block_css() {
    // 投稿(single)や固定ページ(page)などの個別ページ「以外」では不要なCSSを解除
    if (!is_singular() ) {
        wp_dequeue_style( 'global-styles' );
        wp_dequeue_style( 'wp-block-library' );
        wp_dequeue_style( 'wp-block-library-theme' );
        wp_dequeue_style( 'classic-theme-styles' );
    }
}
add_action( 'wp_enqueue_scripts', 'my_theme_optimize_block_css', 200 );


// SVGフィルターはデザインに影響しにくいので全ページで強制ストップ
function my_theme_remove_extra_bloat() {
    remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
    remove_action( 'in_admin_header', 'wp_global_styles_render_svg_filters' );
}
add_action( 'init', 'my_theme_remove_extra_bloat' );
