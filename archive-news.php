<?php
/**
 * Template Name:カスタム投稿「archive-news」の一覧ページ
 */
get_header();
?>

<div class="p-news-archive">
    <div class="l-container">
        <div class="l-container-m">
            <h1 class="p-news-archive__title c-heading --h2">news</h1>

            <?php
            $paged = get_query_var('paged') ? get_query_var('paged') : 1;
            $news_query = new WP_Query(array(
                'post_type'      => 'archive-news',
                'posts_per_page' => 10,
                'orderby'        => 'date',
                'order'          => 'DESC',
                'paged'          => $paged,
            ));

            if ($news_query->have_posts()): ?>
                <div class="p-news-archive__lists">
                    <?php while ($news_query->have_posts()):
                        $news_query->the_post(); ?>
                        <article id="news-<?php echo get_the_ID(); ?>" class="p-news-archive__item">
                            <div class="p-news-archive__header">
                                <time datetime="<?php echo get_the_date('Y-m-d'); ?>"><?php echo get_the_date('Y.m.d'); ?></time>
                                <h2 class="p-news-archive__name"><?php the_title(); ?></h2>
                            </div>
                            <div class="p-news-archive__content">
                                <?php the_content(); ?>
                            </div>
                        </article>
                    <?php endwhile; ?>
                </div>

                <?php if ($news_query->max_num_pages > 1): ?>
                    <nav class="p-single__nav">
                        <div class="p-single__nav-inner">
                            <div class="p-single__nav-item --next">
                                <?php if ($paged > 1): ?>
                                    <a href="<?php echo esc_url(get_pagenum_link($paged - 1)); ?>">
                                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/img/icon-left-hand.svg" alt="" width="24" height="24">
                                        <span>newer</span>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <div class="p-single__nav-item --prev">
                                <?php if ($paged < $news_query->max_num_pages): ?>
                                    <a href="<?php echo esc_url(get_pagenum_link($paged + 1)); ?>">
                                        <span>older</span>
                                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/img/icon-right-hand.svg" alt="" width="24" height="24">
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </nav>
                <?php endif; ?>

                <div class="p-news-archive__back">
                    <a href="<?php echo esc_url(home_url('/about/#news')); ?>" class="c-btn-link">back</a>
                </div>

            <?php else: ?>
                <p>まだお知らせはありません。</p>
            <?php endif;
            wp_reset_postdata(); ?>
        </div>
    </div>
</div>

<?php get_footer(); ?>
