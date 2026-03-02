<?php
// Google Fontsの最速読み込み
function my_theme_optimized_google_fonts() {
    // 1. preconnect と dns-prefetch
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    echo '<link rel="dns-prefetch" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="dns-prefetch" href="https://fonts.gstatic.com">' . "\n";
    echo '<link rel="preconnect" href="https://cdn.jsdelivr.net">' . "\n";

    // 2. 読み込みたいフォントを配列で登録
    $fonts = array(
        'Noto+Sans+JP:wght@100..900', // Font A
        'Josefin+Sans:wght@400',      // Font B
    );

    // 3. フォント配列を「&family=」で合体させて、1つのURLを生成
    $base_url = 'https://fonts.googleapis.com/css2?family=';
    $joined_fonts = implode( '&family=', $fonts );
    $font_url = $base_url . $joined_fonts . '&display=swap';

    // 4. printハックを利用した非同期読み込み
    echo '<link rel="stylesheet" href="' . esc_url( $font_url ) . '" media="print" onload="this.media=\'all\'">' . "\n";

    // 5. JavaScriptが無効な環境向けのフォールバック
    echo '<noscript>' . "\n";
    echo '<link rel="stylesheet" href="' . esc_url( $font_url ) . '">' . "\n";
    echo '</noscript>' . "\n";
}
add_action( 'wp_head', 'my_theme_optimized_google_fonts', 1 );