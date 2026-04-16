<?php
/**
 * Aboutページ専用：プロフィール（リピーター）カスタムフィールド
 */

// メタボックスの追加
function helixia_about_add_meta_box()
{
    global $post;

    // page-about.php を適用しているページのみ表示
    if (!empty($post)) {
        $template = get_post_meta($post->ID, '_wp_page_template', true);
        if ($template !== 'page-about.php') {
            return;
        }
    }

    add_meta_box(
        'helixia_about_profile_meta_box',
        'PROFILE セクション設定（リピーター）',
        'helixia_about_profile_meta_box_callback',
        'page',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'helixia_about_add_meta_box');

// メタボックスの表示
function helixia_about_profile_meta_box_callback($post)
{
    wp_nonce_field('helixia_about_save_meta_box_data', 'helixia_about_meta_box_nonce');

    // 保存されている値を取得
    $profiles = get_post_meta($post->ID, '_about_profiles', true);

    if (!is_array($profiles) || empty($profiles)) {
        // 初期状態：空のアイテムを一つ用意
        $profiles = array(
            array(
                'image_id' => '',
                'name' => '',
                'role' => '',
                'text' => ''
            )
        );
    }
    ?>
    <style>
        .helixia-repeater-container {
            margin-bottom: 20px;
        }

        .helixia-repeater-item {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 15px;
            position: relative;
            background: #fff;
        }

        .helixia-repeater-item .remove-row {
            position: absolute;
            top: 10px;
            right: 10px;
            color: #d63638;
            cursor: pointer;
            text-decoration: underline;
            font-size: 13px;
        }

        .helixia-repeater-field {
            margin-bottom: 10px;
        }

        .helixia-repeater-field label {
            display: block;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .helixia-repeater-field input[type="text"],
        .helixia-repeater-field textarea {
            width: 100%;
        }

        .helixia-repeater-field textarea {
            height: 80px;
        }

        .helixia-image-preview {
            max-width: 150px;
            display: block;
            margin-bottom: 10px;
        }

        .ui-sortable-helper {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>

    <div id="helixia-about-profiles-container" class="helixia-repeater-container">
        <p>※ 画像を選択し､役職､名前､紹介文を入力してください。ドラッグ＆ドロップで並び替え可能です。</p>
        <?php foreach ($profiles as $index => $profile):
            $img_url = '';
            if (!empty($profile['image_id'])) {
                $img_src = wp_get_attachment_image_src($profile['image_id'], 'thumbnail');
                if ($img_src) {
                    $img_url = $img_src[0];
                }
            }
            ?>
            <div class="helixia-repeater-item">
                <span class="remove-row js-remove-profile">削除</span>
                <div class="helixia-repeater-field">
                    <label>プロフィール画像</label>
                    <img src="<?php echo esc_url($img_url); ?>" class="helixia-image-preview"
                        style="<?php echo empty($img_url) ? 'display:none;' : ''; ?>">
                    <input type="hidden" name="about_profiles[<?php echo $index; ?>][image_id]" class="helixia-image-id"
                        value="<?php echo esc_attr($profile['image_id']); ?>">
                    <button type="button" class="button js-select-image">画像を選択</button>
                    <button type="button" class="button js-remove-image">画像を削除</button>
                </div>
                <div class="helixia-repeater-field">
                    <label>名前</label>
                    <input type="text" name="about_profiles[<?php echo $index; ?>][name]"
                        value="<?php echo esc_attr($profile['name']); ?>">
                </div>
                <div class="helixia-repeater-field">
                    <label>属性・役職 (例: Web Designer)</label>
                    <input type="text" name="about_profiles[<?php echo $index; ?>][role]"
                        value="<?php echo esc_attr($profile['role']); ?>">
                </div>
                <div class="helixia-repeater-field">
                    <label>テキスト</label>
                    <textarea
                        name="about_profiles[<?php echo $index; ?>][text]"><?php echo esc_textarea($profile['text']); ?></textarea>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <button type="button" class="button button-primary button-large js-add-profile">プロフィールを追加</button>

    <script>
        jQuery(document).ready(function ($) {
            var $container = $('#helixia-about-profiles-container');
            var itemIndex = <?php echo count($profiles); ?>;

            // 画像選択
            $(document).on('click', '.js-select-image', function (e) {
                e.preventDefault();
                var $button = $(this);
                var $item = $button.closest('.helixia-repeater-item');

                var frame = wp.media({
                    title: '画像を選択',
                    multiple: false
                });

                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first().toJSON();
                    $item.find('.helixia-image-id').val(attachment.id);
                    var thumb = attachment.sizes && attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                    $item.find('.helixia-image-preview').attr('src', thumb).show();
                });
                frame.open();
            });

            // 画像削除
            $(document).on('click', '.js-remove-image', function (e) {
                e.preventDefault();
                var $item = $(this).closest('.helixia-repeater-item');
                $item.find('.helixia-image-id').val('');
                $item.find('.helixia-image-preview').attr('src', '').hide();
            });

            // 項目追加
            $('.js-add-profile').on('click', function (e) {
                e.preventDefault();
                var html = '<div class="helixia-repeater-item">' +
                    '<span class="remove-row js-remove-profile">削除</span>' +
                    '<div class="helixia-repeater-field">' +
                    '<label>プロフィール画像</label>' +
                    '<img src="" class="helixia-image-preview" style="display:none;">' +
                    '<input type="hidden" name="about_profiles[' + itemIndex + '][image_id]" class="helixia-image-id" value="">' +
                    '<button type="button" class="button js-select-image">画像を選択</button> ' +
                    '<button type="button" class="button js-remove-image">画像を削除</button>' +
                    '</div>' +
                    '<div class="helixia-repeater-field">' +
                    '<label>属性・役職</label>' +
                    '<input type="text" name="about_profiles[' + itemIndex + '][role]" value="">' +
                    '</div>' +
                    '<div class="helixia-repeater-field">' +
                    '<label>名前</label>' +
                    '<input type="text" name="about_profiles[' + itemIndex + '][name]" value="">' +
                    '</div>' +
                    '<div class="helixia-repeater-field">' +
                    '<label>テキスト</label>' +
                    '<textarea name="about_profiles[' + itemIndex + '][text]"></textarea>' +
                    '</div>' +
                    '</div>';

                $container.append(html);
                itemIndex++;
            });

            // 項目削除
            $(document).on('click', '.js-remove-profile', function () {
                if (confirm('このプロフィールを削除しますか？')) {
                    $(this).closest('.helixia-repeater-item').remove();
                }
            });

            // 並び替え可能にする
            if ($.isFunction($.fn.sortable)) {
                $container.sortable({
                    handle: '.helixia-repeater-item',
                    cursor: 'move',
                    opacity: 0.7
                });
            }
        });
    </script>
    <?php
}

// 保存処理
function helixia_about_save_meta_box_data($post_id)
{
    if (!isset($_POST['helixia_about_meta_box_nonce']) || !wp_verify_nonce($_POST['helixia_about_meta_box_nonce'], 'helixia_about_save_meta_box_data')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_page', $post_id)) {
        return;
    }

    if (isset($_POST['about_profiles']) && is_array($_POST['about_profiles'])) {
        $profiles = array();
        // Indexを振り直して保存
        $i = 0;
        foreach ($_POST['about_profiles'] as $profile) {
            // いずれかの項目に入力があれば保存対象とする
            if (empty($profile['name']) && empty($profile['image_id']) && empty($profile['role']) && empty($profile['text']))
                continue;

            $profiles[$i] = array(
                'image_id' => sanitize_text_field($profile['image_id']),
                'role' => sanitize_text_field($profile['role']),
                'name' => sanitize_text_field($profile['name']),
                'text' => sanitize_textarea_field($profile['text']),
            );
            $i++;
        }
        update_post_meta($post_id, '_about_profiles', $profiles);
    } else {
        delete_post_meta($post_id, '_about_profiles');
    }
}
add_action('save_post', 'helixia_about_save_meta_box_data');

// メディアアップローダー用スクリプト読み込み
function helixia_about_admin_scripts($hook)
{
    global $post;
    if ($hook == 'post.php' || $hook == 'post-new.php') {
        if ($post && get_post_meta($post->ID, '_wp_page_template', true) === 'page-about.php') {
            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-sortable');
        }
    }
}
add_action('admin_enqueue_scripts', 'helixia_about_admin_scripts');
