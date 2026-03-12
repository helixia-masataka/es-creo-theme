//=============================================== *//
//ビューポートリサイズ
!(function () {
    const viewport = document.querySelector('meta[name="viewport"]');
    function switchViewport() {
        const value = window.outerWidth > 375 ? 'width=device-width,initial-scale=1' : 'width=375';
        if (viewport.getAttribute('content') !== value) {
            viewport.setAttribute('content', value);
        }
    }
    addEventListener('resize', switchViewport, false);
    switchViewport();
})();
//* ===============================================

//=============================================== *//
//ドロワーメニュー (WAI-ARIA & Focus Trap 対応)
document.addEventListener('DOMContentLoaded', () => {
    const drawerBtn = document.querySelector('.c-drawer__btn');
    const drawer = document.querySelector('.c-drawer');
    const body = document.body;

    // 要素が存在しないページではエラーを出さずに処理を終了する
    if (!drawerBtn || !drawer) return;

    // モーダル（ドロワーメニュー）内で Tab 移動可能な要素セレクタ
    const focusableSelector = `
      a[href], area[href],
      button:not([disabled]),
      input:not([disabled]),
      select:not([disabled]),
      textarea:not([disabled]),
      [tabindex]:not([tabindex="-1"])
    `;

    // ---------- 開く ----------
    function openDrawer() {
        drawerBtn.classList.add('is-open');
        drawer.classList.add('is-open');
        body.classList.add('is-open'); // CSS側で body.is-open { overflow: hidden; } と連動させる

        // ★改善: WAI-ARIA 状態を「開いている」に更新
        drawerBtn.setAttribute('aria-expanded', 'true');
        drawer.setAttribute('aria-hidden', 'false');

        // ESCキーやフォーカストラップを監視
        document.addEventListener('keydown', handleKeydown);

        // 最初にフォーカスする要素を先頭に (少し遅延させるとフォーカス移動が安定します)
        setTimeout(() => {
            const focusableEls = drawer.querySelectorAll(focusableSelector);
            if (focusableEls.length > 0) {
                focusableEls[0].focus();
            }
        }, 10);
    }

    // ---------- 閉じる ----------
    function closeDrawer() {
        drawerBtn.classList.remove('is-open');
        drawer.classList.remove('is-open');
        body.classList.remove('is-open');

        // ★改善: WAI-ARIA 状態を「閉じている」に更新
        drawerBtn.setAttribute('aria-expanded', 'false');
        drawer.setAttribute('aria-hidden', 'true');

        // 監視解除
        document.removeEventListener('keydown', handleKeydown);

        // ボタンにフォーカスを戻す（ユーザーが迷子にならないための重要処理）
        drawerBtn.focus();
    }

    // ---------- フォーカストラップ & ESC ----------
    function handleKeydown(e) {
        // ESCキー押下で閉じる
        if (e.key === 'Escape' || e.keyCode === 27) {
            closeDrawer();
            return;
        }

        // Tab or Shift+Tab でフォーカストラップ
        if (e.key === 'Tab' || e.keyCode === 9) {
            trapFocus(e);
        }
    }

    function trapFocus(e) {
        const focusableEls = drawer.querySelectorAll(focusableSelector);
        if (!focusableEls.length) return;

        const firstEl = focusableEls[0];
        const lastEl = focusableEls[focusableEls.length - 1];

        // 「Shift + Tab」かつ「今のフォーカスが先頭要素」の場合、末尾へ移動
        if (e.shiftKey && document.activeElement === firstEl) {
            e.preventDefault();
            lastEl.focus();
        }
        // 「Tab」かつ「今のフォーカスが末尾要素」の場合、先頭へ移動
        else if (!e.shiftKey && document.activeElement === lastEl) {
            e.preventDefault();
            firstEl.focus();
        }
    }

    // ---------- ボタン操作で開閉 ----------
    drawerBtn.addEventListener('click', () => {
        // aria-expanded の状態で開閉を判定する（より確実な判定方法）
        const isExpanded = drawerBtn.getAttribute('aria-expanded') === 'true';
        if (isExpanded) {
            closeDrawer();
        } else {
            openDrawer();
        }
    });

    // ---------- ドロワー内のクリック制御 ----------
    drawer.addEventListener('click', (e) => {
        // ① オーバーレイ（ドロワーの背景部分）をクリックした時に閉じる
        // ※ drawer自身が全画面の黒背景で、その中に白いメニューがある構造を想定
        if (e.target === drawer) {
            closeDrawer();
        }

        // ② ドロワー内のリンク (aタグ) をクリックした時に閉じる
        // ※ ページ内リンク（#contact など）を押した時にメニューが開きっぱなしになるのを防ぐ
        const isLink = e.target.closest('a[href]');
        if (isLink) {
            closeDrawer();
        }
    });
});
//=============================================== *//

