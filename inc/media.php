<?php
//* ===============================================
//# メディアサイト向け表示機能
//* ===============================================
//
// 【このファイルの特徴】
// オウンドメディアやブログの回遊率・ユーザビリティを高めるための多様なUI機能を提供します。
//
// 1. 目次（Table of Contents）の自動生成（h2, h3から生成）
// 2. 読了時間の自動表示 + プログレスバー（スクロール連動）
// 3. 関連記事の自動表示（カテゴリー連動での回遊率アップ）
// 4. SNSシェアボタン（各種SNSへのリンク）
// 5. 外部リンクの自動 target="_blank" 付与
// 6. 日本語抜粋の最適化（マルチバイト対応）
// 7. 公開日・更新日の両方表示
// 8. ブログカード（内部/外部リンクのカードUIへの自動変換）
// 9. 著者情報ボックス（E-E-A-T対策による権威性アピール）
// 10. PR表記（2023年施行のステマ規制法に準拠）
// 11. 人気記事ランキング（PV数ベースの表示）
//

//* ===============================================
//# 1. 目次（Table of Contents）の自動生成
//* ===============================================

function helixia_auto_toc($content)
{
    // 個別ページ（投稿・固定ページ）以外では処理しない
    if (!is_singular()) {
        return $content;
    }

    // h2, h3 を正規表現で検出
    $pattern = '/<(h[23])(.*?)>(.*?)<\/\1>/i';
    preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

    // 見出しが2つ未満なら目次を生成しない
    if (count($matches) < 2) {
        return $content;
    }

    // 目次HTML生成
    $toc = '<nav class="c-toc" aria-label="目次">';
    $toc .= '<details class="c-toc__details" open>';
    $toc .= '<summary class="c-toc__title">目次</summary>';
    $toc .= '<ol class="c-toc__list">';

    $counter = 0;
    $in_sub_list = false;

    foreach ($matches as $match) {
        $tag = strtolower($match[1]); // h2 or h3
        $heading_text = wp_strip_all_tags($match[3]);
        $anchor_id = 'toc-' . $counter;
        $counter++;

        // h2：通常のリスト項目
        if ($tag === 'h2') {
            if ($in_sub_list) {
                $toc .= '</ol></li>';
                $in_sub_list = false;
            }
            $toc .= '<li class="c-toc__item"><a href="#' . esc_attr($anchor_id) . '">' . esc_html($heading_text) . '</a>';
        }

        // h3：サブリスト
        if ($tag === 'h3') {
            if (!$in_sub_list) {
                $toc .= '<ol class="c-toc__sublist">';
                $in_sub_list = true;
            }
            $toc .= '<li class="c-toc__subitem"><a href="#' . esc_attr($anchor_id) . '">' . esc_html($heading_text) . '</a></li>';
        }

        // 本文内の見出しに id を付与
        $new_heading = '<' . $match[1] . $match[2] . ' id="' . esc_attr($anchor_id) . '">' . $match[3] . '</' . $match[1] . '>';
        $content = str_replace($match[0], $new_heading, $content);
    }

    if ($in_sub_list) {
        $toc .= '</ol></li>';
    }

    $toc .= '</ol></details></nav>';

    // 目次を最初の <h2> の前に挿入
    $first_h2_pos = strpos($content, '<h2');
    if ($first_h2_pos !== false) {
        $content = substr_replace($content, $toc, $first_h2_pos, 0);
    } else {
        $content = $toc . $content;
    }

    return $content;
}
add_filter('the_content', 'helixia_auto_toc', 12);


//* ===============================================
//# 2. 読了時間の自動表示 + プログレスバー
//* ===============================================

/**
 * 投稿の読了時間を返す（日本語: 400文字/分）
 * テンプレートで使用: <?php echo helixia_reading_time(); ?>
 */
function helixia_reading_time($post_id = null)
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $content = get_post_field('post_content', $post_id);
    $content = wp_strip_all_tags($content);
    $char_count = mb_strlen($content, 'UTF-8');

    // 日本語は400文字/分、最低1分
    $minutes = max(1, ceil($char_count / 400));

    return '<span class="c-reading-time">📖 この記事は約' . esc_html($minutes) . '分で読めます</span>';
}

/**
 * スクロール進捗バー用のCSS/JSをフッターに出力
 */
