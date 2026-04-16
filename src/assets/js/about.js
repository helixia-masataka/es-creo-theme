/**
 * Aboutページ専用スクリプト
 */

document.addEventListener('DOMContentLoaded', () => {
    // PROFILE セクションのスライダー初期化
    const profileSwiperElement = document.querySelector('.js-about-profile-swiper');

    console.log('Profile Swiper Element:', profileSwiperElement);
    console.log('Swiper Type:', typeof Swiper);

    // スライダーのクラスが存在し､かつSwiperが読み込まれている場合のみ実行
    if (profileSwiperElement && typeof Swiper !== 'undefined') {
        const swiper = new Swiper(profileSwiperElement, {
            loop: true, // 無限ループを有効化
            slidesPerView: 1.2, // スマホでは少し見切れるように
            spaceBetween: 20,
            // centeredSlides: true, // アクティブなスライドを中央に
            grabCursor: true,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            breakpoints: {
                768: {
                    slidesPerView: 2.2,
                    spaceBetween: 30,
                },
                1024: {
                    slidesPerView: 2.5,
                    spaceBetween: 32,
                }
            },
            on: {
                init: function () {
                    console.log('Swiper initialized!');
                },
            },
        });
    }
});
