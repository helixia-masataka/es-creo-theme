<?php

//* ===============================================
//# SEO管理（メタタグ・OGP・構造化データ・パンくず・サイトマップ）
//* ===============================================
//
// 【このファイルの特徴】
// SEOプラグインに依存せず、高度かつ最新のSEO対策を内部的に完結させるためのロジック群です。
//
// 1. メタタグ・OGP・Twitter Cards の自動生成（表示ページに合わせて最適化）
// 2. OGP画像のスマートなフォールバック（デフォルト画像の設定）
// 3. ファビコン設定（Web Clipアイコン含む自動フォールバック）
// 4. 画像のalt属性の自動補完（空の場合に記事タイトル等から生成）
// 5. パンくずリストの実装（JSON-LD構造化データ・microdata対応）
// 6. FAQ構造化データ 自動生成（FAQブロックを検知してJSON-LD生成）
// 7. 動的 sitemap.xml の出力コントロール
//
//    - 優先2: カスタムロゴ
//    - 優先1（最高）: 各投稿のアイキャッチ画像
//    → どの設定でも必ずOGP画像が存在する状態を保証
//
// 3. JSON-LD 構造化データを自動生成（SEO・AI検索対策）
//    - トップページ: WebSite + SearchAction（サイト内検索ボックス）+ Organization
//    - 投稿ページ: Article（author, publishedDate, image 含む）
//    - 固定ページ: WebPage
//    - すべてに speakable プロパティを追加（AI音声検索・AI引用対策）
//
// 4. ページタイプ別の自動判定
//    - is_front_page(), is_singular(), is_404() 等で自動分岐
//    - 404・検索結果ページは noindex を自動付与
//
// 【設定変更】OGPデフォルト画像の差し替え → img/ogp-default.webp を更新するだけ


function helixia_seo_meta_tags()
{
    global $post;

    $site_name = get_bloginfo('name');
    $site_desc = get_bloginfo('description');
    $url = home_url('/');
    $type = 'website';
    $title = $site_name;
    $description = $site_desc;

    // --- OGP画像のフォールバック（優先順位判定） ---
    // 優先度4（最低）: テーマ内のデフォルト画像（1200×630推奨）
    $image = get_theme_file_uri('/img/ogp-default.webp');

    // 優先度3: 管理画面で「サイトアイコン（Favicon）」が設定されていればそれを使用
    if (has_site_icon()) {
        $image = get_site_icon_url(512);
    }

    // 優先度2: 管理画面で「カスタムロゴ」が設定されていればそれを優先
    $custom_logo_id = get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $logo_url = wp_get_attachment_image_src($custom_logo_id, 'full');
        if ($logo_url) {
            $image = $logo_url[0];
        }
    }

    // --- 価値の低いページは「noindex」にする ---
    if (is_404() || is_search()) {
        echo '<meta name="robots" content="noindex, follow">' . "\n";
        return; // OGPなどは出力せずに終了
    }

    // --- ページごとのデータ取得 ---
    if (is_front_page() || is_home()) {
        $type = 'website';
        $title = $site_name;
        $url = home_url('/');
    } elseif (is_singular()) {
        $type = 'article';
        $title = get_the_title() . ' | ' . $site_name;
        $url = get_permalink();

        // ディスクリプションの自動生成
        if (has_excerpt()) {
            $description = wp_strip_all_tags(get_the_excerpt());
        } else {
            $content = strip_shortcodes($post->post_content);
            $content = wp_strip_all_tags($content);
            $description = mb_substr($content, 0, 100) . '...';
            $description = str_replace(array("\r", "\n"), '', $description);
        }

        // 優先度1（最高）: 記事の「アイキャッチ画像」があれば最優先
        if (has_post_thumbnail()) {
            $image = get_the_post_thumbnail_url($post->ID, 'large');
        }
    } elseif (is_category() || is_tax() || is_tag()) {
        $type = 'website';
        $term = get_queried_object();
        $title = $term->name . ' | ' . $site_name;
        $url = get_term_link($term);
        $description = term_description() ? wp_strip_all_tags(term_description()) : $term->name . 'の一覧ページです。';
    } elseif (is_post_type_archive()) {
        $type = 'website';
        $title = post_type_archive_title('', false) . ' | ' . $site_name;
        $url = get_post_type_archive_link(get_query_var('post_type'));
        $description = post_type_archive_title('', false) . 'の一覧ページです。';
    }

    if (is_paged()) {
        $description .= '（' . get_query_var('paged') . 'ページ目）';
    }

    // --- HTMLへの出力 ---
    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:type" content="' . esc_attr($type) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
    echo '<meta property="og:image:width" content="1200">' . "\n";
    echo '<meta property="og:image:height" content="630">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
}
add_action('wp_head', 'helixia_seo_meta_tags', 1);


