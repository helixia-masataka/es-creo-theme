<?php
//* ===============================================
//# ビジネス向け構造化データ（LocalBusiness / JobPosting / Event）
//* ===============================================
//
// 【このファイルの特徴】
// Googleの検索結果上でリッチリザルト（ナレッジパネルやカード型の目立つ表示）を獲得・最適化するための「構造化データ (JSON-LD)」を構築・自動生成します。
//
// 1. LocalBusiness 構造化データ（Googleマップ連携）
//    - カスタマイザーから会社情報（住所・電話・営業時間）を設定しトップページに出力します。
// 2. JobPosting 構造化データ（Google しごと検索）
//    - カスタム投稿「recruit」の詳細ページに自動出力し、Google検索の求人情報枠への表示を促進します。
// 3. Event 構造化データ（セミナー・イベント）
//    - カスタム投稿「event」の詳細ページに自動出力し、イベント情報のリッチリザルトに対応します。
//


//* ===============================================
//# 1. LocalBusiness 構造化データ
//* ===============================================

// カスタマイザーに企業情報セクションを追加
function helixia_local_business_customizer($wp_customize)
{
    // セクション
    $wp_customize->add_section('helixia_local_business', array(
        'title'    => '企業情報（LocalBusiness）',
        'priority' => 45,
    ));

    // 設定フィールド定義
    $fields = array(
        'helixia_biz_type'      => array('label' => '業種タイプ', 'default' => 'LocalBusiness', 'description' => '例: Restaurant, Dentist, LegalService, RealEstateAgent, BeautySalon'),
        'helixia_biz_name'      => array('label' => '会社名/店舗名', 'default' => ''),
        'helixia_biz_tel'       => array('label' => '電話番号', 'default' => '', 'description' => '例: +81-3-1234-5678'),
        'helixia_biz_email'     => array('label' => 'メールアドレス', 'default' => ''),
        'helixia_biz_zip'       => array('label' => '郵便番号', 'default' => ''),
        'helixia_biz_region'    => array('label' => '都道府県', 'default' => ''),
        'helixia_biz_city'      => array('label' => '市区町村', 'default' => ''),
        'helixia_biz_street'    => array('label' => '番地・建物名', 'default' => ''),
        'helixia_biz_lat'       => array('label' => '緯度（latitude）', 'default' => '', 'description' => 'Google Maps で取得可能'),
        'helixia_biz_lng'       => array('label' => '経度（longitude）', 'default' => '', 'description' => 'Google Maps で取得可能'),
        'helixia_biz_hours'     => array('label' => '営業時間', 'default' => 'Mo-Fr 09:00-18:00', 'description' => '形式: Mo-Fr 09:00-18:00, Sa 10:00-15:00'),
        'helixia_biz_price'     => array('label' => '価格帯', 'default' => '$$', 'description' => '$ ～ $$$$'),
    );

    foreach ($fields as $id => $args) {
        $wp_customize->add_setting($id, array(
            'default'           => $args['default'],
            'sanitize_callback' => 'sanitize_text_field',
        ));

        $control_args = array(
            'label'   => $args['label'],
            'section' => 'helixia_local_business',
        );

        if (isset($args['description'])) {
            $control_args['description'] = $args['description'];
        }

        $wp_customize->add_control($id, $control_args);
    }
}
add_action('customize_register', 'helixia_local_business_customizer');

