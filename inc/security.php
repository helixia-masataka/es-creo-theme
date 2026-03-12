<?php
//* ===============================================
//# セキュリティ対策 & プライバシー
//* ===============================================
//
// 【このファイルの特徴】
// サイトの安全性を高めるセキュリティ対策と、プライバシー保護法案に対応する機能を提供します。
//
// 1. セキュリティ対策 & <head> のクリーンアップ（旧 security.php）
//    - wp_generator（バージョン情報）などWordPressが出力する不要なメタデータを削除し、攻撃者に情報を与えないようにします。
// 2. Cookie 同意バナー（改正電気通信事業法 / GDPR 対応）（旧 cookie-consent.php）
//    - ユーザーのプライバシー保護を目的としたCookie同意バナーを表示し、再訪問時の表示を制御します。
//

//* ===============================================
//# 1. セキュリティ対策 & <head> のクリーンアップ
//* ===============================================

// WordPressのバージョン情報を削除
remove_action('wp_head', 'wp_generator');

//RSSフィードなどに出力されるバージョン情報を削除
function helixia_remove_version_info()
{
    return '';
}
add_filter('the_generator', 'helixia_remove_version_info');

//<head> 内の不要なタグを削除
remove_action('wp_head', 'wlwmanifest_link'); // Windows Live Writer用のタグを削除
remove_action('wp_head', 'rsd_link'); // 外部ツールからの編集用API（XML-RPC）のURLを削除
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0); // 短縮URLを削除

//ログイン画面のエラーメッセージを曖昧にする（ユーザーIDの特定を防ぐ）
function helixia_obscure_login_errors()
{
    return '<strong>エラー</strong>: ログイン情報が間違っています。';
}
add_filter('login_errors', 'helixia_obscure_login_errors');

//XML-RPCの無効化（DDoS攻撃やブルートフォース攻撃を防止）
// ※注意：Jetpackプラグインや、WordPress公式スマホアプリを使う場合はコメントアウト
add_filter('xmlrpc_enabled', '__return_false');

//ユーザー列挙（Authorスキャン）からの保護
function helixia_disable_author_archive()
{
    if (is_author()) {
        wp_redirect(esc_url(home_url('/'))); // トップページへ強制リダイレクト
        exit;
    }
}
add_action('template_redirect', 'helixia_disable_author_archive');


//* ===============================================
//# 2. Cookie 同意バナー（改正電気通信事業法 / GDPR 対応）
//* ===============================================

// カスタマイザー設定

