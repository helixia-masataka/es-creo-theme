<?php
//* ===============================================
//# incフォルダの読み込み

// 読み込みたいファイル名を配列にまとめる
$inc_files = array(
    'contactform.php',
    'exclude-aio-migration.php',
    'fonts.php',
    'security.php',
    'page-type.php',
    'speed.php',

);

// 配列をループ処理して順番に読み込む
foreach ($inc_files as $file) {
    $file_path = get_theme_file_path('/inc/' . $file);
    if (file_exists($file_path)) {
        include_once $file_path;
    }
}

//* ===============================================
//# テーマ設定用関数
function my_theme_setup()
{
    add_theme_support('post-thumbnails'); // アイキャッチ画像を有効化
    add_theme_support('automatic-feed-links'); // 投稿とコメントのRSSフィードのリンクを有効化
    add_theme_support('title-tag'); // タイトルタグ自動生成
    add_theme_support('custom-logo'); // ★追加：先ほど作った「カスタムロゴの条件分岐」を動かすために必須！
    add_theme_support(
        'html5',
        array( //HTML5でマークアップ
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
            'style',
            'script'
        )
    );
    //管理画面メニュー登録
    register_nav_menus(array(
        'header-menu' => 'ヘッダーメニュー',
        'footer-menu' => 'フッターメニュー',
    ));
}
add_action('after_setup_theme', 'my_theme_setup');

//* ===============================================
//# CSSとJavaScriptの読み込み（共通JS＋専用JS ハイブリッド版）
//* ===============================================
function my_theme_enqueue_assets() {
    if ( ! is_admin() ) {
        wp_deregister_script('jquery');
        wp_enqueue_script('jquery', '//code.jquery.com/jquery-3.6.1.min.js', array(), '3.6.1', true);
    }

    $theme_uri = get_template_directory_uri();
    
    // ページタイプを取得
    $page_type = function_exists('get_data_page_type') ? get_data_page_type() : 'common';

    // 読み込みたいJSファイルを格納する配列
    $js_files = array(
        // 全てのページで読み込む共通JS
        'common' => '/js/common.js',
    );

    // もしページタイプが 'common' 以外（home や contact）なら、専用JSも「追加」する
    if ( $page_type !== 'common' ) {
        $js_files[ $page_type ] = '/js/' . $page_type . '.js';
    }

    // 配列をループしてJSを読み込む
    foreach ( $js_files as $handle => $path ) {
        $full_path = get_theme_file_path( $path );
        // ファイルが実際に存在する場合のみ読み込む
        if ( file_exists( $full_path ) ) {
            $ver = filemtime( $full_path ); 
            wp_enqueue_script( $handle . '-js', $theme_uri . $path, array('jquery'), $ver, true );
        }
    }

    // スタイルシート
    $style_css_path = get_theme_file_path('/css/style.css');
    $style_css_ver = file_exists($style_css_path) ? filemtime($style_css_path) : '1.0.1';
    wp_enqueue_style('style-css', $theme_uri . '/css/style.css', array(), $style_css_ver, 'all');
}
add_action('wp_enqueue_scripts', 'my_theme_enqueue_assets');

//* ===============================================
//# 外部ライブラリの読み込み
//* ===============================================
// Swiperの最適化読み込み
function my_theme_enqueue_library()
{
    // 例：トップページでのみSwiperを読み込む場合
    if (is_front_page() || is_home()) {
        // CDNからの読み込み（※バージョン情報のクエリを消すため第4引数はnull）
        wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.css', array(), null);
        wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@8/swiper-bundle.min.js', array(), null, true);
    }
}
add_action('wp_enqueue_scripts', 'my_theme_enqueue_library');

//* ===============================================
//# JavaScriptの読み込み最適化（defer属性の付与・動的対応版）
//* ===============================================
function my_theme_add_defer_to_script( $tag, $handle ) {
    
    // deferを付与したいスクリプト
    $defer_scripts = array(
        'swiper-js',
        'common-js', // ★ 共通JSは常にdeferを付与
    );

    // ★ 追加で読み込まれたページ専用のJS（home-js, contact-jsなど）もdeferの対象にする
    if ( function_exists('get_data_page_type') ) {
        $page_type = get_data_page_type();
        if ( $page_type !== 'common' ) {
            $defer_scripts[] = $page_type . '-js';
        }
    }

    // 読み込もうとしているJSが配列の中に含まれていれば defer を付ける
    if ( in_array( $handle, $defer_scripts, true ) ) {
        return str_replace( ' src', ' defer src', $tag );
    }

    return $tag;
}
add_filter( 'script_loader_tag', 'my_theme_add_defer_to_script', 10, 2 );