// LocalBusiness JSON-LD 出力（トップページのみ）
function helixia_local_business_schema()
{
    if (!is_front_page()) {
        return;
    }

    $biz_name = get_theme_mod('helixia_biz_name', '');
    if (empty($biz_name)) {
        $biz_name = get_bloginfo('name');
    }

    // 住所が未設定なら出力しない
    $region = get_theme_mod('helixia_biz_region', '');
    if (empty($region)) {
        return;
    }

    $schema = array(
        '@context' => 'https://schema.org',
        '@type'    => get_theme_mod('helixia_biz_type', 'LocalBusiness'),
        'name'     => $biz_name,
        'url'      => home_url('/'),
        'address'  => array(
            '@type'           => 'PostalAddress',
            'postalCode'      => get_theme_mod('helixia_biz_zip', ''),
            'addressRegion'   => $region,
            'addressLocality' => get_theme_mod('helixia_biz_city', ''),
            'streetAddress'   => get_theme_mod('helixia_biz_street', ''),
            'addressCountry'  => 'JP',
        ),
    );

    // 電話番号
    $tel = get_theme_mod('helixia_biz_tel', '');
    if (!empty($tel)) {
        $schema['telephone'] = $tel;
    }

    // メールアドレス
    $email = get_theme_mod('helixia_biz_email', '');
    if (!empty($email)) {
        $schema['email'] = $email;
    }

    // ロゴ
    $logo_url = get_theme_file_uri('/img/ogp-default.webp');
    $custom_logo_id = get_theme_mod('custom_logo');
    if ($custom_logo_id) {
        $logo_image = wp_get_attachment_image_src($custom_logo_id, 'full');
        if ($logo_image) {
            $logo_url = $logo_image[0];
        }
    }
    $schema['image'] = esc_url($logo_url);

    // 緯度・経度
    $lat = get_theme_mod('helixia_biz_lat', '');
    $lng = get_theme_mod('helixia_biz_lng', '');
    if (!empty($lat) && !empty($lng)) {
        $schema['geo'] = array(
            '@type'     => 'GeoCoordinates',
            'latitude'  => (float)$lat,
            'longitude' => (float)$lng,
        );
    }

    // 営業時間
    $hours = get_theme_mod('helixia_biz_hours', '');
    if (!empty($hours)) {
        $schema['openingHours'] = $hours;
    }

    // 価格帯
    $price = get_theme_mod('helixia_biz_price', '');
    if (!empty($price)) {
        $schema['priceRange'] = $price;
    }

    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
    echo '</script>' . "\n";
}
add_action('wp_head', 'helixia_local_business_schema', 3);


//* ===============================================
//# 2. JobPosting 構造化データ（Google しごと検索）
//* ===============================================

