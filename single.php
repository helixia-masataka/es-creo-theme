<?php
// 通常の投稿（お知らせやブログ）の詳細ページ
get_header();
?>

<div class="l-container">
    <section class="l-section p-single">
        <?php /* メインループ */ ?>
        <?php if (have_posts()): ?>
            <?php while (have_posts()):
                the_post(); ?>
                <article id="post-<?php the_ID(); ?>" <?php post_class('p-single__article'); ?>>
                    <header class="p-single__header">
                        <div class="p-single__meta">
                            <time datetime="<?php echo get_the_date('Y-m-d'); ?>" class="p-single__date">
                                <?php echo get_the_date('Y.m.d'); ?>
                            </time>
                            <div class="p-single__category">
                                <?php the_category(', '); ?>
                            </div>
                        </div>
                        <h1 class="c-heading --h1 p-single__title"><?php the_title(); ?></h1>
                    </header>

                    <div class="p-single__thumbnail">
                        <?php if (has_post_thumbnail()): ?>
                            <?php the_post_thumbnail('large'); ?>
                        <?php else: ?>
                            <img src="<?php echo esc_url(get_theme_file_uri('/img/no-image.webp')); ?>" alt="No Image">
                        <?php endif; ?>
                    </div>

                    <div class="p-single__content">
                        <?php the_content(); ?>
                    </div>
                </article>

                <div class="c-pagination">
                    <?php
                    the_post_navigation(array(
                        'prev_text' => '← 前の記事へ',
                        'next_text' => '次の記事へ →',
                    ));
                    ?>
                </div>

            <?php endwhile; ?>
        <?php endif; ?>
        <?php ?>
    </section>
</div>

<?php get_footer(); ?>