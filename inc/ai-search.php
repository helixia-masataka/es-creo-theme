<?php
//* ===============================================
//# AI検索最適化（クローラー制御 + llms.txt）
//* ===============================================
//
// 【このファイルの特徴】
// 現代のSEO（AIO・SGE対策）に対応した先進的な機能を持っています。
//
// 1. AIクローラー制御（robots.txt 動的拡張）
//    - ChatGPT､Claude､Gemini､Perplexity などの生成AI bot（クローラー）のアクセスを許可・制御するための記述を robots.txt へ動的に追加します。
// 2. llms.txt 動的生成（AI向けサイト情報）
//    - 生成AIがサイト情報を把握しやすいよう､サイト名､主要ページ､最新投稿などをまとめた `llms.txt` を自動生成し `/llms.txt` で提供します。
//

//* ===============================================
//# 1. AIクローラー制御（robots.txt 動的拡張）
//* ===============================================

function helixia_ai_crawlers_robots_txt($output, $public)
{
    // サイトが検索エンジンに公開されていない場合は何も追加しない
    if ('0' === $public) {
        return $output;
    }

    $ai_rules = "\n";
    $ai_rules .= "# ===============================================\n";
    $ai_rules .= "# AI Crawler Control (by Helixia Theme)\n";
    $ai_rules .= "# ===============================================\n\n";

    // --- OpenAI ---
    $ai_rules .= "# OpenAI (ChatGPT / GPT検索)\n";
    $ai_rules .= "User-agent: GPTBot\n";
    $ai_rules .= "Allow: /\n\n";

    $ai_rules .= "User-agent: ChatGPT-User\n";
    $ai_rules .= "Allow: /\n\n";

    // --- Anthropic ---
    $ai_rules .= "# Anthropic (Claude)\n";
    $ai_rules .= "User-agent: ClaudeBot\n";
    $ai_rules .= "Allow: /\n\n";

    // --- Google AI ---
    $ai_rules .= "# Google AI (Gemini / AI Overview)\n";
    $ai_rules .= "User-agent: Google-Extended\n";
    $ai_rules .= "Allow: /\n\n";

    // --- Perplexity ---
    $ai_rules .= "# Perplexity\n";
    $ai_rules .= "User-agent: PerplexityBot\n";
    $ai_rules .= "Allow: /\n\n";

    // --- Apple ---
    $ai_rules .= "# Apple Intelligence\n";
    $ai_rules .= "User-agent: Applebot-Extended\n";
    $ai_rules .= "Allow: /\n\n";

    // --- Meta ---
    $ai_rules .= "# Meta AI\n";
    $ai_rules .= "User-agent: Meta-ExternalAgent\n";
    $ai_rules .= "Allow: /\n\n";

    // --- Common Crawl (多くのAIの学習データ元) ---
    $ai_rules .= "# Common Crawl (AI学習データソース)\n";
    $ai_rules .= "User-agent: CCBot\n";
    $ai_rules .= "Allow: /\n\n";

    // --- llms.txt の参照先を明示 ---
    $ai_rules .= "# AI向けサイト情報ファイル\n";
    $ai_rules .= "# llms.txt: " . home_url('/llms.txt') . "\n";

    $output .= $ai_rules;

    return $output;
}
add_filter('robots_txt', 'helixia_ai_crawlers_robots_txt', 10, 2);


//* ===============================================
//# 2. llms.txt 動的生成（AI crawler 向けサイト情報）
//* ===============================================

// カスタムクエリ変数の登録
function helixia_llms_txt_query_vars($vars)
{
    $vars[] = 'helixia_llms_txt';
    return $vars;
}
add_filter('query_vars', 'helixia_llms_txt_query_vars');

// リライトルールの追加（/llms.txt でアクセス可能にする）
function helixia_llms_txt_rewrite_rules()
{
    add_rewrite_rule('^llms\.txt$', 'index.php?helixia_llms_txt=1', 'top');
}
add_action('init', 'helixia_llms_txt_rewrite_rules');

// テンプレートの乗っ取り（llms.txt の出力処理）
function helixia_llms_txt_template_redirect()
{
    if (!get_query_var('helixia_llms_txt')) {
        return;
    }

    header('Content-Type: text/plain; charset=utf-8');
    header('X-Robots-Tag: noindex');

    $site_name = get_bloginfo('name');
    $site_desc = get_bloginfo('description');
    $site_url = home_url('/');

    // --- ヘッダー ---
    echo "# {$site_name}\n\n";

    if ($site_desc) {
        echo "> {$site_desc}\n\n";
    }

    echo "- URL: {$site_url}\n";
    echo "- 言語: 日本語\n\n";

    // --- 主要ページ（固定ページ） ---
    echo "## 主要ページ\n\n";

    $pages = get_pages(array(
        'sort_column' => 'menu_order',
        'sort_order' => 'ASC',
        'status' => 'publish',
    ));

    if ($pages) {
        foreach ($pages as $page) {
            $title = $page->post_title;
            $url = get_permalink($page->ID);
            $excerpt = '';

            // 抜粋があれば付与
            if ($page->post_excerpt) {
                $excerpt = ' - ' . wp_strip_all_tags($page->post_excerpt);
            }

            echo "- [{$title}]({$url}){$excerpt}\n";
        }
    }

    echo "\n";

    // --- 最新の投稿 ---
    echo "## 最新のお知らせ\n\n";

    $recent_posts = get_posts(array(
        'numberposts' => 10,
        'post_status' => 'publish',
        'post_type' => 'post',
        'orderby' => 'date',
        'order' => 'DESC',
    ));

    if ($recent_posts) {
        foreach ($recent_posts as $post_item) {
            $title = $post_item->post_title;
            $url = get_permalink($post_item->ID);
            $date = get_the_date('Y-m-d', $post_item->ID);
            echo "- [{$title}]({$url}) ({$date})\n";
        }
    } else {
        echo "- （まだ投稿がありません）\n";
    }

    echo "\n";

    // --- カスタム投稿タイプ ---
    $custom_post_types = get_post_types(array(
        'public' => true,
        '_builtin' => false,
    ), 'objects');

    if ($custom_post_types) {
        echo "## コンテンツ\n\n";

        foreach ($custom_post_types as $cpt) {
            $archive_url = get_post_type_archive_link($cpt->name);
            if ($archive_url) {
                echo "- [{$cpt->label}]({$archive_url})\n";
            }
        }

        echo "\n";
    }

    // --- サイトの技術情報 ---
    echo "## 技術情報\n\n";
    echo "- CMS: WordPress\n";
    echo "- テーマ: " . wp_get_theme()->get('Name') . "\n";

    // --- お問い合わせ情報 ---
    $contact_page = get_page_by_path('contact');
    if ($contact_page) {
        echo "\n## お問い合わせ\n\n";
        echo "- [お問い合わせページ](" . get_permalink($contact_page->ID) . ")\n";
    }

    exit;
}
add_action('template_redirect', 'helixia_llms_txt_template_redirect');