//* ===============================================
//# リッチリザルト対応 JSON-LD構造化データ自動出力
//* ===============================================
function helixia_json_ld_structured_data()
{
    if (is_404() || is_search())
        return;

    $site_name = get_bloginfo('name');
    $site_url = home_url('/');

    // ロゴ画像のフォールバック取得
    $logo_url = get_theme_file_uri('/img/ogp-default.webp');
    $custom_logo_id = get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $logo_image = wp_get_attachment_image_src($custom_logo_id, 'full');
        if ($logo_image)
            $logo_url = $logo_image[0];
    } elseif (has_site_icon()) {
        $logo_url = get_site_icon_url(512);
    }

    $schema_data = array();

    // トップページ：WebSite（サイト内検索ボックス対応）＆ Organization
    if (is_front_page() || is_home()) {
        $schema_data[] = array(
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $site_name,
            'url' => $site_url,
            'potentialAction' => array(
                '@type' => 'SearchAction',
                'target' => array(
                    '@type' => 'EntryPoint',
                    'urlTemplate' => $site_url . '?s={search_term_string}'
                ),
                'query-input' => 'required name=search_term_string'
            )
        );

        $schema_data[] = array(
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $site_name,
            'url' => $site_url,
            'logo' => esc_url($logo_url)
        );
    }

    // 記事ページ：Article / WebPage
    if (is_singular() && !is_front_page()) {
        global $post;
        $article_image = has_post_thumbnail() ? get_the_post_thumbnail_url($post->ID, 'large') : $logo_url;

        $schema_data[] = array(
            '@context' => 'https://schema.org',
            '@type' => is_single() ? 'Article' : 'WebPage',
            'headline' => get_the_title(),
            'image' => array(esc_url($article_image)),
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            // 文字数（メディアサイト向け：コンテンツボリュームの指標）
            'wordCount' => mb_strlen(wp_strip_all_tags($post->post_content), 'UTF-8'),
            'author' => array(
                '@type' => 'Person',
                'name' => get_the_author_meta('display_name', $post->post_author),
                'url' => get_author_posts_url($post->post_author)
            ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => $site_name,
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => esc_url($logo_url)
                )
            ),
            // AI・音声アシスタント向け：読み上げに適した箇所を明示
            'speakable' => array(
                '@type' => 'SpeakableSpecification',
                'cssSelector' => array(
                    'h1',           // ページタイトル
                    '.entry-content p:first-of-type', // 本文の最初の段落
                    '.p-single__lead', // リード文（テーマ固有）
                ),
            ),
        );

        // Article の場合のみ articleSection（カテゴリー）と keywords（タグ）を追加
        if (is_single()) {
            $last_index = count($schema_data) - 1;

            // カテゴリー名を articleSection に
            $categories = get_the_category($post->ID);
            if (!empty($categories)) {
                $schema_data[$last_index]['articleSection'] = $categories[0]->name;
            }

            // タグ名を keywords 配列に
            $tags = get_the_tags($post->ID);
            if (!empty($tags)) {
                $schema_data[$last_index]['keywords'] = wp_list_pluck($tags, 'name');
            }
        }

    }

    // HTMLに出力
    if (!empty($schema_data)) {
        echo '<script type="application/ld+json">' . "\n";
        echo wp_json_encode(
            count($schema_data) === 1 ? $schema_data[0] : $schema_data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        ) . "\n";
        echo '</script>' . "\n";
    }
}
add_action('wp_head', 'helixia_json_ld_structured_data', 2);


