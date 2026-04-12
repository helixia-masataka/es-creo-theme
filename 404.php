<?php
/**
 * 404 Error Page
 */
get_header();
?>


<section class="p-static">
    <div class="l-container">
        <div class="p-static__inner">
            <div class="p-static__content">
                <h1 class="p-static__title">NOT FOUND</h1>
                <div class="p-static__message">
                    <p>お探しのページは見つかりませんでした。</p>
                    <p>削除されたか､URLが変更された可能性があります。</p>
                </div>
                <div class="p-static__btn">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="c-btn-link">TOP</a>
                </div>
            </div>
        </div>
    </div>
</section>


<?php
get_footer();
?>

