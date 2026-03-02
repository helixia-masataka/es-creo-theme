<?php
// カスタム検索フォームのテンプレート
// サイト内で get_search_form() を呼び出すとこのファイルが読み込まれます
?>

<form role="search" method="get" class="p-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <div class="p-search-form__inner">
        
        <input 
            type="search" 
            class="p-search-form__input" 
            placeholder="キーワードを入力..." 
            value="<?php echo get_search_query(); ?>" 
            name="s" 
            id="s" 
            aria-label="サイト内検索"
        >
        
        <button type="submit" class="p-search-form__submit c-btn">
            <span class="c-btn-link --main">検索</span>
        </button>
        
    </div>
</form>