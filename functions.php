<?php
//* ===============================================
//# テーマのメイン設定ファイル（functions.php）
//* ===============================================
//
// 【このファイルの特徴】
//
// 1. inc/ フォルダの PHP ファイルを配列でまとめて一括インクルード
//    - $inc_files 配列にファイル名を追加するだけで自動読み込み
//    - file_exists() チェックで存在しないファイルはスキップ（エラーなし）
//
// 2. 新しい機能を追加したいとき
//    - inc/ フォルダに新しい .php ファイルを作成
//    - $inc_files 配列にファイル名を追加（アルファベット順を推奨）
//
// 3. ページ固有JSの自動読み込み（ハイブリッド版）
//    - 全ページで common.js を読み込む
//    - ページタイプ（スラッグ）に対応した専用JSを自動追加
//    - 例: contact ページ → js/common.js + js/contact.js
//
// 4. 外部ライブラリ（Swiper）の条件読み込み
//    - トップページ・ブログ一覧ページでのみ Swiper を読み込む
//    → 不要なページへのスクリプト配信を削減
//
// 【注意】このファイルに直接コードを大量に書かず､
//         機能ごとに inc/ フォルダへ分割することを強く推奨

/**
 * Critical CSS の読み込み設定
 * true  : 常に読み込む
 * false : 常に読み込まない
 * null  : 本番環境（WP_DEBUG=false）の時のみ読み込む（デフォルト）
 */
define('HELIXIA_CRITICAL_CSS', null);

// 読み込みたいファイル名を配列にまとめる（統合済み：23→9ファイル）
$inc_files = array(
    'admin-tools.php',        // 管理画面ツール + バックアップ除外
    'ai-search.php',          // AIクローラー制御 + llms.txt
    'analytics.php',          // GA4/GTM + イベント計測
    'media.php',              // メディアサイト機能（ブログカード・著者情報・PR表記・人気記事）
    'performance.php',        // 速度最適化 + Critical CSS + リソースヒント + Web Vitals + Fonts
    'schema-business.php',    // ビジネス構造化データ（LocalBusiness/JobPosting/Event）
    'security.php',           // セキュリティ + Cookie同意バナー
    'seo.php',                // SEO/OGP/JSON-LD + パンくず + FAQ Schema + サイトマップ
    'helper-frontend.php',    // ページタイプ + ページネーション + CF7 + View Transitions
    'custom-fields-works.php', // 実績詳細用カスタムフィールド
    'custom-fields-about.php', // プロフィール用カスタムフィールド
    'ajax-works.php',         // 実績一覧Ajaxフィルタリング
    'cf7-honeypot.php',       // Contact Form 7 独自ハニーポット
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
function helixia_theme_setup()
{
    add_theme_support('post-thumbnails'); // アイキャッチ画像を有効化
    add_theme_support('automatic-feed-links'); // 投稿とコメントのRSSフィードのリンクを有効化
    add_theme_support('title-tag'); // タイトルタグ自動生成
    add_theme_support('custom-logo'); // カスタムロゴの条件分岐を動かすために必須
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
add_action('after_setup_theme', 'helixia_theme_setup');

//* ===============================================
//# CSSとJavaScriptの読み込み（共通JS＋専用JS ハイブリッド版）
//* ===============================================
function helixia_enqueue_assets()
{
    $theme_uri = get_template_directory_uri();

    // ページタイプを取得
    $page_type = function_exists('get_data_page_type') ? get_data_page_type() : 'common';

    // 読み込みたいJSファイルを格納する配列
    $js_files = array(
        // 全てのページで読み込む共通JS
        'common' => '/js/common.js',
    );

    // もしページタイプが 'common' 以外（home や contact）なら､専用JSも「追加」する
    if ($page_type !== 'common') {
        $js_files[$page_type] = '/js/' . $page_type . '.js';
    } elseif (is_page_template('page-about.php')) {
        $js_files['about'] = '/js/about.js';
    }

    // 配列をループしてJSを読み込む
    foreach ($js_files as $handle => $path) {
        $full_path = get_theme_file_path($path);
        // ファイルが実際に存在する場合のみ読み込む
        if (file_exists($full_path)) {
            $ver = filemtime($full_path);
            $deps = array();

            // about.js の場合は Swiper に依存させる
            if ($handle === 'about') {
                $deps[] = 'swiper-js';
            }

            wp_enqueue_script($handle . '-js', $theme_uri . $path, $deps, $ver, array('in_footer' => true, 'strategy' => 'defer'));

            // Ajax用のデータをホーム画面のJSに渡す
            if ($handle === 'home') {
                wp_localize_script('home-js', 'helixia_ajax', array(
                    'url' => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('helixia_ajax_nonce'),
                ));
            }
        }
    }

    // スタイルシート
    $style_css_path = get_theme_file_path('/css/style.css');
    $style_css_ver = file_exists($style_css_path) ? filemtime($style_css_path) : '1.0.1';
    wp_enqueue_style('style-css', $theme_uri . '/css/style.css', array(), $style_css_ver, 'all');
}
add_action('wp_enqueue_scripts', 'helixia_enqueue_assets');

//* ===============================================
//# 外部ライブラリの読み込み
//* ===============================================
function helixia_enqueue_library()
{
    // 例：トップページ､またはアバウトページでのみSwiperを読み込む場合
    if (is_front_page() || is_home() || is_page('about') || is_page_template('page-about.php')) {
        wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), null);
        wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), null, array('in_footer' => true, 'strategy' => 'defer'));
    }
}
add_action('wp_enqueue_scripts', 'helixia_enqueue_library');