function helixia_cookie_consent_customizer($wp_customize)
{
    $wp_customize->add_section('helixia_cookie_consent', array(
        'title'    => 'Cookie同意バナー',
        'priority' => 50,
    ));

    // 有効/無効スイッチ
    $wp_customize->add_setting('helixia_cookie_enabled', array(
        'default'           => false,
        'sanitize_callback' => 'wp_validate_boolean',
    ));
    $wp_customize->add_control('helixia_cookie_enabled', array(
        'label'   => 'Cookie同意バナーを有効化',
        'section' => 'helixia_cookie_consent',
        'type'    => 'checkbox',
    ));

    // バナー文言
    $wp_customize->add_setting('helixia_cookie_message', array(
        'default'           => '当サイトでは、サービス向上のためにCookieを使用しています。サイトの利用を続けることで、Cookieの使用に同意したものとみなされます。',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('helixia_cookie_message', array(
        'label'   => 'バナー文言',
        'section' => 'helixia_cookie_consent',
        'type'    => 'textarea',
    ));

    // プライバシーポリシーURL
    $wp_customize->add_setting('helixia_cookie_privacy_url', array(
        'default'           => '/privacy-policy/',
        'sanitize_callback' => 'esc_url_raw',
    ));
    $wp_customize->add_control('helixia_cookie_privacy_url', array(
        'label'       => 'プライバシーポリシーURL',
        'section'     => 'helixia_cookie_consent',
        'type'        => 'url',
        'description' => 'バナーに「詳しくはこちら」リンクとして表示',
    ));
}
add_action('customize_register', 'helixia_cookie_consent_customizer');


// バナーHTML/CSS/JS出力

function helixia_cookie_consent_banner()
{
    // 無効化されている場合は何も出力しない
    if (!get_theme_mod('helixia_cookie_enabled', false)) {
        return;
    }

    // 管理者は除外しない（テスト用に表示を確認できるようにする）

    $message = get_theme_mod('helixia_cookie_message', '当サイトでは、サービス向上のためにCookieを使用しています。サイトの利用を続けることで、Cookieの使用に同意したものとみなされます。');
    $privacy_url = get_theme_mod('helixia_cookie_privacy_url', '/privacy-policy/');
    ?>

<style>
.c-cookie-banner{position:fixed;bottom:0;left:0;right:0;background:rgba(30,30,30,.95);color:#fff;padding:16px 24px;display:flex;align-items:center;justify-content:center;gap:16px;z-index:10000;font-size:14px;line-height:1.6;backdrop-filter:blur(8px);transform:translateY(100%);transition:transform .4s ease}
.c-cookie-banner.is-visible{transform:translateY(0)}
.c-cookie-banner__text{max-width:680px}
.c-cookie-banner__text a{color:#93c5fd;text-decoration:underline}
.c-cookie-banner__actions{display:flex;gap:8px;flex-shrink:0}
.c-cookie-banner__btn{padding:8px 24px;border:none;border-radius:6px;font-size:14px;font-weight:bold;cursor:pointer;transition:opacity .2s}
.c-cookie-banner__btn--accept{background:#2563eb;color:#fff}
.c-cookie-banner__btn--accept:hover{opacity:.85}
.c-cookie-banner__btn--reject{background:transparent;color:#ccc;border:1px solid #666}
.c-cookie-banner__btn--reject:hover{border-color:#999;color:#fff}
@media(max-width:768px){.c-cookie-banner{flex-direction:column;text-align:center;padding:16px}.c-cookie-banner__actions{width:100%;justify-content:center}}
</style>

<div class="c-cookie-banner" id="js-cookie-banner" role="dialog" aria-label="Cookie同意">
    <div class="c-cookie-banner__text">
        <?php echo esc_html($message); ?>
        <?php if (!empty($privacy_url)) : ?>
            <a href="<?php echo esc_url($privacy_url); ?>">詳しくはこちら</a>
        <?php endif; ?>
    </div>
    <div class="c-cookie-banner__actions">
        <button class="c-cookie-banner__btn c-cookie-banner__btn--accept" id="js-cookie-accept">同意する</button>
        <button class="c-cookie-banner__btn c-cookie-banner__btn--reject" id="js-cookie-reject">拒否</button>
    </div>
</div>

<script>
(function(){
    var STORAGE_KEY = 'helixia_cookie_consent';
    var EXPIRY_DAYS = 30;
    var banner = document.getElementById('js-cookie-banner');
    if (!banner) return;

    // 同意状態を確認
    function getConsent() {
        try {
            var data = JSON.parse(localStorage.getItem(STORAGE_KEY));
            if (data && data.expires > Date.now()) {
                return data.value; // 'accepted' or 'rejected'
            }
            localStorage.removeItem(STORAGE_KEY);
        } catch(e) {}
        return null;
    }

    function setConsent(value) {
        var data = {
            value: value,
            expires: Date.now() + (EXPIRY_DAYS * 24 * 60 * 60 * 1000)
        };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    }

    var consent = getConsent();

    // 未同意の場合のみバナーを表示
    if (consent === null) {
        requestAnimationFrame(function(){
            banner.classList.add('is-visible');
        });
    }

    // 同意ボタン
    document.getElementById('js-cookie-accept').addEventListener('click', function(){
        setConsent('accepted');
        banner.classList.remove('is-visible');
        // GA4/GTM のスクリプトをロード
        if (typeof window.helixia_load_analytics === 'function') {
            window.helixia_load_analytics();
        }
    });

    // 拒否ボタン
    document.getElementById('js-cookie-reject').addEventListener('click', function(){
        setConsent('rejected');
        banner.classList.remove('is-visible');
    });

    // 同意済みの場合はGA4/GTMを即時ロード
    if (consent === 'accepted') {
        // analytics.php が同意ベースで動的ロードされる場合はここで実行
        if (typeof window.helixia_load_analytics === 'function') {
            window.helixia_load_analytics();
        }
    }
})();
</script>

    <?php
}
add_action('wp_footer', 'helixia_cookie_consent_banner', 50);