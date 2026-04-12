<?php
/**
 * 実績一覧のAjaxフィルタリング処理
 */

function helixia_ajax_filter_works() {
    // セキュリティチェック（Nonce）
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'helixia_ajax_nonce')) {
        wp_send_json_error('不正なリクエストです。');
    }

    $cat_slug = isset($_POST['cat']) ? sanitize_text_field($_POST['cat']) : 'all';
    
    $args = array(
        'post_type' => 'works',
        'posts_per_page' => 9,
        'orderby' => 'date',
        'order' => 'DESC',
    );

    // カテゴリ絞り込み
    if ($cat_slug !== 'all') {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'works-cat',
                'field'    => 'slug',
                'terms'    => $cat_slug,
            ),
        );
    }

    $works_query = new WP_Query($args);

    ob_start();
    if ($works_query->have_posts()) :
        while ($works_query->have_posts()) : $works_query->the_post();
            $thumb_url = helixia_get_works_thumbnail_src(get_the_ID(), 'large');
            ?>
            <article class="p-home-works__item p-work-card">
                <a href="<?php the_permalink(); ?>" class="p-work-card__link">
                    <div class="p-work-card__thumb">
                        <img src="<?php echo esc_url($thumb_url); ?>" alt="<?php the_title(); ?>" loading="lazy">
                    </div>
                    <div class="p-work-card__overlay">
                        <div class="p-work-card__info">
                            <h3 class="p-work-card__title">
                                <?php the_title(); ?>
                            </h3>
                            <span class="p-work-card__more">VIEW MORE</span>
                        </div>
                    </div>
                </a>
            </article>
            <?php
        endwhile;
    else :
        echo '<p class="p-home-works__empty">該当する作品は見つかりませんでした。</p>';
    endif;
    wp_reset_postdata();

    $html = ob_get_clean();
    wp_send_json_success($html);
}

// ログインユーザー用と非ログインユーザー用の両方に登録
add_action('wp_ajax_helixia_filter_works', 'helixia_ajax_filter_works');
add_action('wp_ajax_nopriv_helixia_filter_works', 'helixia_ajax_filter_works');
