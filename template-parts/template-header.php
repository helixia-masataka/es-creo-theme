<header class="l-header">
    <div class="l-container l-header__container">
        <div class="l-header__inner">

            <?php // トップページかどうかで h1 か div を判定 ?>
            <?php $logo_tag = (is_front_page() || is_home()) ? 'h1' : 'div'; ?>
            <<?php echo $logo_tag; ?> class="l-header__logo">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <?php if (has_custom_logo()): ?>
                        <?php
                        $custom_logo_id = get_theme_mod('custom_logo');
                        $logo_image = wp_get_attachment_image_src($custom_logo_id, 'full');
                        ?>
                        <img src="<?php echo esc_url($logo_image[0]); ?>" alt="<?php bloginfo('name'); ?>">
                    <?php else: ?>
                        <img src="<?php echo esc_url(get_theme_file_uri('/img/logo.svg')); ?>"
                            alt="<?php bloginfo('name'); ?>">
                    <?php endif; ?>
                </a>
            </<?php echo $logo_tag; ?>>

            <div class="l-header__info c-drawer">
                <?php // WPの管理画面でメニューを登録しているとき ?>
                <?php if (has_nav_menu('header-menu')): ?>
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'header-menu',
                        'container' => 'nav',
                        'container_class' => 'l-header__nav c-drawer__wrapper',
                        'container_aria_label' => 'グローバルナビゲーション',
                        'menu_class' => 'l-header__lists',
                        'menu_id' => 'global-nav',
                        'depth' => 2,
                    ));
                    ?>
                <?php else: ?>
                    <!-- ★追加: aria-label と id="global-nav" を付与 -->
                    <nav aria-label="グローバルナビゲーション" class="l-header__nav c-drawer__wrapper">
                        <ul id="global-nav" class="l-header__lists">
                            <?php
                            // トップページにいる時は「#top」､下層ページにいる時はトップページに遷移
                            $top_link = is_front_page() ? '#top' : esc_url(home_url('/'));
                            ?>
                            <li><a href="<?php echo $top_link; ?>">top</a></li>
                            <li><a href="<?php echo esc_url(home_url('/about/')); ?>">about</a></li>
                            <li><a href="<?php echo esc_url(home_url('/news/')); ?>">news</a></li>
                            <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">contact</a></li>
                            <li><a href="<?php echo esc_url('https://www.instagram.com/_____es.d'); ?>" target="_blank"
                                    rel="noopener noreferrer"><img
                                        src="<?php echo esc_url(get_theme_file_uri('/img/icon-instagram.svg')); ?>"
                                        alt="Instagram"></a>
                            </li>
                            <li><a href="<?php echo esc_url(home_url('/privacy/')); ?>">privacy</a></li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

            <button type="button" class="c-drawer__btn" aria-controls="global-nav" aria-expanded="false"
                aria-label="メニューを開閉する">
                <div class="c-drawer__icon-wrapper">
                    <span class="c-drawer__icon --close">
                        <img src="<?php echo esc_url(get_theme_file_uri('/img/icon-book-close.svg')); ?>" alt="">
                    </span>
                    <span class="c-drawer__icon --open">
                        <img src="<?php echo esc_url(get_theme_file_uri('/img/icon-book-open.svg')); ?>" alt="">
                    </span>
                </div>
                <div class="c-drawer__text">
                    <span class="c-drawer__text-menu">
                        <span>m</span><span>e</span><span>n</span><span>u</span>
                    </span>
                    <span class="c-drawer__text-close">
                        <span>c</span><span>l</span><span>o</span><span>s</span><span>e</span>
                    </span>
                </div>
            </button>

        </div>
    </div>
</header>
