<?php
/**
 *Template Name: アバウト
 **/
get_header();
?>


<div class="p-about">
    <!-- DESIGN Section -->
    <section class="p-about__section p-about-design">
        <div class="l-container">
            <div class="l-container-l">
                <div class="p-about-design__inner">
                    <div class="p-about-design__content">
                        <div class="p-about-design__img">
                            <img src="<?php echo get_theme_file_uri('/img/about-main.webp'); ?>" alt="About Main"
                                loading="eager">
                        </div>
                        <div class="p-about-design__texts">
                            <h1 class="p-about-design__heading">はじめまして､<span class="p-about__br">______es.（イズ）です。</span>
                            </h1>
                            <ul class="p-about-design__lists">
                                <li class="p-about-design__list">
                                    <p>私たちは､「伝わり方」を設計するデザイン事務所です。
                                        <span class="p-about__br">Webサイトやグラフィックをはじめ､さまざまな接点を横断しながら､<br
                                                class="pc-only">一貫した印象づくりをサポートしています。</span>
                                    </p>
                                </li>
                                <li class="p-about-design__list">
                                    <p><span class="p-about__br">ブランドの価値や世界観を整え､<br
                                                class="pc-only">想いや､らしさ､強みを､ひとつずつ拾いながら形にしていく。</span></p>
                                    <p>その積み重ねが､選ばれる理由につながっていきます。</p>
                                </li>
                                <li class="p-about-design__list">
                                    <p>まだ言葉にできていない想いや､曖昧なイメージのままでも大丈夫です。</p>
                                </li>
                                <li class="p-about-design__list">
                                    <p><span class="p-about__br">お話をしながら方向性を整理し､見た目の印象だけでなく､<br
                                                class="pc-only">手に取ってくださった方の反応や変化まで意識しながら､<br
                                                class="pc-only">ひとつひとつ丁寧にデザインしています。</span>
                                    </p>
                                </li>
                                <li class="p-about-design__list">
                                    <p>新しく何かを始めるときも､今あるものを見直したいときも。</p>
                                </li>
                                <li class="p-about-design__list">
                                    <p>「ちょっと相談してみたい」</p>
                                    <p>そんな段階でも､お気軽にお声かけください。</p>
                                </li>
                                <li class="p-about-design__list">
                                    <p>一時的な制作ではなく､ これからの成長につながるデザインを。
                                        <br>
                                        _____es.は､あなたと長く寄り添えるパートナーでありたいと考えています。
                                    </p>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PROFILE Section -->
    <section class="p-about__section p-about-profile">
        <div class="l-container">
            <div class="l-container-l">
                <div class="p-about-profile__inner">
                    <div class="p-about-profile__heading">
                        <h2 class="p-about-profile__title c-heading --h2">profile</h2>
                    </div>
                    <div class="p-about__content p-about-profile__content">
                        <?php
                        $profiles_raw = get_post_meta(get_the_ID(), '_about_profiles', true);
                        $profiles = array();
                        if (!empty($profiles_raw) && is_array($profiles_raw)) {
                            foreach ($profiles_raw as $profile) {
                                // どれか1つでも入力があれば表示対象とする
                                if (empty($profile['name']) && empty($profile['image_id']) && empty($profile['role']) && empty($profile['text'])) {
                                    continue;
                                }
                                $profiles[] = $profile;
                            }
                        }

                        if (!empty($profiles)):
                            $profile_count = count($profiles);
                            $is_slider = $profile_count >= 3;
                            ?>
                            <div class="p-about-profile__list <?php if ($is_slider)
                                echo 'swiper js-about-profile-swiper'; ?>">
                                <div
                                    class="<?php echo $is_slider ? 'swiper-wrapper' : 'p-about-profile__grid' . ($profile_count === 1 ? ' --single' : ''); ?>">
                                    <?php foreach ($profiles as $profile):
                                        $img_src = '';
                                        if (!empty($profile['image_id'])) {
                                            $img_data = wp_get_attachment_image_src($profile['image_id'], 'large');
                                            $img_src = $img_data ? $img_data[0] : '';
                                        }
                                        ?>
                                        <article class="p-about-profile__item <?php if ($is_slider)
                                            echo 'swiper-slide'; ?>">
                                            <div class="p-about-profile__thumb">
                                                <?php if ($img_src): ?>
                                                    <img src="<?php echo esc_url($img_src); ?>"
                                                        alt="<?php echo esc_attr($profile['name']); ?>" loading="lazy">
                                                <?php else: ?>
                                                    <img src="<?php echo get_theme_file_uri('/img/no-image.webp'); ?>"
                                                        alt="No Image" loading="lazy">
                                                <?php endif; ?>
                                            </div>
                                            <div class="p-about-profile__info">
                                                <h3 class="p-about-profile__name"><?php echo esc_html($profile['name']); ?></h3>
                                                <span
                                                    class="p-about-profile__role"><?php echo esc_html($profile['role']); ?></span>
                                                <p class="p-about-profile__desc">
                                                    <?php echo nl2br(esc_html($profile['text'])); ?>
                                                </p>
                                            </div>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                                <?php if ($is_slider): ?>
                                    <div class="p-about-profile__controls">
                                        <div class="swiper-button-prev"></div>
                                        <div class="swiper-pagination"></div>
                                        <div class="swiper-button-next"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- SERVICE Section -->
    <section class="p-about__section p-about-service">
        <div class="l-container">
            <div class="l-container-l">
                <div class="p-about-service__inner">
                    <div class="p-about-service__content">
                        <ul class="p-about-service__list">
                            <li class="p-about-service__item --title">
                                <h2 class="p-about-service__heading c-heading --h2">service</h2>
                            </li>
                            <?php
                            $services = array(
                                'ロゴ' => 'ブランドの印象を決めるロゴを制作します。想いやコンセプトを整理し､長く使える形に落とし込みます。',
                                '名刺' => '第一印象を左右する名刺をデザインします。手に取ったときに印象に残る､伝わるデザインに仕上げます。',
                                'パッケージ' => '商品の魅力が伝わるパッケージを設計します。売り場やターゲットに合わせて､手に取りたくなる見せ方を提案します。',
                                '撮影・編集' => '世界観に合わせた撮影や編集を行います。デザインと一貫したビジュアルづくりをサポートします。',
                                '広告' => '伝えたい内容を整理し､目的やターゲットに刺さりやすい構成でデザインを制作します。',
                                'グラフィック' => 'ポスターやチラシなど､幅広い制作に対応。ブランドの印象を保ちながら､わかりやすく伝えるデザインを提案します。',
                                '動画編集' => 'SNSやプロモーション用の動画編集を行います。見る人に負担をかけず､テンポ良く情報をお届けします。',
                                'Webサイト' => '目的やターゲットに合わせたWebサイトを制作します。見た目だけでなく､伝わり方や使いやすさまで設計します。',
                                'UI設計' => 'ユーザーが迷わず使える設計を行います。使いやすさと見た目のバランスを大切にしています。',
                                'ブランディング' => 'ブランド全体の方向性や世界観を整理します。一貫した伝わり方になるよう､土台から設計します。',
                            );
                            foreach ($services as $title => $desc): ?>
                                <li class="p-about-service__item">
                                    <h3 class="p-about-service__name"><?php echo $title; ?></h3>
                                    <p class="p-about-service__desc"><?php echo $desc; ?></p>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FLOW Section -->
    <section class="p-about__section p-about-flow">
        <div class="l-container">
            <div class="l-container-l">
                <div class="p-about-flow__inner">
                    <h2 class="p-about-flow__title c-heading --h2">flow</h2>
                    <div class="p-about-flow__content">
                        <div class="p-about-flow__head">
                            <p>依頼内容に応じて､作業工程や費用などが異なりますので､詳しくはお問い合わせください。</p>
                        </div>
                        <div class="l-container-m">
                            <div class="p-about-flow__wrapper">
                                <ol class="p-about-flow__steps">
                                    <?php
                                    $flows = ['概要確認', 'お見積り', 'ご契約', 'ヒアリング', 'リサーチ', 'コンセプト設計', 'デザインのご提案', 'ブラッシュアップ', '納品', 'アフターサポート'];
                                    $texts = [
                                        '「どんなことをしたいのか」「どんなお悩みがあるのか」をお伺いします。',
                                        'お伺いした内容をもとに､制作内容と費用の目安をご案内します。',
                                        '内容と金額にご納得いただけましたら､ご契約へと進みます。',
                                        '制作に向けて､事業内容やターゲット､目指したい方向性などを整理していきます。',
                                        'ヒアリング内容をもとに､現状の整理や市場のリサーチを行います。',
                                        'どのように伝えるか､どんな見せ方が最適かを考え､方向性を固めます。',
                                        'コンセプト設計を元に作成したデザインをご提案します',
                                        'ご意見をもとに､より良い形になるよう調整していきます。',
                                        '最終的なデータをお渡しします。',
                                        '公開後の反応や運用についても',
                                    ];
                                    foreach ($flows as $index => $step): ?>
                                        <li class="p-about-flow__step">
                                            <div class="p-about-flow__row">
                                                <span
                                                    class="p-about-flow__num"><?php echo sprintf('%02d', $index + 1); ?></span>
                                                <h3 class="p-about-flow__name"><?php echo $step; ?></h3>
                                            </div>
                                            <p class="p-about-flow__desc"><?php echo $texts[$index]; ?></p>
                                        </li>
                                    <?php endforeach; ?>
                                </ol>
                                <div class="p-about-flow__img">
                                    <img src="<?php echo get_theme_file_uri('/img/about-flow.webp'); ?>" alt="Flow"
                                        loading="lazy">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- NEWS & AWARD Section -->
    <section id="news" class="p-about__section p-about-news-award">
        <div class="l-container">
            <div class="l-container-l">
                <div class="p-about-news-award__inner">
                    <div class="p-about-news-award__col">
                        <h2 class="p-about-news-award__title c-heading --h2">news</h2>
                        <div class="p-about-news-award__content">
                            <?php
                            $news_query = new WP_Query(array(
                                'post_type' => 'archive-news',
                                'posts_per_page' => 2,
                            ));
                            if ($news_query->have_posts()):
                                ?>
                                <ul class="p-about-news__lists">
                                    <?php while ($news_query->have_posts()):
                                        $news_query->the_post(); ?>
                                        <li class="p-about-news__list">
                                            <a href="<?php echo esc_url(home_url('/news/#news-' . get_the_ID())); ?>">
                                                <time
                                                    datetime="<?php echo get_the_date('Y-m-d'); ?>"><?php echo get_the_date('Y.m.d'); ?></time>
                                                <h3 class="p-about-news__title"><?php the_title(); ?></h3>
                                            </a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                                <div class="p-about-news__more c-btn">
                                    <a href="<?php echo esc_url(home_url('/news/')); ?>" class="c-btn-link">view more</a>
                                </div>
                            <?php else: ?>
                                <p>まだお知らせはありません。</p>
                            <?php endif;
                            wp_reset_postdata(); ?>
                        </div>
                    </div>
                    <div class="p-about__section p-about-news-award__col">
                        <h2 class="p-about-news-award__title c-heading --h2">award</h2>
                        <div class="p-about-news-award__content">
                            <?php
                            $award_query = new WP_Query(array(
                                'post_type' => 'award',
                                'posts_per_page' => 2,
                            ));
                            if ($award_query->have_posts()):
                                ?>
                                <ul class="p-about-award__lists">
                                    <?php while ($award_query->have_posts()):
                                        $award_query->the_post(); ?>
                                        <li class="p-about-award__list">
                                            <time class="p-about-award__year"><?php echo get_the_date('Y'); ?>年</time>
                                            <h3 class="p-about-award__title"><?php the_title(); ?></h3>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                <p>受賞歴はまだありません。</p>
                            <?php endif;
                            wp_reset_postdata(); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTACT Section -->
    <section class="p-about-contact">
        <div class="l-container">
            <div class="l-container-l">
                <div class="p-about-contact__btn">
                    <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="p-about-contact__link">
                        <img class="p-about-contact__icon --black" src="<?php echo get_theme_file_uri('/img/icon-right-hand-black.webp'); ?>" alt="">
                        <img class="p-about-contact__icon --white" src="<?php echo get_theme_file_uri('/img/icon-right-hand-white.webp'); ?>" alt="">
                        <span>contact</span>
                        <img class="p-about-contact__icon --black" src="<?php echo get_theme_file_uri('/img/icon-left-hand-black.webp'); ?>" alt="">
                        <img class="p-about-contact__icon --white" src="<?php echo get_theme_file_uri('/img/icon-left-hand-white.webp'); ?>" alt="">
                    </a>
                </div>
            </div>
        </div>
    </section>

</div>

<?php
get_footer();
