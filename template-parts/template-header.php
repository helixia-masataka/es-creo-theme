<header class="l-header">
    <div class="l-container">
        <div class="l-header__inner">
            
            <?php // トップページかどうかで h1 か div を判定 ?>
            <?php $logo_tag = (is_front_page() || is_home()) ? 'h1' : 'div'; ?>
            <<?php echo $logo_tag; ?> class="l-header__logo">
                <a href="<?php echo esc_url(home_url('/')); ?>">
                    <?php if (has_custom_logo()) : ?>
                        <?php
                        $custom_logo_id = get_theme_mod('custom_logo');
                        $logo_image = wp_get_attachment_image_src($custom_logo_id, 'full');
                        ?>
                        <img src="<?php echo esc_url($logo_image[0]); ?>" alt="<?php bloginfo('name'); ?>">
                    <?php else : ?>
                        <img src="<?php echo esc_url(get_theme_file_uri('/img/logo.svg')); ?>" alt="<?php bloginfo('name'); ?>">
                    <?php endif; ?>
                </a>
            </<?php echo $logo_tag; ?>>

            <div class="l-header__info c-drawer">
                <?php if ( has_nav_menu( 'header-menu' ) ) : ?>
                    <?php
                    wp_nav_menu( array(
                        'theme_location'  => 'header-menu',
                        'container'       => 'nav',
                        'container_class' => 'l-header__nav c-drawer__wrapper',
                        'menu_class'      => 'l-header__lists',
                        'depth'           => 2,
                    ) );
                    ?>
                <?php else : ?>
                    <nav class="l-header__nav c-drawer__wrapper">
                        <ul class="l-header__lists">
                            <li><a href="#top">Top</a></li>
                            <li><a href="<?php echo esc_url(home_url('/セクション1')); ?>">ナビ1</a></li>
                            <li><a href="<?php echo esc_url(home_url('/セクション2')); ?>">ナビ2</a></li>
                            <li><a href="<?php echo esc_url(home_url('/セクション3')); ?>">ナビ3</a></li>
                            <li><a href="<?php echo esc_url(home_url('/セクション4')); ?>">ナビ4</a></li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>

            <button class="c-drawer__btn">
                <span class="c-drawer__bars">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            </button>

        </div>
    </div>
</header>