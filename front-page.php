<?php
/**
 * Template Name:トップページ
 */
global $my_mv_image_path;
// メインビジュアルに使用する画像パスを登録する
$my_mv_image_path = '/images/top-mv.webp';
get_header();
?>

<div id="top" class="p-home-mv">
    <div class="p-home-mv__container" style="position: relative;">
        <div class="p-home-mv__visual">
            <?php
            $mv_url = file_exists(get_theme_file_path($my_mv_image_path))
                ? get_theme_file_uri($my_mv_image_path)
                : 'https://placehold.jp/1200x630.png?text=Main%20Visual';
            ?>
            <img
                src="<?php echo esc_url($mv_url); ?>"
                alt="メインビジュアル"
                class="c-mv"
                width="1200"
                height="630"
                loading="eager"
                fetchpriority="high"
                decoding="sync"
                style="width: 100%; height: auto; display: block;"
            >
        </div>
        <div class="p-home-mv__content" style="position: absolute; top: 50%; left: 5%; transform: translateY(-50%);">
            <h2 class="p-home-mv__copy" style="font-size: 2rem; background: rgba(255,255,255,0.8); padding: 10px 20px;">
                WP Theme Template<br>by Helixia.inc
            </h2>
        </div>
    </div>
</div>

<section class="l-section p-section-1" style="padding: 60px 0;">
    <div class="l-container">
        <h2 class="c-heading --h2" style="text-align: center; margin-bottom: 30px;">About Us</h2>
        <div class="p-about__content" style="text-align: center;">
            <p style="line-height: 1.8; margin-bottom: 30px;">
                Helixiaは、Webテクノロジーを活用して企業の課題解決をサポートします。<br>
                最新のWordPressテーマ開発から、パフォーマンスの最適化まで幅広く対応いたします。
            </p>
            <div class="c-btn">
                <a href="<?php echo esc_url(home_url('/about/')); ?>" class="c-btn-link --main">会社概要を見る</a>
            </div>
        </div>
    </div>
</section>

<section class="l-section p-section-2" style="padding: 60px 0; background-color: #f9f9f9;">
    <div class="l-container">
        <h2 class="c-heading --h2" style="text-align: center; margin-bottom: 30px;">News</h2>
        <div class="p-news" style="max-width: 800px; margin: 0 auto 30px;">
            <?php
            $news_query = new WP_Query(array(
                'post_type'      => 'post',
                'posts_per_page' => 3,
            ));
            ?>
            <?php if ($news_query->have_posts()) : ?>
                <ul class="p-news__list" style="list-style: none; padding: 0;">
                    <?php while ($news_query->have_posts()) : $news_query->the_post(); ?>
                        <li class="p-news__item" style="border-bottom: 1px solid #ddd; padding: 15px 0;">
                            <a href="<?php the_permalink(); ?>" style="display: flex; gap: 15px; text-decoration: none; color: inherit;">
                                <time datetime="<?php echo get_the_date('Y-m-d'); ?>"><?php echo get_the_date('Y.m.d'); ?></time>
                                <span class="p-news__title"><?php the_title(); ?></span>
                            </a>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else : ?>
                <p style="text-align: center;">お知らせはまだありません。</p>
            <?php endif; ?>
            <?php wp_reset_postdata(); // サブループの後は必ずリセット！ ?>
        </div>
        <div class="c-btn" style="text-align: center;">
            <a href="<?php echo esc_url(home_url('/news/')); ?>" class="c-btn-link --main">お知らせ一覧へ</a>
        </div>
    </div>
</section>

<section class="l-section p-section-3" style="padding: 60px 0;">
    <div class="l-container">
        <h2 class="c-heading --h2" style="text-align: center; margin-bottom: 30px;">Service</h2>
        <div class="p-service__items" style="display: flex; gap: 20px; flex-wrap: wrap; justify-content: space-between;">
            <?php for ($i = 1; $i <= 3; $i++) : ?>
            <div class="p-card" style="width: calc(33.333% - 14px); box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                <div class="p-card__thumb">
                    <img src="https://placehold.jp/400x300.png?text=Service%20<?php echo $i; ?>" alt="サービス<?php echo $i; ?>" style="width: 100%; height: auto; display: block;">
                </div>
                <div class="p-card__body" style="padding: 20px;">
                    <h3 class="p-card__title" style="margin-bottom: 10px; font-size: 1.25rem;">Web制作サービス <?php echo $i; ?></h3>
                    <p class="p-card__text" style="font-size: 0.9rem; line-height: 1.6;">お客様のビジネス課題に合わせた、最適なWebサイトをご提案・構築いたします。</p>
                </div>
            </div>
            <?php endfor; ?>
        </div>
    </div>
</section>

<section class="l-section p-section-4" style="padding: 60px 0; background-color: #2c3e50; color: #fff;">
    <div class="l-container">
        <div class="p-cta" style="text-align: center;">
            <h2 class="c-heading --h2" style="margin-bottom: 20px;">Contact</h2>
            <p class="p-cta__text" style="margin-bottom: 30px;">
                Webサイトに関するご相談や、お見積りのご依頼など、<br>まずはお気軽にお問い合わせください。
            </p>
            <div class="c-btn p-cta__btn">
                <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="c-btn-link --main" style="background-color: #e67e22; color: #fff; padding: 15px 30px; text-decoration: none; border-radius: 4px;">お問い合わせはこちら</a>
            </div>
        </div>
    </div>
</section>

<?php
get_footer();
?>