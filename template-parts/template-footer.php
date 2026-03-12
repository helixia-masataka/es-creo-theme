<footer class="l-footer">
    <div class="l-container">
        <div class="l-container-m l-footer__inner">
            <div class="l-footer__info">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="l-footer__logo" rel="home">
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

                <address class="l-footer__address">
                    <dl class="l-footer__access">
                        <div class="l-footer__wrap">
                            <dt>住所</dt>
                            <dd>〒060-0061 北海道札幌市中央区南1条西2丁目1-2 木NINARU BLDG.</dd>
                        </div>
                        <div class="l-footer__wrap">
                            <dt>TEL</dt>
                            <dd><a href="tel:0110000000">011-000-0000</a></dd>
                        </div>
                    </dl>
                </address>
                
            </div>
           
        </div>
        <div class="l-footer__bottom">
             <nav aria-label="フッターナビゲーション" class="l-footer__nav">
                <?php if (has_nav_menu('footer-menu')): ?>
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'footer-menu',
                        'container' => false,
                        'menu_class' => 'l-footer__lists',
                        'depth' => 1,
                    ));
                    ?>
                <?php else: ?>
                    <ul class="l-footer__lists">
                        <li><a href="<?php echo esc_url(home_url('/about/')); ?>">会社概要</a></li>
                        <li><a href="<?php echo esc_url(home_url('/service/')); ?>">事業内容</a></li>
                        <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">お問い合わせ</a></li>
                        <li><a href="<?php echo esc_url(home_url('/privacy/')); ?>">プライバシーポリシー</a></li>
                    </ul>
                <?php endif; ?>
            </nav>
            <p class="l-footer__copyright"><small>&copy; <?php echo wp_date('Y'); ?> Helixia.inc</small></p>
        </div>
    </div>

    <!-- ページトップに戻るボタン -->
    <button class="c-to-top" id="js-to-top" aria-label="ページの先頭に戻る" type="button">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="18 15 12 9 6 15" />
        </svg>
    </button>
</footer>