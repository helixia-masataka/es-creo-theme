<?php
/**
 * Template Name:トップページ
 */
get_header();
?>


    <section class="p-home-works">
        <div class="l-container">
            <div class="p-home-works__inner">
                <?php
                $works_query = new WP_Query(array(
                    'post_type' => 'works',
                    'posts_per_page' => 9,
                    'orderby' => 'date',
                    'order' => 'DESC',
                ));
                ?>

                <?php if ($works_query->have_posts()): ?>
                    <div id="js-works-grid" class="p-home-works__grid">
                        <?php while ($works_query->have_posts()): $works_query->the_post();
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
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p class="p-home-works__empty">作品がまだ登録されていません。</p>
                <?php endif; ?>
                <?php wp_reset_postdata(); ?>
            </div>
        </div>
        
        <!-- カテゴリチップ (固定表示) -->
        <div class="p-home-categories">
            <ul class="p-home-categories__list">
                <li class="p-home-categories__item --active"><a href="#" data-cat="all">ALL</a></li>
                <li class="p-home-categories__item"><a href="#" data-cat="logo">LOGO</a></li>
                <li class="p-home-categories__item"><a href="#" data-cat="web">WEB</a></li>
                <li class="p-home-categories__item"><a href="#" data-cat="graphic">GRAPHIC</a></li>
                <li class="p-home-categories__item"><a href="#" data-cat="photo">PHOTO</a></li>
            </ul>
        </div>
    </section>


<?php
get_footer();
?>