//=============================================== *//
//スムーススクロール
const smoothScrollTriggers = document.querySelectorAll('a[href^="#"]');
smoothScrollTriggers.forEach((smoothScrollTrigger) => {
    smoothScrollTrigger.addEventListener('click', (e) => {
        e.preventDefault();
        const hrefLink = smoothScrollTrigger.getAttribute('href');

        // "#"のみのリンクまたはページ内リンクでない場合は処理をスキップ
        if (hrefLink === '#' || !hrefLink.startsWith('#')) {
            return;
        }
        const targetElement = document.getElementById(hrefLink.replace('#', ''));

        // 対象の要素が存在しない場合は処理をスキップ
        if (!targetElement) {
            return;
        }

        const rectTop = targetElement.getBoundingClientRect().top;
        const offset = window.pageYOffset;
        const scrollTarget = rectTop + offset;
        window.scrollTo({
            top: scrollTarget,
            behavior: 'smooth',
        });
    });
});


//* ===============================================
//# スクロール時、カラー変更
document.addEventListener('DOMContentLoaded', function () {
    const cDrawerBtn = document.querySelector('.c-drawer__btn');
    const cDrawerBar = document.querySelector('.c-drawer__bars');
    const cDrawerBarSpan = cDrawerBar.querySelectorAll('span');

    function setDrawerBarSpanColor(color) {
        // 取得した NodeList をループして各要素に色を適用
        cDrawerBarSpan.forEach(function (span) {
            span.style.backgroundColor = color;
        });
    }

    function handleScroll() {
        // 画面幅が1279px以下 かつ スクロール量が0より大きいとき
        if (window.innerWidth <= 1279 && window.scrollY > 0) {
            // ボタンの背景色をオレンジに
            cDrawerBtn.style.backgroundColor = 'var(--global-green)';
            // span の背景色を白に
            setDrawerBarSpanColor('var(--color-secondary)');
        } else {
            // 条件に当てはまらない場合はデフォルトに戻す
            cDrawerBtn.style.backgroundColor = '';
            setDrawerBarSpanColor('');
        }
    }

    // スクロール時
    window.addEventListener('scroll', handleScroll);
    // リサイズ時
    window.addEventListener('resize', handleScroll);
    // 初期ロード時
    handleScroll();
});
//=============================================== *//

//* ===============================================
//# スクロール時ロゴ隠す
document.addEventListener('DOMContentLoaded', function () {
    const logo = document.querySelector('.l-header__logo');

    window.addEventListener('scroll', function () {
        // スクロール量をチェック
        const scrollY = window.scrollY;

        // スクロール量が100pxを超えたら隠す
        if (scrollY > 100) {
            logo.style.opacity = '0';
            logo.style.visibility = 'hidden';
        } else {
            logo.style.opacity = '1';
            logo.style.visibility = 'visible';
        }
    });
});
//=============================================== *//
//* ===============================================
//# // フェードインアニメーション
document.addEventListener('DOMContentLoaded', function () {
    window.addEventListener('scroll', function () {
        const inviewElements = document.querySelectorAll('.inview');

        inviewElements.forEach(function (el) {
            const rect = el.getBoundingClientRect();
            const targetPosition = rect.top + window.pageYOffset;

            const scroll = window.pageYOffset;

            const windowHeight = window.innerHeight;

            if (scroll > targetPosition - windowHeight) {
                el.classList.add('show');
            }
        });
    });
});

//=============================================== *//

//* ===============================================
//# ページトップに戻るボタン
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('js-to-top');
    if (!btn) return;

    let ticking = false;

    window.addEventListener('scroll', function () {
        if (!ticking) {
            requestAnimationFrame(function () {
                if (window.pageYOffset > 300) {
                    btn.classList.add('is-visible');
                } else {
                    btn.classList.remove('is-visible');
                }
                ticking = false;
            });
            ticking = true;
        }
    });

    btn.addEventListener('click', function () {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});
//=============================================== *//
