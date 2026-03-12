<?php
//* ===============================================
//# テンプレート補助・UI機能
//* ===============================================
//
// 【このファイルの特徴】
// フロントエンド（ユーザー側の画面）におけるUIの補助的な機能を一手に引き受けます。
// 主に以下の機能が統合されています：
//
// 1. ページタイプ取得・body クラス管理
//    - 各ページごとの body クラスを自動生成し、bodyタグに付与することでCSSでの固有スタイリングを容易にします。
// 2. 汎用ページネーション
//    - WordPress標準のページネーションをカスタマイズし、SVGの矢印アイコンを用いたモダンなデザインで出力します。
// 3. Contact Form 7 カスタマイズ
//    - CF7 の不要な自動 <p> タグ挿入を無効化し、送信完了時にサンクスページへ自動遷移する機能を追加します。
// 4. Cross-Document View Transitions（旧 view-transitions.php）
//    - 画面遷移時のアニメーションに関する独自CSSを出力し、SPAのような滑らかなページ切り替えを実現します。
//

//* ===============================================
//# 1. ページタイプ取得（data-page 属性の自動生成）
//* ===============================================

function get_data_page_type()
{
    if (is_front_page()) {
        return 'home';
    }

    // 固定ページの場合、スラッグをそのまま返す（例: contact → 'contact'）
    if (is_page()) {
        $slug = get_post_field('post_name', get_post());
        if ($slug) {
            return $slug;
        }
    }

    // 上記以外のページは、すべて 'common'
    return 'common';
}

//* ===============================================
//# bodyクラス取得用関数
//* ===============================================
function helixia_body_classes($classes)
{
    global $post;

    // 1. トップページ
    if (is_front_page() || is_home()) {
        $classes[] = 'home';
        return $classes; // トップはここで処理終了
    }

    // --- ここから下はすべて下層ページ ---
    $classes[] = 'common'; // 下層ページ共通のクラスを付与

    // 2. 固定ページ (ページのスラッグを自動で付与)
    if (is_page()) {
        $classes[] = 'page';
        if (isset($post->post_name)) {
            $classes[] = 'page-' . $post->post_name; // 例: page-about, page-contact
        }
    }

    // 3. 記事詳細ページ (通常の投稿・カスタム投稿問わず自動判定)
    elseif (is_singular()) {
        $classes[] = 'single';
        $post_type = get_post_type();
        if ($post_type) {
            $classes[] = 'single-' . $post_type; // 例: single-post, single-works
        }
    }

    // 4. アーカイブページ (一覧ページ全般)
    elseif (is_archive()) {
        $classes[] = 'archive';

        // カスタム投稿一覧の場合
        if (is_post_type_archive()) {
            $post_type = get_query_var('post_type');
            // ※複数投稿タイプ指定時の対策
            if (is_array($post_type)) {
                $post_type = reset($post_type);
            }

            if (!empty($post_type)) {
                $classes[] = 'archive-' . $post_type; // 例: archive-works
            }
        }
        // タクソノミー（カテゴリー・タグ含む）一覧の場合
        elseif (is_tax() || is_category() || is_tag()) {
            $term = get_queried_object();
            if ($term) {
                $classes[] = 'tax-' . $term->taxonomy; // 例: tax-works_category
            }
        }
    }

    // 5. 検索結果ページ
    elseif (is_search()) {
        $classes[] = 'search-results';
    }

    // 6. 404エラーページ
    elseif (is_404()) {
        $classes[] = 'error404';
    }

    // 配列内のクラス名の重複を排除して返す
    return array_unique($classes);
}
add_filter('body_class', 'helixia_body_classes');


//* ===============================================
//# 2. 汎用ページネーション（paginate_links活用版）
//* ===============================================

function helixia_pagination()
{
    global $wp_query;

    // ページが1ページしかない場合は何も表示しない
    if ($wp_query->max_num_pages <= 1)
        return;

    // SVGアイコン（色はCSSで制御できるようcurrentColorを使用）
    $prev_svg = '<svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M6 11L1 6L6 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>';
    $next_svg = '<svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M1 11L6 6L1 1" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" /></svg>';

    echo '<div class="pagination-links">';

    // WordPressのページネーション出力関数
    echo paginate_links(array(
        'current' => max(1, get_query_var('paged')),
        'total' => $wp_query->max_num_pages,
        'prev_text' => '<span class="c-btn-pagenate --prev">' . $prev_svg . '</span>',
        'next_text' => '<span class="c-btn-pagenate --next">' . $next_svg . '</span>',
        'mid_size' => 1,
        'end_size' => 1,
    ));

    echo '</div>';
}


//* ===============================================
//# 3. Contact Form 7 カスタマイズ
//* ===============================================

add_filter('wpcf7_autop_or_not', 'helixia_wpcf7_autop_return_false');
function helixia_wpcf7_autop_return_false()
{
  return false;
}

//送信完了ページ遷移
// Contact Form7の送信ボタンをクリックした後の遷移先設定
add_action('wp_footer', 'helixia_redirect_to_thanks_page');
function helixia_redirect_to_thanks_page()
{
  $homeUrl = esc_url(home_url());
  echo <<<EOD
    <script>
      document.addEventListener( 'wpcf7mailsent', function( event ) {
        location = '{$homeUrl}/thanks/';
      }, false );
    </script>
  EOD;
}


//* ===============================================
//# 4. Cross-Document View Transitions（ページ遷移アニメーション）
//* ===============================================

/**
 * View Transitions 用の CSS をインラインで出力
 * wp_head に出力して全ページで適用
 */
function helixia_view_transitions_css()
{
    ?>
<style>
/* ============================================= */
/* Cross-Document View Transitions               */
/* ============================================= */

/* 1. View Transitions を有効化 */
@view-transition {
    navigation: auto;
}

/* 2. 各要素に view-transition-name を設定 */
.l-header {
    view-transition-name: site-header;
}

.l-header__logo {
    view-transition-name: site-logo;
}

.l-main {
    view-transition-name: main-content;
}

.l-footer {
    view-transition-name: site-footer;
}

/* 3. デフォルトのクロスフェードをカスタマイズ */

/* ルート（ページ全体）のフェードを無効化（個別要素で制御するため） */
::view-transition-group(root) {
    animation-duration: 0s;
}

/* ヘッダー: 遷移中も固定表示（フェードしない） */
::view-transition-old(site-header),
::view-transition-new(site-header) {
    animation: none;
}

/* ロゴ: スムーズに位置・サイズを補間 */
::view-transition-group(site-logo) {
    animation-duration: 0.3s;
    animation-timing-function: ease;
}

/* メインコンテンツ: フェード + スライドアップで入れ替わる */
::view-transition-old(main-content) {
    animation: vt-fade-out 0.2s ease forwards;
}

::view-transition-new(main-content) {
    animation: vt-slide-in 0.3s ease forwards;
}

/* フッター: 軽いフェード */
::view-transition-old(site-footer),
::view-transition-new(site-footer) {
    animation-duration: 0.2s;
}

/* 4. アニメーション定義 */
@keyframes vt-fade-out {
    from { opacity: 1; transform: translateY(0); }
    to   { opacity: 0; transform: translateY(-8px); }
}

@keyframes vt-slide-in {
    from { opacity: 0; transform: translateY(12px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* 5. アクセシビリティ: 視差効果を減らす設定に従う */
@media (prefers-reduced-motion: reduce) {
    ::view-transition-group(*),
    ::view-transition-old(*),
    ::view-transition-new(*) {
        animation-duration: 0s !important;
    }
}
</style>
    <?php
}
add_action('wp_head', 'helixia_view_transitions_css', 5);
