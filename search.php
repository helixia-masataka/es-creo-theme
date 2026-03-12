<?php
// 検索結果を表示するテンプレート
get_header();
?>

<main class="l-main">
    <div class="l-container">

        <h1 class="c-heading --h1">
            「<?php echo esc_html(get_search_query()); ?>」の検索結果
        </h1>

        <section class="l-section p-archive">

            <?php /* メインループ */ ?>
            <?php if (have_posts()): ?>

                <p class="p-archive__count">
                    <?php
                    global $wp_query;
                    echo $wp_query->found_posts;
                    ?>件の記事が見つかりました。
                </p>

                <ul class="p-archive__items">
                    <?php while (have_posts()):
                        the_post(); ?>
                        <li class="p-archive__item">
                            <article>
                                <a href="<?php the_permalink(); ?>">

                                    <div class="p-archive__thumb">
                                        <?php if (has_post_thumbnail()): ?>
                                            <?php the_post_thumbnail('medium'); ?>
                                        <?php else: ?>
                                            <img src="<?php echo esc_url(get_theme_file_uri('/img/no-image.webp')); ?>"
                                                alt="No Image" loading="lazy">
                                        <?php endif; ?>
                                    </div>

                                    <div class="p-archive__info">
                                        <time
                                            datetime="<?php echo get_the_date('Y-m-d'); ?>"><?php echo get_the_date('Y.m.d'); ?></time>
                                        <h2><?php the_title(); ?></h2>
                                    </div>

                                </a>
                            </article>
                        </li>
                    <?php endwhile; ?>
                </ul>

                <div class="c-pagination">
                    <?php the_posts_pagination(); ?>
                </div>

            <?php else: ?>

                <div class="p-archive__empty">
                    <p>「<?php echo esc_html(get_search_query()); ?>」に一致する記事は見つかりませんでした。<br>別のキーワードで再度検索をお試しください。</p>

                    <div class="p-archive__search-retry">
                        <?php get_search_form(); ?>
                    </div>
                </div>

            <?php endif; ?>

        </section>

    </div>
</main>

<?php get_footer(); ?>