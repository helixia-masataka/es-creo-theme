<?php

/**
 * Template Name:コンタクトページ
 **/
get_header();
?>

<div class="l-container">
    <div class="l-container-large">
        <!-- ①CF7用テンプレート｜ここからCF7の管理画面にコピペする -->
        <dl class="p-contact-form__lists">
            <!-- お名前 -->
            <div class="p-contact-form__list">
                <dt class="p-contact-form__term">
                    <label>お名前</label>
                    <span class="p-contact-form__label --required">必須</span>
                </dt>
                <dd class="p-contact-form__desc">
                    [text* your-name class:c-input placeholder "例）山田 太郎"]
                </dd>
            </div>
            <!-- メールアドレス -->
            <div class="p-contact-form__list">
                <dt class="p-contact-form__term">
                    <label>メールアドレス</label>
                    <span class="p-contact-form__label --required">必須</span>
                </dt>
                <dd class="p-contact-form__desc">
                    [email* your-email class:c-input placeholder "例）info@example.com"]
                </dd>
            </div>
            <!-- お問い合わせ種別（セレクトボックス） -->
            <div class="p-contact-form__list">
                <dt class="p-contact-form__term">
                    <label>お問い合わせ種別</label>
                    <span class="p-contact-form__label --optional">任意</span>
                </dt>
                <dd class="p-contact-form__desc">
                    <div class="c-select-wrapper">
                        [select inquiry-type class:c-select include_blank "お見積りについて" "採用について" "その他"]
                    </div>
                </dd>
            </div>
            <!-- ご連絡方法のご希望（ラジオボタン） -->
            <div class="p-contact-form__list">
                <dt class="p-contact-form__term">
                    <span class="p-contact-form__term-text">ご連絡方法のご希望</span>
                    <span class="p-contact-form__label --required">必須</span>
                </dt>
                <dd class="p-contact-form__desc">
                    [radio contact-method class:c-radio-group default:1 "メール" "お電話"]
                </dd>
            </div>
            <!-- 興味のあるサービス（チェックボックス） -->
            <div class="p-contact-form__list">
                <dt class="p-contact-form__term">
                    <span class="p-contact-form__term-text">興味のあるサービス</span>
                    <span class="p-contact-form__label --optional">任意</span>
                </dt>
                <dd class="p-contact-form__desc">
                    [checkbox service class:c-checkbox-group "Web制作" "システム開発" "マーケティング支援"]
                </dd>
            </div>
            <!-- お問い合わせ内容（テキストエリア） -->
            <div class="p-contact-form__list">
                <dt class="p-contact-form__term">
                    <label>お問い合わせ内容</label>
                    <span class="p-contact-form__label --required">必須</span>
                </dt>
                <dd class="p-contact-form__desc">
                    [textarea* your-message class:c-textarea placeholder "お問い合わせ内容をご記入ください。"]
                </dd>
            </div>
        </dl>

        <!-- 送信ボタンエリア -->
        <div class="p-contact-form__submit" style="text-align: center; margin-top: 30px;">
            [submit class:c-button class:c-button--submit "送信する"]
        </div>
        <!-- ②コピペここまで→コピペ後、上記を本テンプレートから削除 -->

        <!-- ③管理画面でID、タイトル確認して書き換え -->
        <?php echo do_shortcode('[contact-form-7 id="d79df5f" title="お問い合わせ"]'); ?>

    </div>
</div>


<?php
get_footer();
