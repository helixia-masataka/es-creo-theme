<?php
/**
 * Template Name:サンクスページ
 **/
get_header();
?>


<section class="p-static">
    <div class="l-container">
        <div class="l-container-l">
            <div class="p-static__inner">
                <div class="p-static__content">
                    <h1 class="p-static__title c-heading --h2">THANKS</h1>
                    <div class="p-static__message">
                        <p>送信が完了しました。</p>
                        <p>お問い合わせいただき､ありがとうございます。</p>
                    </div>
                    <div class="p-static__btn">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="c-btn-link">TOP</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<?php
get_footer();
