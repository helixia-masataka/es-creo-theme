<?php
/**
 * Contact Form 7 Custom Honeypot
 *
 * スパムボット対策のための、自作ハニーポット機能です。
 * 人間には見えない隠しフィールドをチェックし、入力がある場合は送信をブロックします。
 */

// フィールド名（ボットが騙されやすい名前を推奨）
define('HELIXIA_HP_FIELD_NAME', 'id-extra-field');

/**
 * CF7のバリデーション時に、ハニーポットフィールドをチェックする
 */
function helixia_cf7_honeypot_validation($result, $tag)
{
    // 指定したフィールド名以外はスルー
    if ($tag->name !== HELIXIA_HP_FIELD_NAME) {
        return $result;
    }

    // 値が入力されている場合（ボットによる入力）
    $value = isset($_POST[HELIXIA_HP_FIELD_NAME]) ? $_POST[HELIXIA_HP_FIELD_NAME] : '';

    if (!empty($value)) {
        // バリデーションエラーを発生させ、送信を停止する
        $result->invalidate($tag, 'Spam detected.');
    }

    return $result;
}

// テキスト入力フィールドのバリデーションにフック
add_filter('wpcf7_validate_text', 'helixia_cf7_honeypot_validation', 10, 2);
add_filter('wpcf7_validate_text*', 'helixia_cf7_honeypot_validation', 10, 2);