// カスタム投稿「求人」の登録
function helixia_register_recruit_post_type()
{
    if (post_type_exists('recruit')) {
        return; // 別のプラグインで既に登録済みの場合はスキップ
    }

    register_post_type('recruit', array(
        'labels' => array(
            'name'          => '求人',
            'singular_name' => '求人',
            'add_new'       => '求人を追加',
            'add_new_item'  => '新しい求人を追加',
            'edit_item'     => '求人を編集',
        ),
        'public'       => true,
        'has_archive'  => true,
        'menu_icon'    => 'dashicons-businessperson',
        'supports'     => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'rewrite'      => array('slug' => 'recruit'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'helixia_register_recruit_post_type');

// JobPosting メタボックス
function helixia_recruit_meta_boxes()
{
    add_meta_box(
        'helixia_recruit_details',
        '求人情報（Google しごと検索用）',
        'helixia_recruit_meta_box_content',
        'recruit',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'helixia_recruit_meta_boxes');

function helixia_recruit_meta_box_content($post)
{
    wp_nonce_field('helixia_recruit_meta', 'helixia_recruit_nonce');

    $fields = array(
        '_recruit_employment_type' => array('label' => '雇用形態', 'placeholder' => 'FULL_TIME / PART_TIME / CONTRACT / TEMPORARY / INTERN', 'default' => 'FULL_TIME'),
        '_recruit_salary_min'      => array('label' => '最低給与（月額・円）', 'placeholder' => '例: 250000', 'default' => ''),
        '_recruit_salary_max'      => array('label' => '最高給与（月額・円）', 'placeholder' => '例: 400000', 'default' => ''),
        '_recruit_location'        => array('label' => '勤務地', 'placeholder' => '例: 東京都渋谷区...', 'default' => ''),
        '_recruit_valid_through'   => array('label' => '応募期限', 'placeholder' => 'YYYY-MM-DD', 'default' => ''),
    );

    echo '<table class="form-table"><tbody>';
    foreach ($fields as $key => $args) {
        $value = get_post_meta($post->ID, $key, true);
        if (empty($value)) {
            $value = $args['default'];
        }
        echo '<tr>';
        echo '<th><label for="' . esc_attr($key) . '">' . esc_html($args['label']) . '</label></th>';
        echo '<td><input type="text" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '" placeholder="' . esc_attr($args['placeholder']) . '" class="regular-text"></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

// メタデータ保存
function helixia_save_recruit_meta($post_id)
{
    if (!isset($_POST['helixia_recruit_nonce']) || !wp_verify_nonce($_POST['helixia_recruit_nonce'], 'helixia_recruit_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    $fields = array('_recruit_employment_type', '_recruit_salary_min', '_recruit_salary_max', '_recruit_location', '_recruit_valid_through');

    foreach ($fields as $key) {
        if (isset($_POST[$key])) {
            update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
        }
    }
}
add_action('save_post_recruit', 'helixia_save_recruit_meta');

// JobPosting JSON-LD 出力
function helixia_job_posting_schema()
{
    if (!is_singular('recruit')) {
        return;
    }

    global $post;

    $schema = array(
        '@context'      => 'https://schema.org',
        '@type'         => 'JobPosting',
        'title'         => get_the_title(),
        'description'   => wp_strip_all_tags(get_the_content()),
        'datePosted'    => get_the_date('c'),
        'hiringOrganization' => array(
            '@type'  => 'Organization',
            'name'   => get_bloginfo('name'),
            'sameAs' => home_url('/'),
        ),
    );

    // 雇用形態
    $employment_type = get_post_meta($post->ID, '_recruit_employment_type', true);
    if (!empty($employment_type)) {
        $schema['employmentType'] = $employment_type;
    }

    // 給与
    $salary_min = get_post_meta($post->ID, '_recruit_salary_min', true);
    $salary_max = get_post_meta($post->ID, '_recruit_salary_max', true);
    if (!empty($salary_min)) {
        $salary = array(
            '@type'    => 'MonetaryAmount',
            'currency' => 'JPY',
            'value'    => array(
                '@type'    => 'QuantitativeValue',
                'unitText' => 'MONTH',
            ),
        );

        if (!empty($salary_max)) {
            $salary['value']['minValue'] = (int)$salary_min;
            $salary['value']['maxValue'] = (int)$salary_max;
        } else {
            $salary['value']['value'] = (int)$salary_min;
        }

        $schema['baseSalary'] = $salary;
    }

    // 勤務地
    $location = get_post_meta($post->ID, '_recruit_location', true);
    if (!empty($location)) {
        $schema['jobLocation'] = array(
            '@type'   => 'Place',
            'address' => array(
                '@type'          => 'PostalAddress',
                'streetAddress'  => $location,
                'addressCountry' => 'JP',
            ),
        );
    }

    // 応募期限
    $valid_through = get_post_meta($post->ID, '_recruit_valid_through', true);
    if (!empty($valid_through)) {
        $schema['validThrough'] = $valid_through . 'T23:59:59+09:00';
    }

    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
    echo '</script>' . "\n";
}
add_action('wp_head', 'helixia_job_posting_schema', 3);


//* ===============================================
//# 3. Event 構造化データ（セミナー・イベント）
//* ===============================================

// カスタム投稿「イベント」の登録
function helixia_register_event_post_type()
{
    if (post_type_exists('event')) {
        return;
    }

    register_post_type('event', array(
        'labels' => array(
            'name'          => 'イベント',
            'singular_name' => 'イベント',
            'add_new'       => 'イベントを追加',
            'add_new_item'  => '新しいイベントを追加',
            'edit_item'     => 'イベントを編集',
        ),
        'public'       => true,
        'has_archive'  => true,
        'menu_icon'    => 'dashicons-calendar-alt',
        'supports'     => array('title', 'editor', 'thumbnail', 'custom-fields'),
        'rewrite'      => array('slug' => 'event'),
        'show_in_rest' => true,
    ));
}
add_action('init', 'helixia_register_event_post_type');

// Event メタボックス
function helixia_event_meta_boxes()
{
    add_meta_box(
        'helixia_event_details',
        'イベント情報（Google 検索用）',
        'helixia_event_meta_box_content',
        'event',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'helixia_event_meta_boxes');

function helixia_event_meta_box_content($post)
{
    wp_nonce_field('helixia_event_meta', 'helixia_event_nonce');

    $fields = array(
        '_event_start_date'  => array('label' => '開始日時', 'type' => 'datetime-local', 'default' => ''),
        '_event_end_date'    => array('label' => '終了日時', 'type' => 'datetime-local', 'default' => ''),
        '_event_location'    => array('label' => '会場名', 'type' => 'text', 'default' => '', 'placeholder' => '例: ○○ホール'),
        '_event_address'     => array('label' => '会場住所', 'type' => 'text', 'default' => '', 'placeholder' => '例: 東京都千代田区...'),
        '_event_online_url'  => array('label' => 'オンラインURL（ウェビナーの場合）', 'type' => 'url', 'default' => ''),
        '_event_ticket_url'  => array('label' => 'チケット/申込URL', 'type' => 'url', 'default' => ''),
        '_event_price'       => array('label' => '参加費（円）', 'type' => 'text', 'default' => '0', 'placeholder' => '0 = 無料'),
        '_event_status'      => array('label' => '開催状態', 'type' => 'text', 'default' => 'EventScheduled', 'placeholder' => 'EventScheduled / EventPostponed / EventCancelled'),
    );

    echo '<table class="form-table"><tbody>';
    foreach ($fields as $key => $args) {
        $value = get_post_meta($post->ID, $key, true);
        if (empty($value) && !empty($args['default'])) {
            $value = $args['default'];
        }
        echo '<tr>';
        echo '<th><label for="' . esc_attr($key) . '">' . esc_html($args['label']) . '</label></th>';
        echo '<td><input type="' . esc_attr($args['type']) . '" id="' . esc_attr($key) . '" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '"';
        if (isset($args['placeholder'])) {
            echo ' placeholder="' . esc_attr($args['placeholder']) . '"';
        }
        echo ' class="regular-text"></td>';
        echo '</tr>';
    }
    echo '</tbody></table>';
}

// メタデータ保存
function helixia_save_event_meta($post_id)
{
    if (!isset($_POST['helixia_event_nonce']) || !wp_verify_nonce($_POST['helixia_event_nonce'], 'helixia_event_meta')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    $fields = array('_event_start_date', '_event_end_date', '_event_location', '_event_address', '_event_online_url', '_event_ticket_url', '_event_price', '_event_status');

    foreach ($fields as $key) {
        if (isset($_POST[$key])) {
            update_post_meta($post_id, $key, sanitize_text_field($_POST[$key]));
        }
    }
}
add_action('save_post_event', 'helixia_save_event_meta');

// Event JSON-LD 出力
function helixia_event_schema()
{
    if (!is_singular('event')) {
        return;
    }

    global $post;

    $start_date = get_post_meta($post->ID, '_event_start_date', true);
    if (empty($start_date)) {
        return;
    }

    $schema = array(
        '@context'    => 'https://schema.org',
        '@type'       => 'Event',
        'name'        => get_the_title(),
        'description' => wp_strip_all_tags(get_the_excerpt()),
        'startDate'   => $start_date,
        'eventStatus' => 'https://schema.org/' . get_post_meta($post->ID, '_event_status', true),
        'organizer'   => array(
            '@type' => 'Organization',
            'name'  => get_bloginfo('name'),
            'url'   => home_url('/'),
        ),
    );

    // 終了日時
    $end_date = get_post_meta($post->ID, '_event_end_date', true);
    if (!empty($end_date)) {
        $schema['endDate'] = $end_date;
    }

    // アイキャッチ画像
    if (has_post_thumbnail()) {
        $schema['image'] = get_the_post_thumbnail_url($post->ID, 'large');
    }

    // 開催場所（物理 or オンライン）
    $online_url = get_post_meta($post->ID, '_event_online_url', true);
    $location_name = get_post_meta($post->ID, '_event_location', true);

    if (!empty($online_url)) {
        $schema['eventAttendanceMode'] = 'https://schema.org/OnlineEventAttendanceMode';
        $schema['location'] = array(
            '@type' => 'VirtualLocation',
            'url'   => $online_url,
        );
    } elseif (!empty($location_name)) {
        $schema['eventAttendanceMode'] = 'https://schema.org/OfflineEventAttendanceMode';
        $schema['location'] = array(
            '@type'   => 'Place',
            'name'    => $location_name,
            'address' => array(
                '@type'          => 'PostalAddress',
                'streetAddress'  => get_post_meta($post->ID, '_event_address', true),
                'addressCountry' => 'JP',
            ),
        );
    }

    // チケット/参加費
    $ticket_url = get_post_meta($post->ID, '_event_ticket_url', true);
    $price = get_post_meta($post->ID, '_event_price', true);

    if (!empty($ticket_url) || $price !== '') {
        $offer = array(
            '@type'         => 'Offer',
            'price'         => (int)$price,
            'priceCurrency' => 'JPY',
            'availability'  => 'https://schema.org/InStock',
        );
        if (!empty($ticket_url)) {
            $offer['url'] = $ticket_url;
        }
        $schema['offers'] = $offer;
    }

    echo '<script type="application/ld+json">' . "\n";
    echo wp_json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . "\n";
    echo '</script>' . "\n";
}
add_action('wp_head', 'helixia_event_schema', 3);
