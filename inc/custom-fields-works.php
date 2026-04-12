<?php
/**
 * カスタム投稿「works」専用のカスタムフィールド登録
 */

// メタボックスの追加
function helixia_works_add_meta_box()
{
    add_meta_box(
        'helixia_works_meta_box',
        '作品詳細情報',
        'helixia_works_meta_box_callback',
        'works', // カスタム投稿タイプ名
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'helixia_works_add_meta_box');

// メタボックスの表示
function helixia_works_meta_box_callback($post)
{
    wp_nonce_field('helixia_works_save_meta_box_data', 'helixia_works_meta_box_nonce');

    // 保存されている値を取得
    $content = get_post_meta($post->ID, '_works_content', true);
    $info = get_post_meta($post->ID, '_works_info', true);
    $url = get_post_meta($post->ID, '_works_url', true);
    $images = get_post_meta($post->ID, '_works_images', true); // 配列で保存

    // 初期値の設定（新規投稿時または空の場合）
    if (empty($info)) {
        $info = "CLIENT: \nDIRECTION: \nDESIGN: \nKOUMOKU: 担当者名";
    }

    if (!is_array($images)) {
        $images = array();
    }
    ?>
    <style>
        .helixia-meta-row {
            margin-bottom: 20px;
        }

        .helixia-meta-row label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .helixia-meta-row textarea {
            width: 100%;
            height: 100px;
        }

        .helixia-meta-row input[type="text"],
        .helixia-meta-row input[type="url"] {
            width: 100%;
        }

        /* リピーター部分 */
        .helixia-images-repeater {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .helixia-image-item {
            width: 150px;
            position: relative;
            border: 1px solid #ddd;
            padding: 5px;
            background: #fff;
            text-align: center;
        }

        .helixia-image-item img {
            max-width: 100%;
            height: auto;
            display: block;
            margin-bottom: 5px;
            cursor: pointer;
        }

        .helixia-image-item .remove-image {
            color: #d63638;
            text-decoration: none;
            font-size: 12px;
            cursor: pointer;
        }

        .helixia-add-image-container {
            margin-top: 10px;
        }
    </style>

    <div class="helixia-meta-row">
        <label for="works_content">本文</label>
        <textarea name="works_content" id="works_content"><?php echo esc_textarea($content); ?></textarea>
    </div>

    <div class="helixia-meta-row">
        <label for="works_info">制作者情報</label>
        <textarea name="works_info" id="works_info"
            placeholder="CLIENT: ○○&#10;DIRECTION: ○○&#10;DESIGN: ○○"><?php echo esc_textarea($info); ?></textarea>
        <p class="description">改行して入力してください。</p>
    </div>

    <div class="helixia-meta-row">
        <label for="works_url">リンク (URL)</label>
        <input type="url" name="works_url" id="works_url" value="<?php echo esc_url($url); ?>"
            placeholder="https://example.com">
    </div>

    <div class="helixia-meta-row">
        <label>作品画像 (複数登録可)</label>
        <p class="description">一番上の画像がアーカイブページ等でサムネイルとして使用されます。</p>
        <div id="helixia-images-container" class="helixia-images-repeater">
            <?php foreach ($images as $image_id):
                $img_src = wp_get_attachment_image_src($image_id, 'thumbnail');
                if ($img_src):
                    ?>
                    <div class="helixia-image-item">
                        <img src="<?php echo esc_url($img_src[0]); ?>" class="js-upload-image">
                        <input type="hidden" name="works_images[]" value="<?php echo esc_attr($image_id); ?>">
                        <span class="remove-image js-remove-image">削除</span>
                    </div>
                    <?php
                endif;
            endforeach; ?>
        </div>
        <div class="helixia-add-image-container">
            <button type="button" class="button button-large js-add-image" id="helixia-add-image-btn">画像を追加</button>
        </div>
    </div>

    <script>
        jQuery(document).ready(function ($) {
            var frame;
            var $container = $('#helixia-images-container');

            // 画像追加ボタン
            $(document).on('click', '.js-add-image', function (e) {
                e.preventDefault();

                // メディアライブラリを開く
                if (frame) {
                    frame.open();
                    return;
                }

                frame = wp.media({
                    title: '画像を選択',
                    button: { text: '画像を登録' },
                    multiple: true // 複数選択可
                });

                frame.on('select', function () {
                    var selections = frame.state().get('selection');
                    selections.map(function (attachment) {
                        attachment = attachment.toJSON();
                        var thumbUrl = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;

                        var html = '<div class="helixia-image-item">' +
                            '<img src="' + thumbUrl + '" class="js-upload-image">' +
                            '<input type="hidden" name="works_images[]" value="' + attachment.id + '">' +
                            '<span class="remove-image js-remove-image">削除</span>' +
                            '</div>';

                        $container.append(html);
                    });
                });

                frame.open();
            });

            // 画像削除
            $(document).on('click', '.js-remove-image', function () {
                $(this).closest('.helixia-image-item').remove();
            });

            // 並び替え（おまけ：ドラッグ&ドロップで並び替えできるようにする場合は jQuery UI Sortable が必要）
            if ($.isFunction($.fn.sortable)) {
                $container.sortable({
                    placeholder: 'ui-state-highlight'
                });
            }
        });
    </script>
    <?php
}

// データの保存
function helixia_works_save_meta_box_data($post_id)
{
    // nonceの確認
    if (!isset($_POST['helixia_works_meta_box_nonce']) || !wp_verify_nonce($_POST['helixia_works_meta_box_nonce'], 'helixia_works_save_meta_box_data')) {
        return;
    }

    // 自動保存時は何もしない
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // ユーザー権限確認
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // 文の保存
    if (isset($_POST['works_content'])) {
        update_post_meta($post_id, '_works_content', wp_kses_post($_POST['works_content']));
    }

    // 情報の保存
    if (isset($_POST['works_info'])) {
        update_post_meta($post_id, '_works_info', sanitize_textarea_field($_POST['works_info']));
    }

    // URLの保存
    if (isset($_POST['works_url'])) {
        update_post_meta($post_id, '_works_url', esc_url_raw($_POST['works_url']));
    }

    // 画像の保存
    if (isset($_POST['works_images']) && is_array($_POST['works_images'])) {
        $images = array_map('absint', $_POST['works_images']);
        update_post_meta($post_id, '_works_images', $images);

        // 特徴的な要件: 最初の画像をアイキャッチとしても設定するか?
        // 今回はテンプレート側で制御するが､必要ならここで set_post_thumbnail 可能
    } else {
        delete_post_meta($post_id, '_works_images');
    }
}
add_action('save_post', 'helixia_works_save_meta_box_data');

// 管理画面へのスクリプト読み込み
function helixia_works_admin_assets($hook)
{
    global $post_type;
    if ($post_type !== 'works') {
        return;
    }

    // メディアライブラリ用のスクリプト
    wp_enqueue_media();
    // 並び替え用の jQuery UI
    wp_enqueue_script('jquery-ui-sortable');
}
add_action('admin_enqueue_scripts', 'helixia_works_admin_assets');

/**
 * 実績のサムネイル（最初の画像）を取得するヘルパー関数
 */
function helixia_get_works_thumbnail_src($post_id, $size = 'full')
{
    $images = get_post_meta($post_id, '_works_images', true);
    if (!empty($images) && is_array($images)) {
        $img_src = wp_get_attachment_image_src($images[0], $size);
        if ($img_src) {
            return $img_src[0];
        }
    }
    // 代替としてアイキャッチ
    if (has_post_thumbnail($post_id)) {
        return get_the_post_thumbnail_url($post_id, $size);
    }
    // なければデフォルト
    return get_theme_file_uri('/img/no-image.webp');
}