//* ===============================================
//# ファビコン（Site Icon）の自動フォールバック機能
//* ===============================================
function helixia_default_favicon()
{
    if (!has_site_icon()) {
        $favicon = get_theme_file_uri('/favicon.ico');
        $apple_icon = get_theme_file_uri('/apple-touch-icon.webp');

        echo '<link rel="icon" href="' . esc_url($favicon) . '" />' . "\n";
        echo '<link rel="apple-touch-icon" href="' . esc_url($apple_icon) . '" />' . "\n";
    }
}
add_action('wp_head', 'helixia_default_favicon', 100);


//* ===============================================
//# 画像SEO対策：alt属性が空の場合、記事タイトルを自動補完
//* ===============================================
function helixia_auto_image_alt($content)
{
    global $post;
    if (!empty($post)) {
        $title = esc_attr($post->post_title);

        // alt属性が存在しないimgタグに alt="記事タイトル" を追加
        $content = preg_replace('/<img((?:(?!\balt=)[^>])*?)>/i', '<img$1 alt="' . $title . '">', $content);
        // alt="" のように空っぽになっているimgタグに記事タイトルを挿入
        $content = preg_replace('/<img([^>]*?)alt=""([^>]*?)>/i', '<img$1alt="' . $title . '"$2>', $content);
    }
    return $content;
}
add_filter('the_content', 'helixia_auto_image_alt', 10);


//* ===============================================
//# 4. パンくずリスト（JSON-LD構造化データ対応版）
//* ===============================================

function helixia_breadcrumbs()
{
    // トップページでは表示しない
    if (is_front_page() || is_home())
        return;

    $separator = ' <span class="c-breadcrumb__sep">&gt;</span> ';
    $position = 1; // JSON-LD用の階層カウント

    // JSON-LDのベースとなる配列
    $json_ld = array(
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => array()
    );

    // HTMLの出力用変数
    $html = '<div class="c-breadcrumb"><ol class="c-breadcrumb__list">';

    // 【便利機能】HTMLとJSON-LDの両方に同時にデータを追加する処理
    $add_item = function ($name, $url = '', $is_last = false) use (&$html, &$json_ld, &$position, $separator) {

        // 1. HTMLの組み立て
        if ($position > 1) {
            $html .= $separator;
        }
        if ($url && !$is_last) {
            // リンクあり（途中の階層）
            $html .= '<li><a href="' . esc_url($url) . '">' . esc_html($name) . '</a></li>';
        } else {
            // リンクなし（現在のページ）
            $html .= '<li><span class="current">' . esc_html($name) . '</span></li>';
        }

        // 2. JSON-LDの組み立て
        $item = array(
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $name,
        );
        if ($url) {
            $item['item'] = esc_url($url);
        }
        $json_ld['itemListElement'][] = $item;

        $position++;
    };


    // --- ここから各ページの条件分岐 ---

    // 階層1: トップページ
    $add_item('トップ', home_url('/'));

    // 階層2以降: 固定ページ
    if (is_page()) {
        global $post;
        if ($post->post_parent) {
            // 親ページがあれば順番に取得
            $ancestors = array_reverse(get_post_ancestors($post->ID));
            foreach ($ancestors as $ancestor) {
                $add_item(get_the_title($ancestor), get_permalink($ancestor));
            }
        }
        // 現在の固定ページ（最後なので $is_last を true に）
        $add_item(get_the_title(), get_permalink(), true);
    }
    // 階層2以降: 記事詳細ページ
    elseif (is_singular()) {
        $post_type = get_post_type();
        if ($post_type !== 'post') {
            // カスタム投稿の場合：アーカイブページへのリンクを挟む
            $post_type_object = get_post_type_object($post_type);
            $archive_link = get_post_type_archive_link($post_type);
            if ($archive_link) {
                $add_item($post_type_object->label, $archive_link);
            }
        } else {
            // 通常の投稿の場合：カテゴリーへのリンクを挟む
            $category = get_the_category();
            if (!empty($category)) {
                $add_item($category[0]->name, get_category_link($category[0]->term_id));
            }
        }
        // 現在の記事（最後）
        $add_item(get_the_title(), get_permalink(), true);
    }
    // 階層2以降: アーカイブページ（一覧）
    elseif (is_archive()) {
        $add_item(get_the_archive_title(), '', true);
    }
    // 階層2以降: 検索結果
    elseif (is_search()) {
        $add_item('「' . get_search_query() . '」の検索結果', '', true);
    }
    // 階層2以降: 404ページ
    elseif (is_404()) {
        $add_item('ページが見つかりません', '', true);
    }

    $html .= '</ol></div>';


    // --- 最終出力 ---

    // 1. 人間用のHTMLを出力
    echo $html;

    // 2. 検索エンジン用のJSON-LDを出力
    echo '<script type="application/ld+json">';
    echo wp_json_encode($json_ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo '</script>';
}


//* ===============================================
//# 5. FAQ構造化データ（FAQPage JSON-LD）自動生成
//* ===============================================

function helixia_faq_schema()
{
    // 個別ページ（投稿・固定ページ）以外では出力しない
    if (!is_singular()) {
        return;
    }

    global $post;
    if (!$post || empty($post->post_content)) {
        return;
    }

    $content = $post->post_content;

    // <details><summary>質問</summary>回答</details> のパターンを正規表現で検出
    $pattern = '/<details[^>]*>\s*<summary[^>]*>(.*?)<\/summary>(.*?)<\/details>/si';

    if (!preg_match_all($pattern, $content, $matches, PREG_SET_ORDER)) {
        return; // FAQ項目が見つからなければ何もしない
    }

    $faq_items = array();

    foreach ($matches as $match) {
        $question = wp_strip_all_tags(trim($match[1]));
        $answer = wp_strip_all_tags(trim($match[2]));

        // 空の質問や回答はスキップ
        if (empty($question) || empty($answer)) {
            continue;
        }

        $faq_items[] = array(
            '@type' => 'Question',
            'name' => $question,
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text' => $answer,
            ),
        );
    }

    // FAQ項目が1つもなければ出力しない
    if (empty($faq_items)) {
        return;
    }

    $schema = array(
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faq_items,
    );

    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    echo "\n" . '</script>' . "\n";
}
add_action('wp_head', 'helixia_faq_schema', 3);


