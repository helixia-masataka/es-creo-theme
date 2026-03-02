<?php
/**
 * All-in-One WP Migration使用時の除外フォルダの登録
 * $_theme_nameの値をフォルダ名に応じて変更する
 */
$_theme_name = '最新WP開発マスター';
add_filter(
    'ai1wm_exclude_themes_from_export',
    function ($exclude_filters) use ($_theme_name) {
        $new_exclusions = array(
            "{$_theme_name}/node_modules",
            "{$_theme_name}/src/js",
            "{$_theme_name}/src/img",
            "{$_theme_name}/package-lock.json",
            "{$_theme_name}/package.json",
            "{$_theme_name}/Read-first.md"
        );
        return array_merge($exclude_filters, $new_exclusions);
    }
);
