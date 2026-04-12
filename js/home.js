/**
 * ホームページ（Front Page）専用スクリプト
 * 実績一覧のAjaxフィルタリングを制御
 */

document.addEventListener('DOMContentLoaded', () => {
    const worksGrid = document.querySelector('#js-works-grid');
    const categoryLinks = document.querySelectorAll('.p-home-categories__item a');

    if (!worksGrid || categoryLinks.length === 0) return;

    categoryLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();

            // すでにアクティブなら何もしない
            const parentItem = link.parentElement;
            if (parentItem.classList.contains('--active')) return;

            const catSlug = link.getAttribute('data-cat');

            // 1. アクティブ状態の切り替え
            categoryLinks.forEach(l => l.parentElement.classList.remove('--active'));
            parentItem.classList.add('--active');

            // 2. フェードアウトとAjax通信
            worksGrid.style.transition = 'opacity 0.3s ease';
            worksGrid.style.opacity = '0.4'; // 読み込み中の演出

            const formData = new FormData();
            formData.append('action', 'helixia_filter_works');
            formData.append('cat', catSlug);
            formData.append('nonce', helixia_ajax.nonce);

            fetch(helixia_ajax.url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(res => {
                if (res.success) {
                    // 3. コンテンツの差し替えとフェードイン
                    worksGrid.innerHTML = res.data;
                    worksGrid.style.opacity = '1';
                } else {
                    console.error('Ajax Error:', res.data);
                    worksGrid.style.opacity = '1';
                }
            })
            .catch(error => {
                console.error('Fetch Error:', error);
                worksGrid.style.opacity = '1';
            });
        });
    });
});
