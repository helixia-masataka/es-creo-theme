<?php
/**
 * Template Name:トップページ
 */
global $my_mv_image_path;
global $my_mv_image_path_sp;
// メインビジュアルに使用する画像パスを登録する
$my_mv_image_path = '/img/top-mv-pc.webp';
$my_mv_image_path_sp = '/img/top-mv-sp.webp';
get_header();
?>

<div id="top" class="p-home-mv">
    <div class="p-home-mv__container">
        <div class="p-home-mv__visual">
            <?php
            $mv_url_pc = file_exists(get_theme_file_path($my_mv_image_path))
                ? get_theme_file_uri($my_mv_image_path)
                : 'https://placehold.jp/1440x720.webp?text=Main%20Visual%20PC';
            $mv_url_sp = file_exists(get_theme_file_path($my_mv_image_path_sp))
                ? get_theme_file_uri($my_mv_image_path_sp)
                : 'https://placehold.jp/750x860.webp?text=Main%20Visual%20SP';
            ?>
            <picture>
                <source media="(min-width: 768px)" srcset="<?php echo esc_url($mv_url_pc); ?>">
                <img src="<?php echo esc_url($mv_url_sp); ?>" alt="メインビジュアル" class="c-mv" <?php //c-mvは削除不可 ?>
                    width="1440" height="800" loading="eager" fetchpriority="high" decoding="sync">
            </picture>
        </div>
        <div class="p-home-mv__content">
            <h2 class="p-home-mv__copy">
                WP Theme Template<br>by Helixia.inc
            </h2>
        </div>
    </div>
</div>

<section class="l-section p-home-about">
    <div class="l-container p-home-about__contaier">
        <h2 class="p-home-about__head c-heading --h2">About Us</h2>
        <div class="p-home-about__content">
            <p>
                Helixiaは、Webテクノロジーを活用して企業の課題解決をサポートします。<br>
                最新のWordPressテーマ開発から、パフォーマンスの最適化まで幅広く対応いたします。
            </p>
            <div class="p-home-about__btn c-btn">
                <a href="<?php echo esc_url(home_url('/about/')); ?>" class="c-btn-link --accent">会社概要を見る</a>
            </div>
        </div>
    </div>
</section>

<section class="l-section p-home-news">
    <div class="l-container p-home-news__container">
        <h2 class="p-home-news__head c-heading --h2">News</h2>
        <div class="p-home-news__content">
            <?php //front-pageのためサブループ
            $news_query = new WP_Query(array(
                'post_type' => 'post', //通常の投稿
                'posts_per_page' => 3,
            ));
            ?>
            <?php if ($news_query->have_posts()): ?>
                <ul class="p-home-news__lists">
                    <?php while ($news_query->have_posts()):
                        $news_query->the_post(); ?>
                        <li class="p-home-news__list">
                            <a href="<?php the_permalink(); ?>">
                                <time
                                    datetime="<?php echo get_the_date('Y-m-d'); ?>"><?php echo get_the_date('Y.m.d'); ?></time>
                                <span class="p-home-news__title"><?php the_title(); ?></span>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else: ?>
                <p>お知らせはまだありません。</p>
            <?php endif; ?>
            <?php wp_reset_postdata(); ?>
        </div>
        <div class="p-home-news__btn c-btn">
            <a href="<?php echo esc_url(home_url('/news/')); ?>" class="c-btn-link --accent">お知らせ一覧へ</a>
        </div>
    </div>
</section>

<section class="l-section p-home-service">
    <div class="l-container l-container-cq p-home-service__container">
        <h2 class="p-home-service__head  c-heading --h2">Service</h2>
        <ul class="p-home-service__lists">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <li class="p-home-service__list p-service-card">
                    <article class="p-service-card__item">
                        <div class="p-service-card__thumb">
                            <img src="https://placehold.jp/400x300.webp?text=Service%20<?php echo $i; ?>"
                                alt="サービス<?php echo $i; ?>">
                        </div>
                        <div class="p-service-card__body">
                            <h3 class="p-service-card__head">Web制作サービス <?php echo $i; ?></h3>
                            <p class="p-service-card__text" style="">お客様のビジネス課題に合わせた、最適なWebサイトをご提案・構築いたします。</p>
                        </div>
                    </article>
                </li>
            <?php endfor; ?>
        </ul>
    </div>
</section>

<section class="l-section">
    <div class="c-cta">
        <div class="c-cta__container">
            <div class="c-cta__inner">
                <h2 class="c-cta__head c-heading --h2">Contact</h2>
                <p class="c-cta__text">
                    Webサイトに関するご相談や、お見積りのご依頼など、<br>まずはお気軽にお問い合わせください。
                </p>
                <div class="c-cta__btn c-btn">
                    <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="c-btn-link --accent">お問い合わせはこちら</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
get_footer();
?>