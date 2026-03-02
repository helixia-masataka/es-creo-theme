<footer class="l-footer">
    <div class="l-container">
        <div class="l-container-large l-footer__inner">
            <div class="l-footer__info">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="l-footer__logo">
                    <?php
                    if ( has_custom_logo() ) {
                        $custom_logo_id = get_theme_mod( 'custom_logo' );
                        $logo_image = wp_get_attachment_image_src( $custom_logo_id, 'full' );
                        echo '<img src="' . esc_url( $logo_image[0] ) . '" alt="' . get_bloginfo( 'name' ) . '" loading="lazy" decoding="async">';
                    } else {
                        echo 'Helixia.inc';
                    }
                    ?>
                </a>
                <address class="l-footer__address">
                    〒000-0000<br>
                    北海道札幌市〇〇区〇〇 1-2-3<br>
                    TEL: 011-000-0000
                </address>
            </div>
            <div class="l-footer__nav">
                <?php if ( has_nav_menu( 'footer-menu' ) ) : ?>
                    <?php
                    wp_nav_menu( array(
                        'theme_location' => 'footer-menu',
                        'container'      => false,
                        'menu_class'     => 'l-footer__nav-list',
                        'depth'          => 1,
                    ) );
                    ?>
                <?php else : ?>
                    <ul class="l-footer__nav-list">
                        <li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>">会社概要</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/service/' ) ); ?>">事業内容</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">お問い合わせ</a></li>
                        <li><a href="<?php echo esc_url( home_url( '/privacy-policy/' ) ); ?>">プライバシーポリシー</a></li>
                    </ul>
                <?php endif; ?>
            </div>
        </div><div class="l-footer__copyright">
            <p>&copy; <?php echo wp_date('Y'); ?> Helixia.inc</p>
        </div>
    </div>
</footer>