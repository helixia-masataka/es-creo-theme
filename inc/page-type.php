<?php
//* ===============================================
//# data-page="{name}"に使用するページタイプ取得用関数
//* ===============================================
function get_data_page_type() {
    if ( is_front_page() ) {
        return 'home';
    }
    if ( is_page( 'contact' ) ) {
        return 'contact';
    }
    // 上記（homeとcontact）以外のページは、すべて 'common'
    return 'common';
}

//* ===============================================
//# bodyクラス取得用関数（完全汎用型）
//* ===============================================
function my_generic_body_classes( $classes ) {
    global $post;

    // 1. トップページ
    if ( is_front_page() || is_home() ) {
        $classes[] = 'home';
        return $classes; // トップはここで処理終了
    }

    // --- ここから下はすべて下層ページ ---
    $classes[] = 'common'; // 下層ページ共通のクラスを付与

    // 2. 固定ページ (ページのスラッグを自動で付与)
    if ( is_page() ) {
        $classes[] = 'page';
        if ( isset( $post->post_name ) ) {
            $classes[] = 'page-' . $post->post_name; // 例: page-about, page-contact
        }
    }

    // 3. 記事詳細ページ (通常の投稿・カスタム投稿問わず自動判定)
    elseif ( is_singular() ) {
        $classes[] = 'single';
        $post_type = get_post_type();
        if ( $post_type ) {
            $classes[] = 'single-' . $post_type; // 例: single-post, single-works
        }
    }

    // 4. アーカイブページ (一覧ページ全般)
    elseif ( is_archive() ) {
        $classes[] = 'archive';
        
        // カスタム投稿一覧の場合
        if ( is_post_type_archive() ) {
            $post_type = get_query_var( 'post_type' );
            // ※複数投稿タイプ指定時の対策
            if ( is_array( $post_type ) ) { $post_type = reset( $post_type ); } 
            
            if ( ! empty( $post_type ) ) {
                $classes[] = 'archive-' . $post_type; // 例: archive-works
            }
        }
        // タクソノミー（カテゴリー・タグ含む）一覧の場合
        elseif ( is_tax() || is_category() || is_tag() ) {
            $term = get_queried_object();
            if ( $term ) {
                $classes[] = 'tax-' . $term->taxonomy; // 例: tax-works_category
            }
        }
    }

    // 5. 検索結果ページ
    elseif ( is_search() ) {
        $classes[] = 'search-results';
    }

    // 6. 404エラーページ
    elseif ( is_404() ) {
        $classes[] = 'error404'; // WP標準に近いクラス名にしておきます
    }

    // 配列内のクラス名の重複を排除して綺麗にしてから返す（プロの小技）
    return array_unique( $classes );
}
add_filter( 'body_class', 'my_generic_body_classes' );