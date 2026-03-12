<?php

/**
 * Template Name:コンタクトページ
 **/
get_header();
?>

<div class="l-container">
    <div class="l-container-l">
        <!-- ①CF7用テンプレート｜ここからCF7の管理画面にコピペする -->
        <div class="p-contact-form__lists">
            <!-- 1. お名前（テキスト入力） -->
            <div class="p-contact-form__list">
                <label class="p-contact-form__term" for="your-name">
                    <span class="p-contact-form__term-text">お名前</span>
                    <span class="p-contact-form__label --required">必須</span>
                </label>
                <div class="p-contact-form__desc">
                    [text* your-name id:your-name class:c-contact-input placeholder "例）山田 太郎"]
                </div>
            </div>

            <!-- 2. メールアドレス -->
            <div class="p-contact-form__list">
                <label class="p-contact-form__term" for="your-email">
                    <span class="p-contact-form__term-text">メールアドレス</span>
                    <span class="p-contact-form__label --required">必須</span>
                </label>
                <div class="p-contact-form__desc">
                    [email* your-email id:your-email class:c-contact-input placeholder "例）info@example.com"]
                </div>
            </div>

            <!-- 3. お問い合わせ種別（セレクトボックス） -->
            <div class="p-contact-form__list">
                <label class="p-contact-form__term" for="inquiry-type">
                    <span class="p-contact-form__term-text">お問い合わせ種別</span>
                    <span class="p-contact-form__label --optional">任意</span>
                </label>
                <div class="p-contact-form__desc">
                    <div class="c-contact-select-wrapper">
                        [select inquiry-type id:inquiry-type class:c-contact-select include_blank "お見積りについて" "採用について" "その他"]
                    </div>
                </div>
            </div>

            <!-- 4. ご連絡方法のご希望（ラジオボタン） -->
            <fieldset class="p-contact-form__list">
                <legend class="p-contact-form__term">
                    <span class="p-contact-form__term-text">ご連絡方法のご希望</span>
                    <span class="p-contact-form__label --required">必須</span>
                </legend>
                <div class="p-contact-form__desc">
                    [radio contact-method class:c-contact-radio-group default:1 "メール" "お電話"]
                </div>
            </fieldset>

            <!-- 5. 興味のあるサービス（チェックボックス） -->
            <fieldset class="p-contact-form__list">
                <legend class="p-contact-form__term">
                    <span class="p-contact-form__term-text">興味のあるサービス</span>
                    <span class="p-contact-form__label --optional">任意</span>
                </legend>
                <div class="p-contact-form__desc">
                    [checkbox service class:c-contact-checkbox-group "Web制作" "システム開発" "マーケティング支援"]
                </div>
            </fieldset>

            <!-- 6. お問い合わせ内容（テキストエリア） -->
            <div class="p-contact-form__list">
                <label class="p-contact-form__term" for="your-message">
                    <span class="p-contact-form__term-text">お問い合わせ内容</span>
                    <span class="p-contact-form__label --required">必須</span>
                </label>
                <div class="p-contact-form__desc">
                    [textarea* your-message id:your-message class:c-contact-textarea placeholder "お問い合わせ内容をご記入ください。"]
                </div>
            </div>
        </div>

        <!-- 送信ボタンエリア -->
        <div class="p-contact-form__submit">
            [submit class:c-contact-button class:c-contact-button class:--submit "送信する"]
        </div>
        <!-- ②コピペここまで→コピペ後、上記を本テンプレートから削除 -->

        <!-- ③管理画面でID、タイトル確認して書き換え -->
        <?php echo do_shortcode('[contact-form-7 id="d79df5f" title="お問い合わせ"]'); ?>

    </div>
</div>


<?php
get_footer();
