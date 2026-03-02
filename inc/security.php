<?php
//* ===============================================
//# セキュリティ対策 & <head>のクリーンアップ
//* ===============================================

// WordPressのバージョン情報を削除
remove_action('wp_head', 'wp_generator');

//RSSフィードなどに出力されるバージョン情報を削除
function my_theme_remove_version_info() {
    return '';
}
add_filter('the_generator', 'my_theme_remove_version_info');

//<head> 内の不要なタグを削除
remove_action('wp_head', 'wlwmanifest_link'); // Windows Live Writer用のタグを削除
remove_action('wp_head', 'rsd_link'); // 外部ツールからの編集用API（XML-RPC）のURLを削除
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0); // 短縮URLを削除

//ログイン画面のエラーメッセージを曖昧にする（ユーザーIDの特定を防ぐ）
function my_theme_obscure_login_errors() {
    return '<strong>エラー</strong>: ログイン情報が間違っています。';
}
add_filter('login_errors', 'my_theme_obscure_login_errors');

//XML-RPCの無効化（DDoS攻撃やブルートフォース攻撃を防止）
// ※注意：Jetpackプラグインや、WordPress公式スマホアプリを使う場合はコメントアウト
add_filter('xmlrpc_enabled', '__return_false');

//ユーザー列挙（Authorスキャン）からの保護
// URLに「/?author=1」などと入力してログインID（ユーザー名）を割り出す攻撃を防ぐ
function my_theme_disable_author_archive() {
    if ( is_author() ) {
        wp_redirect( esc_url( home_url( '/' ) ) ); // トップページへ強制リダイレクト
        exit;
    }
}
add_action('template_redirect', 'my_theme_disable_author_archive');