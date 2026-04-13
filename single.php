<?php
// 通常の投稿（お知らせやブログ）の詳細ページ
get_header();
?>

<div class="l-container">
    <section class="p-single">
        <div class="l-container-s">
            <p class="p-single__title c-heading --h2">news</p>
            <?php /* メインループ */ ?>
            <?php if (have_posts()): ?>
                <?php while (have_posts()):
                    the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('p-single__article'); ?>>
                        <header class="p-single__header">
                            <h1 class="p-single__title"><?php the_title(); ?></h1>
                            <div class="p-single__meta">
                                <time datetime="<?php echo get_the_date('Y-m-d'); ?>" class="p-single__date">
                                    <?php echo get_the_date('Y.m.d'); ?>
                                </time>
                            </div>
                        </header>
                        <div class="p-single__content">
                            <?php the_content(); ?>
                        </div>
                    </article>
                    <nav class="p-single__nav">
                        <div class="p-single__nav-inner">
                            <div class="p-single__nav-item --next">
                                <?php
                                $next_post = get_next_post();
                                if ($next_post): ?>
                                    <a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>">
                                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/img/icon-left-hand.svg" alt="" width="24" height="24">
                                        <span>newer</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="p-single__nav-item --prev">
                                <?php
                                $prev_post = get_previous_post();
                                if ($prev_post): ?>
                                    <a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>">
                                        <span>older</span>
                                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/img/icon-right-hand.svg" alt="" width="24" height="24">
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </nav>
                <?php endwhile; ?>
            <?php endif; ?>
            <?php ?>
        </div>
    </section>
</div>

<?php get_footer(); ?>