//* ===============================================
//# 6. 動的 sitemap.xml のカスタマイズ
//* ===============================================

// 添付ファイルを除外
function helixia_sitemap_exclude_post_types($post_types)
{
    unset($post_types['attachment']);
    return $post_types;
}
add_filter('wp_sitemaps_post_types', 'helixia_sitemap_exclude_post_types');

// ユーザーサイトマップを完全無効化（セキュリティ対策）
function helixia_sitemap_exclude_users($provider, $name)
{
    if ($name === 'users') {
        return false;
    }
    return $provider;
}
add_filter('wp_sitemaps_add_provider', 'helixia_sitemap_exclude_users', 10, 2);


/**
 * 各URLエントリーに lastmod（最終更新日）を正確に付与
 */
function helixia_sitemap_entry_extra($entry, $post_type, $post)
{
    // lastmod を投稿の最終更新日に設定
    $entry['lastmod'] = get_the_modified_date('c', $post);

    return $entry;
}
add_filter('wp_sitemaps_posts_entry', 'helixia_sitemap_entry_extra', 10, 3);


/**
 * 1つのサイトマップあたりの最大URL数を調整
 */
function helixia_sitemap_max_urls($max_urls)
{
    return 1000;
}
add_filter('wp_sitemaps_max_urls', 'helixia_sitemap_max_urls');


/**
 * llms.txt や固定ページをサイトマップに確実に含める
 */
function helixia_sitemap_additional_urls($url_list, $post_type, $page_num)
{
    // 最初のページにだけ追加
    if ($page_num !== 1 || $post_type !== 'page') {
        return $url_list;
    }

    // llms.txt をサイトマップに追加
    $url_list[] = array(
        'loc' => home_url('/llms.txt'),
    );

    return $url_list;
}
add_filter('wp_sitemaps_posts_pre_url_list', 'helixia_sitemap_additional_urls', 10, 3);
