<?php
/**
 * カスタム投稿「works」の詳細ページ
 */
get_header();
?>

<?php if (have_posts()):
    while (have_posts()):
        the_post(); ?>
        <?php
        $post_id = get_the_ID();
        $works_content = get_post_meta($post_id, '_works_content', true);
        $works_info = get_post_meta($post_id, '_works_info', true);
        $works_url = get_post_meta($post_id, '_works_url', true);
        $works_images = get_post_meta($post_id, '_works_images', true);

        // 改行で分割して配列にする（制作者情報）
        $info_lines = array();
        if (!empty($works_info)) {
            $info_lines = explode("\n", str_replace(array("\r\n", "\r"), "\n", $works_info));
        }
        ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('p-works-detail'); ?>>
            <div class="l-container">
                <div class="p-works-detail__inner">

                    <?php /* 左側：画像エリア */ ?>
                    <div class="p-works-detail__left">
                        <?php if (!empty($works_images) && is_array($works_images)): ?>
                            <div class="p-works-detail__images">
                                <?php foreach ($works_images as $image_id): ?>
                                    <figure class="p-works-detail__image-item">
                                        <?php echo wp_get_attachment_image($image_id, 'full'); ?>
                                    </figure>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <?php /* 画像がない場合はアイキャッチを表示 */ ?>
                            <div class="p-works-detail__images">
                                <figure class="p-works-detail__image-item">
                                    <?php if (has_post_thumbnail()): ?>
                                        <?php the_post_thumbnail('full'); ?>
                                    <?php else: ?>
                                        <img src="<?php echo esc_url(get_theme_file_uri('/img/no-image.webp')); ?>" alt="No Image">
                                    <?php endif; ?>
                                </figure>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php /* 右側：テキストエリア */ ?>
                    <div class="p-works-detail__right">
                        <div class="p-works-detail__content-wrapper">

                            <h1 class="p-works-detail__title">
                                <?php
                                $title = get_the_title();
                                echo wp_kses_post(str_replace('さま', 'さま<br>', $title));
                                ?>
                            </h1>

                            <?php if (!empty($works_content)): ?>
                                <div class="p-works-detail__text">
                                    <?php echo wp_kses_post(wpautop($works_content)); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($info_lines)): ?>
                                <dl class="p-works-detail__info">
                                    <?php foreach ($info_lines as $line):
                                        $parts = explode(':', $line, 2);
                                        if (count($parts) === 2):
                                            ?>
                                            <div class="p-works-detail__info-item">
                                                <dt><?php echo esc_html(trim($parts[0])); ?>:</dt>
                                                <dd><?php echo esc_html(trim($parts[1])); ?></dd>
                                            </div>
                                        <?php else: ?>
                                            <div class="p-works-detail__info-item">
                                                <dd><?php echo esc_html(trim($line)); ?></dd>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </dl>
                            <?php endif; ?>

                            <?php if (!empty($works_url)): ?>
                                <div class="p-works-detail__link">
                                    <a href="<?php echo esc_url($works_url); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php echo esc_html($works_url); ?>
                                        <span class="p-works-detail__link-icon"></span>
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="p-works-detail__back">
                                <a href="<?php echo esc_url(home_url('/')); ?>" class="c-btn-link">BACK</a>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </article>

    <?php endwhile; endif; ?>

<?php get_footer(); ?>