function helixia_reading_progress_bar()
{
    if (!is_singular('post')) {
        return;
    }

    echo <<<'EOD'
<style>
.c-progress-bar{position:fixed;top:0;left:0;width:0;height:3px;background:var(--color-primary, #2563eb);z-index:9999;transition:width .1s linear}
</style>
<div class="c-progress-bar" id="js-progress-bar"></div>
<script>
(function(){
    var bar=document.getElementById('js-progress-bar');
    if(!bar)return;
    var ticking=false;
    window.addEventListener('scroll',function(){
        if(!ticking){
            requestAnimationFrame(function(){
                var h=document.documentElement;
                var scrollTop=window.pageYOffset||h.scrollTop;
                var scrollHeight=h.scrollHeight-h.clientHeight;
                var pct=scrollHeight>0?(scrollTop/scrollHeight)*100:0;
                bar.style.width=pct+'%';
                ticking=false;
            });
            ticking=true;
        }
    });
})();
</script>
EOD;
}
add_action('wp_footer', 'helixia_reading_progress_bar');


//* ===============================================
//# 3. 関連記事の自動表示
//* ===============================================

/**
 * 同カテゴリー → 同タグの順で関連記事を最大4件取得して表示
 * テンプレートで使用: <?php helixia_related_posts(); ?>
 */
function helixia_related_posts($max_posts = 4)
{
    if (!is_singular('post')) {
        return;
    }

    global $post;
    $current_id = $post->ID;
    $related_ids = array();

    // 1. 同じカテゴリーの記事を取得
    $categories = get_the_category($current_id);
    if (!empty($categories)) {
        $cat_ids = wp_list_pluck($categories, 'term_id');
        $cat_posts = get_posts(array(
            'category__in'   => $cat_ids,
            'post__not_in'   => array($current_id),
            'posts_per_page' => $max_posts,
            'orderby'        => 'date',
            'order'          => 'DESC',
            'fields'         => 'ids',
        ));
        $related_ids = array_merge($related_ids, $cat_posts);
    }

    // 2. 足りなければ同じタグの記事を追加
    if (count($related_ids) < $max_posts) {
        $tags = get_the_tags($current_id);
        if (!empty($tags)) {
            $tag_ids = wp_list_pluck($tags, 'term_id');
            $tag_posts = get_posts(array(
                'tag__in'        => $tag_ids,
                'post__not_in'   => array_merge(array($current_id), $related_ids),
                'posts_per_page' => $max_posts - count($related_ids),
                'orderby'        => 'date',
                'order'          => 'DESC',
                'fields'         => 'ids',
            ));
            $related_ids = array_merge($related_ids, $tag_posts);
        }
    }

    // 記事がなければ何も出力しない
    if (empty($related_ids)) {
        return;
    }

    // 重複排除して最大件数に制限
    $related_ids = array_unique($related_ids);
    $related_ids = array_slice($related_ids, 0, $max_posts);

    echo '<section class="c-related-posts">';
    echo '<h2 class="c-related-posts__title">関連記事</h2>';
    echo '<ul class="c-related-posts__list">';

    foreach ($related_ids as $rid) {
        $title = get_the_title($rid);
        $url = get_permalink($rid);
        $date = get_the_date('Y.m.d', $rid);
        $thumb = has_post_thumbnail($rid)
            ? get_the_post_thumbnail($rid, 'medium', array('class' => 'c-related-posts__img', 'loading' => 'lazy'))
            : '';

        echo '<li class="c-related-posts__item">';
        echo '<a href="' . esc_url($url) . '" class="c-related-posts__link">';
        if ($thumb) {
            echo $thumb;
        }
        echo '<div class="c-related-posts__body">';
        echo '<time class="c-related-posts__date" datetime="' . esc_attr(get_the_date('c', $rid)) . '">' . esc_html($date) . '</time>';
        echo '<span class="c-related-posts__name">' . esc_html($title) . '</span>';
        echo '</div></a></li>';
    }

    echo '</ul></section>';
}


//* ===============================================
//# 4. SNSシェアボタン
//* ===============================================

/**
 * X / Facebook / LINE / はてなブックマーク のシェアボタンを生成
 * テンプレートで使用: <?php helixia_sns_share_buttons(); ?>
 */
function helixia_sns_share_buttons()
{
    if (!is_singular()) {
        return;
    }

    $url = urlencode(get_permalink());
    $title = urlencode(get_the_title());

    $buttons = array(
        array(
            'name'  => 'X',
            'class' => '--x',
            'url'   => 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title,
            'label' => 'Xでシェア',
        ),
        array(
            'name'  => 'Facebook',
            'class' => '--facebook',
            'url'   => 'https://www.facebook.com/sharer/sharer.php?u=' . $url,
            'label' => 'Facebookでシェア',
        ),
        array(
            'name'  => 'LINE',
            'class' => '--line',
            'url'   => 'https://social-plugins.line.me/lineit/share?url=' . $url,
            'label' => 'LINEで送る',
        ),
        array(
            'name'  => 'はてブ',
            'class' => '--hatena',
            'url'   => 'https://b.hatena.ne.jp/entry/' . rawurlencode(get_permalink()),
            'label' => 'はてなブックマークに追加',
        ),
    );

    echo '<div class="c-sns-share">';
    echo '<span class="c-sns-share__label">この記事をシェア</span>';
    echo '<ul class="c-sns-share__list">';

    foreach ($buttons as $btn) {
        echo '<li class="c-sns-share__item">';
        echo '<a href="' . esc_url($btn['url']) . '" class="c-sns-share__link ' . esc_attr($btn['class']) . '" target="_blank" rel="noopener noreferrer" aria-label="' . esc_attr($btn['label']) . '">';
        echo esc_html($btn['name']);
        echo '</a></li>';
    }

    echo '</ul></div>';
}


//* ===============================================
//# 5. 外部リンクの自動 target="_blank"
//* ===============================================

function helixia_external_links($content)
{
    if (!is_singular()) {
        return $content;
    }

    $site_url = home_url();

    // <a> タグを正規表現で検出
    $content = preg_replace_callback(
        '/<a\s([^>]*href=["\']([^"\']*)["\'][^>]*)>/i',
        function ($matches) use ($site_url) {
            $full_tag = $matches[0];
            $href = $matches[2];

            // 自サイトのURLやアンカーリンク、tel:、mailto: はスキップ
            if (
                strpos($href, $site_url) === 0 ||
                strpos($href, '#') === 0 ||
                strpos($href, 'tel:') === 0 ||
                strpos($href, 'mailto:') === 0 ||
                strpos($href, '/') === 0
            ) {
                return $full_tag;
            }

            // 外部リンクの場合
            // すでに target が指定されていなければ追加
            if (strpos($full_tag, 'target=') === false) {
                $full_tag = str_replace('<a ', '<a target="_blank" ', $full_tag);
            }

            // すでに rel が指定されていれば noopener を追加、なければ新規追加
            if (strpos($full_tag, 'rel=') !== false) {
                // noopener が未指定なら追加
                if (strpos($full_tag, 'noopener') === false) {
                    $full_tag = preg_replace('/rel=["\']([^"\']*)["\']/', 'rel="$1 noopener noreferrer"', $full_tag);
                }
            } else {
                $full_tag = str_replace('<a ', '<a rel="noopener noreferrer" ', $full_tag);
            }

            return $full_tag;
        },
        $content
    );

    return $content;
}
add_filter('the_content', 'helixia_external_links', 20);


//* ===============================================
//# 6. 日本語抜粋の最適化
//* ===============================================

// 抜粋の長さを日本語に最適化（120文字）
function helixia_excerpt_length($length)
{
    return 120;
}
add_filter('excerpt_length', 'helixia_excerpt_length');

// 抜粋の末尾を「…」に統一
function helixia_excerpt_more($more)
{
    return '…';
}
add_filter('excerpt_more', 'helixia_excerpt_more');

/**
 * 手動抜粋がない場合、本文の最初の段落から自動生成
 * HTMLタグを除去して120文字に切り詰める
 */
function helixia_smart_excerpt($excerpt)
{
    if (has_excerpt()) {
        return $excerpt;
    }

    global $post;
    if (!$post) {
        return $excerpt;
    }

    $content = $post->post_content;
    $content = wp_strip_all_tags($content);
    $content = str_replace(array("\r\n", "\r", "\n"), '', $content);

    if (mb_strlen($content, 'UTF-8') > 120) {
        $content = mb_substr($content, 0, 120, 'UTF-8') . '…';
    }

    return $content;
}
add_filter('get_the_excerpt', 'helixia_smart_excerpt', 10);


//* ===============================================
//# 7. 公開日・更新日の両方表示
//* ===============================================

/**
 * 公開日と更新日を <time> タグ付きで出力する
 * テンプレートで使用: <?php echo helixia_post_dates(); ?>
 *
 * 出力例:
 * <span class="c-post-dates">
 *   <time class="c-post-dates__published" datetime="2026-03-01T00:00:00+09:00">2026.03.01</time>
 *   <time class="c-post-dates__modified" datetime="2026-03-10T12:00:00+09:00">（更新: 2026.03.10）</time>
 * </span>
 */
function helixia_post_dates($post_id = null)
{
    if (!$post_id) {
        $post_id = get_the_ID();
    }

    $published = get_the_date('c', $post_id);
    $modified = get_the_modified_date('c', $post_id);
    $pub_display = get_the_date('Y.m.d', $post_id);
    $mod_display = get_the_modified_date('Y.m.d', $post_id);

    $html = '<span class="c-post-dates">';
    $html .= '<time class="c-post-dates__published" datetime="' . esc_attr($published) . '">' . esc_html($pub_display) . '</time>';

    // 公開日と更新日が異なる場合のみ更新日を表示
    if ($pub_display !== $mod_display) {
        $html .= ' <time class="c-post-dates__modified" datetime="' . esc_attr($modified) . '">（更新: ' . esc_html($mod_display) . '）</time>';
    }

    $html .= '</span>';

    return $html;
}


//* ===============================================
//# 8. ブログカード（内部/外部リンクのカードUI化）
//* ===============================================

/**
 * the_content フィルターで「行に URL だけが書かれている」パターンを検出し、
 * カード型UIに自動変換する。OGP情報を取得して表示。
 *
 * 対象: 投稿本文内の独立した行にある URL（<p>https://...</p> パターン）
 * 除外: YouTube / Twitter など oEmbed で処理される URL
 */
function helixia_blogcard($content)
{
    if (!is_singular()) {
        return $content;
    }

    // <p>タグで囲まれた単独のURLを検出
    $pattern = '/<p>\s*(https?:\/\/[^\s<>"]+)\s*<\/p>/i';

    $content = preg_replace_callback($pattern, function ($matches) {
        $url = $matches[1];

        // oEmbed対象（YouTube, Twitter等）はスキップ
        $oembed_hosts = array('youtube.com', 'youtu.be', 'twitter.com', 'x.com', 'vimeo.com', 'instagram.com', 'tiktok.com');
        foreach ($oembed_hosts as $host) {
            if (strpos($url, $host) !== false) {
                return $matches[0];
            }
        }

        // 内部リンクかどうか判定
        $is_internal = (strpos($url, home_url()) === 0);

        // 内部リンク: WordPress の投稿データから情報取得
        if ($is_internal) {
            $post_id = url_to_postid($url);
            if ($post_id) {
                $title = get_the_title($post_id);
                $excerpt = get_the_excerpt($post_id);
                if (empty($excerpt)) {
                    $excerpt = mb_substr(wp_strip_all_tags(get_post_field('post_content', $post_id)), 0, 100, 'UTF-8') . '…';
                }
                $thumb = has_post_thumbnail($post_id)
                    ? get_the_post_thumbnail_url($post_id, 'medium')
                    : '';
                $site_name = get_bloginfo('name');

                return helixia_render_blogcard($url, $title, $excerpt, $thumb, $site_name, true);
            }
        }

        // 外部リンク: URLからタイトルを推測
        $parsed = parse_url($url);
        $domain = isset($parsed['host']) ? $parsed['host'] : $url;

        // 外部サイトのHTMLを取得してタイトルを抽出（キャッシュ付き）
        $cache_key = 'helixia_bc_' . md5($url);
        $cached = get_transient($cache_key);

        if ($cached !== false) {
            $title = $cached['title'];
            $excerpt = $cached['excerpt'];
            $thumb = $cached['thumb'];
        } else {
            $title = $domain;
            $excerpt = '';
            $thumb = '';

            $response = wp_remote_get($url, array(
                'timeout'   => 5,
                'sslverify' => false,
                'user-agent' => 'Mozilla/5.0 (compatible; WordPressBlogCard)',
            ));

            if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
                $body = wp_remote_retrieve_body($response);

                // <title> タグを抽出
                if (preg_match('/<title[^>]*>([^<]+)<\/title>/i', $body, $m)) {
                    $title = html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8');
                }

                // og:description
                if (preg_match('/<meta[^>]+property=["\']og:description["\'][^>]+content=["\']([^"\']+)["\']/i', $body, $m)) {
                    $excerpt = mb_substr(html_entity_decode(trim($m[1]), ENT_QUOTES, 'UTF-8'), 0, 100, 'UTF-8');
                }

                // og:image
                if (preg_match('/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i', $body, $m)) {
                    $thumb = trim($m[1]);
                }
            }

            // 24時間キャッシュ
            set_transient($cache_key, array(
                'title'   => $title,
                'excerpt' => $excerpt,
                'thumb'   => $thumb,
            ), DAY_IN_SECONDS);
        }

        return helixia_render_blogcard($url, $title, $excerpt, $thumb, $domain, false);
    }, $content);

    return $content;
}
add_filter('the_content', 'helixia_blogcard', 8);

/**
 * ブログカードHTMLを生成するヘルパー
 */
function helixia_render_blogcard($url, $title, $excerpt, $thumb, $site_name, $is_internal)
{
    $badge = $is_internal ? '内部リンク' : '外部リンク';
    $target = $is_internal ? '' : ' target="_blank" rel="noopener noreferrer"';

    $html = '<div class="c-blogcard' . ($is_internal ? ' --internal' : ' --external') . '">';
    $html .= '<a href="' . esc_url($url) . '" class="c-blogcard__link"' . $target . '>';

    if (!empty($thumb)) {
        $html .= '<figure class="c-blogcard__thumb"><img src="' . esc_url($thumb) . '" alt="" loading="lazy" decoding="async"></figure>';
    }

    $html .= '<div class="c-blogcard__body">';
    $html .= '<span class="c-blogcard__title">' . esc_html($title) . '</span>';
    if (!empty($excerpt)) {
        $html .= '<span class="c-blogcard__excerpt">' . esc_html($excerpt) . '</span>';
    }
    $html .= '<span class="c-blogcard__meta">';
    $html .= '<span class="c-blogcard__site">' . esc_html($site_name) . '</span>';
    $html .= '<span class="c-blogcard__badge">' . esc_html($badge) . '</span>';
    $html .= '</span>';
    $html .= '</div></a></div>';

    return $html;
}


//* ===============================================
//# 9. 著者情報ボックス（E-E-A-T対策）
//* ===============================================

/**
 * 記事末に著者のプロフィールを表示する
 * WordPress のユーザー設定（プロフィール画像 + 紹介文）を使用
 * テンプレートで使用: <?php helixia_author_box(); ?>
 */
function helixia_author_box()
{
    if (!is_singular('post')) {
        return;
    }

    global $post;
    $author_id = $post->post_author;
    $name = get_the_author_meta('display_name', $author_id);
    $bio = get_the_author_meta('description', $author_id);
    $avatar = get_avatar($author_id, 96, '', $name, array('class' => 'c-author-box__avatar'));
    $posts_url = get_author_posts_url($author_id);

    // プロフィールが未入力なら非表示
    if (empty($bio)) {
        return;
    }

    echo '<aside class="c-author-box" aria-label="著者情報">';
    echo '<div class="c-author-box__icon">' . $avatar . '</div>';
    echo '<div class="c-author-box__body">';
    echo '<span class="c-author-box__label">この記事を書いた人</span>';
    echo '<a href="' . esc_url($posts_url) . '" class="c-author-box__name">' . esc_html($name) . '</a>';
    echo '<p class="c-author-box__bio">' . esc_html($bio) . '</p>';
    echo '</div></aside>';
}


//* ===============================================
//# 10. PR表記（ステマ規制法対応・2023年10月施行）
//* ===============================================

/**
 * 景品表示法（ステルスマーケティング規制）対応
 * 投稿に「PR」「広告」カスタムフィールドが設定されている場合に
 * 記事の先頭にPR表記を自動挿入する
 *
 * 使い方:
 *   投稿編集画面 → カスタムフィールド「is_pr」に「1」を設定
 *   または: <?php helixia_pr_label(); ?> をテンプレートで呼び出し
 */
function helixia_pr_content_filter($content)
{
    if (!is_singular('post')) {
        return $content;
    }

    global $post;
    $is_pr = get_post_meta($post->ID, 'is_pr', true);

    if (!$is_pr) {
        return $content;
    }

    $pr_label = '<div class="c-pr-label" aria-label="PR表記">';
    $pr_label .= '<span class="c-pr-label__badge">PR</span>';
    $pr_label .= '<span class="c-pr-label__text">本ページはプロモーションが含まれています</span>';
    $pr_label .= '</div>';

    return $pr_label . $content;
}
add_filter('the_content', 'helixia_pr_content_filter', 1);

/**
 * PR表記をテンプレートから直接呼び出す場合
 */
function helixia_pr_label()
{
    global $post;
    if (!$post) return;

    $is_pr = get_post_meta($post->ID, 'is_pr', true);
    if (!$is_pr) return;

    echo '<div class="c-pr-label" aria-label="PR表記">';
    echo '<span class="c-pr-label__badge">PR</span>';
    echo '<span class="c-pr-label__text">本ページはプロモーションが含まれています</span>';
    echo '</div>';
}


//* ===============================================
//# 11. 人気記事ランキング（PV数ベース）
//* ===============================================

/**
 * ページビュー（PV）をカウントする
 * シングルページが表示された際に実行（ボットは除外）
 */
function helixia_track_post_views()
{
    if (!is_singular('post')) {
        return;
    }

    $post_id = get_the_ID();
    $meta_key = 'helixia_post_views_count';

    // ボット・クローラーを除外
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $bots = array('bot', 'spider', 'crawler', 'slurp', 'google', 'bing', 'yahoo', 'duckduckgo', 'baiduspider', 'yandex', 'sogou', 'exabot', 'facebot', 'ia_archiver');
    foreach ($bots as $bot) {
        if (stripos($user_agent, $bot) !== false) {
            return;
        }
    }

    $count = get_post_meta($post_id, $meta_key, true);
    if ($count === '') {
        $count = 0;
        delete_post_meta($post_id, $meta_key);
        add_post_meta($post_id, $meta_key, '1');
    } else {
        $count++;
        update_post_meta($post_id, $meta_key, $count);
    }
}
add_action('wp_head', 'helixia_track_post_views');

/**
 * 人気記事ランキングを表示する
 * テンプレートで使用: <?php helixia_popular_posts(5); ?>
 */
function helixia_popular_posts($max_posts = 5)
{
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => $max_posts,
        'meta_key'       => 'helixia_post_views_count',
        'orderby'        => 'meta_value_num',
        'order'          => 'DESC',
    );

    $popular_posts = new WP_Query($args);

    if ($popular_posts->have_posts()) :
        echo '<section class="c-popular-posts">';
        echo '<h2 class="c-popular-posts__title">人気記事ランキング</h2>';
        echo '<ul class="c-popular-posts__list">';

        $rank = 1;
        while ($popular_posts->have_posts()) : $popular_posts->the_post();
            $thumb = has_post_thumbnail()
                ? get_the_post_thumbnail(get_the_ID(), 'medium', array('class' => 'c-popular-posts__img', 'loading' => 'lazy'))
                : '';
            
            echo '<li class="c-popular-posts__item">';
            echo '<a href="' . esc_url(get_permalink()) . '" class="c-popular-posts__link">';
            
            if ($thumb) {
                echo '<figure class="c-popular-posts__thumb">';
                echo $thumb;
                echo '<span class="c-popular-posts__rank --rank-' . $rank . '">' . $rank . '</span>';
                echo '</figure>';
            }
            
            echo '<div class="c-popular-posts__body">';
            echo '<span class="c-popular-posts__name">' . esc_html(get_the_title()) . '</span>';
            echo '<span class="c-popular-posts__meta">' . esc_html(number_format((int)get_post_meta(get_the_ID(), 'helixia_post_views_count', true))) . ' views</span>';
            echo '</div></a></li>';
            
            $rank++;
        endwhile;

        echo '</ul></section>';
        wp_reset_postdata();
    endif;
}




