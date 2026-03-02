<?php
// カスタム投稿「works（制作実績など）」の詳細ページ
get_header();
?>

<div class="l-container">
    <section class="l-section p-single">
        <?php /* メインループ開始 */ ?>
        <?php if (have_posts()) : ?>
            <?php while (have_posts()) : the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('p-single__article'); ?>>
                    <header class="p-single__header">
                        <div class="p-single__meta">
                            <time datetime="<?php echo get_the_date('Y-m-d'); ?>" class="p-single__date">
                                <?php echo get_the_date('Y.m.d'); ?>
                            </time>
                            <div class="p-single__category">
                                <?php
                                // 'slug_category' を、登録したタクソノミースラッグに変更する
                                $terms = get_the_terms(get_the_ID(), 'slug_category');
                                if ($terms && ! is_wp_error($terms)) {
                                    $term_links = array();
                                    foreach ($terms as $term) {
                                        $term_links[] = '<a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a>';
                                    }
                                    echo implode(', ', $term_links);
                                }
                                ?>
                            </div>
                        </div>
                        <h1 class="c-heading --h1 p-single__title"><?php the_title(); ?></h1>
                    </header>
                    <div class="p-single__thumbnail">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('large'); ?>
                        <?php else : ?>
                            <img src="<?php echo esc_url(get_theme_file_uri('/images/no-image.png')); ?>" alt="No Image">
                        <?php endif; ?>
                    </div>
                    <div class="p-single__content">
                        <?php the_content(); ?>
                    </div>
                </article>

                <div class="c-pagination">
                    <?php
                    // カスタム投稿に合わせてテキストを変更
                    the_post_navigation(array(
                        'prev_text' => '← 前の実績へ',
                        'next_text' => '次の実績へ →',
                    ));
                    ?>
                </div>

            <?php endwhile; ?>
        <?php endif; ?>
        <?php /* メインループ終了 */ ?>

    </section>
</div>

<?php get_footer(); ?>