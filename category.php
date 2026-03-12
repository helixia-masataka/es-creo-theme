<?php
/**
 * Template Name:カテゴリー一覧
 **/
get_header();
?>
<div class="l-container">
    <h1 class="c-heading --h1"><?php single_cat_title(); ?></h1>
    <section class="l-section p-archive">
        <?php /* メインループ */ ?>
        <?php if (have_posts()): ?>
            <ul class="p-archive__items">
                <?php while (have_posts()):
                    the_post(); ?>
                    <li class="p-archive__item">
                        <article>
                            <a href="<?php the_permalink(); ?>">
                                <?php
                                if (has_post_thumbnail()) {
                                    the_post_thumbnail('medium');
                                } else {
                                    echo '<img src="' . esc_url(get_theme_file_uri('/img/no-image.webp')) . '" alt="No Image">';
                                }
                                ?>
                                <h2><?php the_title(); ?></h2>
                            </a>
                        </article>
                    </li>
                <?php endwhile; ?>
            </ul>

            <div class="c-pagination">
                <?php the_posts_pagination(); ?>
            </div>

        <?php else: ?>
            <p>まだ記事がありません。</p>
        <?php endif; ?>
    </section>
</div>

<?php get_footer(); ?>


<?php get_footer(); ?>