<?php
/**
 * Template Name:コンタクトページ
 **/
get_header();
?>


<section class="p-contact">
    <div class="l-container">
        <div class="l-container-l">
            <div class="p-contact__inner">
                <h1 class="c-heading --h2">CONTACT</h1>
                <div class="p-contact__container">
                   
                    <div class="p-contact__form">
                        <?php echo do_shortcode('[contact-form-7 id="9ab1307" title="コンタクトフォーム 1"]'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<?php
get_footer();
?>

