# Helixia WordPress Theme — コーダー向け使用マニュアル

> **バージョン**: 1.0 | **対象読者**: 外注コーダー

---

## 目次

1. [開発を始める前に](#1-開発を始める前に)
2. [フォルダ構成](#2-フォルダ構成)
3. [Gulpによる開発フロー](#3-gulpによる開発フロー)
4. [SCSS設計ルール](#4-scss設計ルール)
5. [CSS関数・ミキシン一覧](#5-css関数ミキシン一覧)
6. [PHPテンプレート構成](#6-phpテンプレート構成)
7. [inc/ 各ファイルの設定変更ポイント](#7-inc-各ファイルの設定変更ポイント)
8. [新しいページを作る手順](#8-新しいページを作る手順)
9. [WordPress管理画面での設定](#9-wordpress管理画面での設定)
10. [必須プラグイン](#10-必須プラグイン)
11. [やってはいけないこと](#11-やってはいけないこと)
12. [テストサーバーへの自動デプロイ(CI/CD)](#12-テストサーバーへの自動デプロイcicd)
13. [ダークモード対応の指針](#13-ダークモード対応の指針)

---

## 1. 開発を始める前に

### 必要な環境

- **Node.js** v18以上
- **npm** v9以上
- **Local** (Flywheel) または他のWordPressローカル環境

### セットアップ手順

```bash
# テーマフォルダに移動
cd /path/to/theme

# npmパッケージをインストール（初回のみ）
npm install

# 開発開始（Gulp起動）
npm run dev
```

### gulpfile.js の接続URL設定（最初に必ず変更）

```js
// gulpfile.js の上部
const projectUrl = "http://helixia-wp-theme.local/";
//                  ↑ Local のサイトURLに変更する
```

---

## 2. フォルダ構成

```
テーマルート/
├── src/                    # ← 開発ファイル（ここを編集する）
│   └── assets/
│       ├── sass/           # SCSSファイル
│       ├── js/             # JavaScriptファイル
│       └── img/            # 画像ファイル（PNG/JPG → 自動でWebP変換）
│
├── css/                    # ← コンパイル済みCSS（編集禁止・自動生成）
│   ├── style.css           # メインCSS
│   └── critical.css        # Critical CSS（ファーストビュー用）
│
├── js/                     # ← コンパイル済みJS（編集禁止・自動生成）
├── img/                    # ← WebP変換済み画像（編集禁止・自動生成）
│
├── inc/                    # ← PHPモジュール（機能ごとに分離）
├── template-parts/         # ← 共通パーツ（header/footer）
│
├── functions.php           # ← テーマ設定（incファイルの読み込み）
├── header.php              # WP標準header（wp_head()を含む）
├── footer.php              # WP標準footer（wp_footer()を含む）
├── front-page.php          # トップページテンプレート
├── page-contact.php        # お問い合わせページテンプレート
├── single.php              # 投稿個別ページ
├── archive-custom.php      # カスタム投稿タイプアーカイブ
└── gulpfile.js             # Gulp設定ファイル
```

---

## 3. Gulpによる開発フロー

### コマンド

| コマンド        | 用途                                                    |
| --------------- | ------------------------------------------------------- |
| `npm run dev`   | 開発サーバー起動（ファイル監視 + ブラウザ自動リロード） |
| `npm run build` | 本番用ビルド（全ファイルを一括コンパイル）              |

### 自動処理される内容

| 処理                      | 入力                            | 出力               |
| ------------------------- | ------------------------------- | ------------------ |
| Sassコンパイル            | `src/assets/sass/**/*.scss`     | `css/style.css`    |
| Critical CSSコンパイル    | `src/assets/sass/critical.scss` | `css/critical.css` |
| JS圧縮                    | `src/assets/js/**/*.js`         | `js/`              |
| 画像WebP変換              | `src/assets/img/**/*.{png,jpg}` | `img/*.webp`       |
| PHPのimg参照を.webpに更新 | `**/*.php`                      | （上書き）         |

> ⚠️ **`css/` `js/` `img/` フォルダは直接編集しない。** 常に `src/` を編集すること。

---

## 4. SCSS設計ルール

### フォルダ構成

```
src/assets/sass/
├── settings/       # 変数・関数・mixin（設定のみ､スタイル出力なし）
│   ├── root.scss   # CSS変数（カラー・フォント・サイズ） ← よく編集する
│   ├── functions.scss  # Sass関数（rem, fluid等）
│   ├── mixin.scss  # ミックスイン（mq等）
│   ├── reset.scss  # CSSリセット
│   ├── base.scss   # body・基本要素スタイル
│   └── global.scss # ユーティリティクラス（.flex, .sp-only等）
│
├── layout/         # レイアウト（l- プレフィックス）
│   ├── l-header.scss
│   ├── l-footer.scss
│   ├── l-main.scss
│   ├── l-section.scss
│   ├── l-container.scss
│   └── l-columns.scss
│
├── components/     # 再利用コンポーネント（c- プレフィックス）
│   ├── c-btn-link.scss
│   ├── c-heading.scss
│   ├── c-cta.scss
│   ├── c-breadcrumb.scss
│   ├── c-drawer.scss
│   ├── c-contact.scss
│   ├── c-popular-posts.scss
│   └── c-to-top.scss
│
├── pages/          # ページ固有スタイル（p- プレフィックス）
│   ├── p-home.scss     # トップページ
│   ├── p-contact.scss  # お問い合わせ
│   ├── p-page.scss     # 固定ページ共通
│   └── p-static.scss   # 404・プライバシー・サンクスページ共通
│
├── critical.scss   # Critical CSSエントリポイント（ファーストビューのみ）
└── style.scss      # メインエントリポイント（全ファイルを読み込む）
```

### 命名規則（BEM）

```scss
// ブロック__エレメント --モディファイア
.p-home-mv {
} // ブロック
.p-home-mv__container {
} // エレメント
.p-home-mv --large {
} // モディファイア
```

### プレフィックスの意味

| プレフィックス | 意味                        | 例                        |
| -------------- | --------------------------- | ------------------------- |
| `l-`           | Layout（レイアウト）        | `l-header`, `l-container` |
| `c-`           | Component（コンポーネント） | `c-btn-link`, `c-heading` |
| `p-`           | Page（ページ固有）          | `p-home`, `p-contact`     |

### index.scssは自動生成される

`layout/` `components/` `pages/` の `index.scss` は **Gulp起動時に自動生成** される。新しいSCSSファイルを追加すると次回ビルド時に自動で `@use` が追記される。**手動編集不要**。

---

## 5. CSS関数・ミキシン一覧

### `functions.scss` で使える関数

```scss
// rem変換（px → rem）
font-size: rem(16); // → 1rem

// em変換（px → em）
letter-spacing: em(0.32, 16); // → 0.02em

// %変換（割合計算）
width: per(300, 1440); // → 20.833...%

// Fluid（画面幅に応じてリニアに変化）
font-size: fluid(14, 20); // SP:14px → PC:20px (clamp出力)
font-size: fluid(14, 20, 375, 1440); // ブレイクポイント指定あり

// Fluid（縦方向）
height: fluid-v(300, 600); // height用

// Fluid（コンテナクエリ基準）
font-size: fluid-c(12, 18); // コンテナ幅基準
```

### `mixin.scss` で使えるmixin

```scss
// メディアクエリ（モバイルファースト）
@include mq("md") {
} // 768px以上
@include mq("lg") {
} // 1024px以上
@include mq("xl") {
} // 1440px以上
@include mq(500) {
} // 任意のpx値

// max-width指定
@include mq("md", max) {
} // 767px以下
```

### ブレイクポイント一覧

| キー | px     |
| ---- | ------ |
| `md` | 768px  |
| `lg` | 1024px |
| `xl` | 1440px |

---

## 6. PHPテンプレート構成

### ページ追加の対応表

| 作りたいページ         | テンプレートファイル             |
| ---------------------- | -------------------------------- |
| トップページ           | `front-page.php`                 |
| 固定ページ（汎用）     | `page-{スラッグ}.php` を新規作成 |
| 投稿個別               | `single.php`                     |
| 投稿一覧               | `home.php`                       |
| カテゴリー             | `category.php`                   |
| カスタム投稿個別       | `single-{投稿タイプ}.php`        |
| カスタム投稿アーカイブ | `archive-custom.php`             |
| タクソノミー           | `taxonomy.php`                   |
| 検索結果               | `search.php`                     |
| 404                    | `404.php`                        |

### 共通パーツの呼び出し

```php
// ヘッダー（全ページで呼び出す）
get_header();

// フッター（全ページで呼び出す）
get_footer();

// テンプレートパーツ
get_template_part('template-parts/template-header');
get_template_part('template-parts/template-footer');
```

### ページ固有JSの追加方法

`page-type.php` でページスラッグを取得し､`functions.php` がデフォルトで `/js/{スラッグ}.js` を自動読み込みする。

```
例: スラッグ "home" → src/assets/js/home.js を作成するだけで自動読み込み
例: スラッグ "contact" → src/assets/js/contact.js を作成するだけで自動読み込み
```

---

## 7. inc/ 統合ファイルと各種設定の変更ポイント

機能ごとに分離されていた `inc/` フォルダのファイル群は､**9つの統合ファイル** にまとめられています。

### seo.php — SEO・OGP・サイトマップの設定

ほぼ自動ですが､以下は確認・変更が必要。

```php
// OGP画像のデフォルト（img/にogp-default.webpを必ず配置すること）
$image = get_theme_file_uri('/img/ogp-default.webp');
```

### root.scss — カラー・CSS変数の変更

**最もよく変更するファイル**。デザインカンプの配色に合わせて `①カラーパレット` の値を変更する。

```scss
// ① テーマカラーを変更する（ここだけ変えれば全体に反映）
--global-main: #0c6768; // メインカラー
--global-accent: #690e0d; // アクセントカラー

// ダークモードも自動で適用される
```

### performance.php — パフォーマンス最適化・フォント設定

LCP改善やGoogle Fonts最適化（旧 `fonts.php` や `speed.php` の機能）を含みます。フォントを変更する場合は以下のURL等を更新します。

```php
// フォントを変更する場合はこの2つのURLを更新
echo '<link rel="preload" ... href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@500;700&family=Poppins:wght@400;600&display=swap" ...>';
echo '<link rel="stylesheet" href="https://fonts.googleapis.com/css2?...">';
```

フォント変更後は `root.scss` のCSS変数も更新。

```scss
--font-jp: "Noto Sans JP", sans-serif; // 日本語フォント
--font-en: "Poppins", sans-serif; // 英字フォント
```

### analytics.php — GA4/GTMの設定

コードを直接書かず､**WordPress管理画面 → カスタマイズ → アナリティクス設定** からIDを入力するだけ。

### helper-frontend.php — UIアシスト機能

ページタイプの取得､ページネーション､Contact Form 7の設定（`contactform.php`）などが一つにまとまっています。

### critical.scss — Critical CSSの内容

ファーストビューが変わった場合（ヘッダー構成変更など）はこのファイルの `@use` を更新する。現在はトップページ（`p-home.scss`）がインクルードされています。

---

## 8. 新しいページを作る手順

### ① 固定ページテンプレートの追加例

> [!NOTE]
> `data-page="{スラッグ}"` は `header.php` の `get_data_page_type()` 関数が**自動で出力**します。ページテンプレートに手動で書く必要はありません。

```php
<?php
// page-service.php（サービスページの例）
get_header();
// ↑ header.php 内で <div id="page-container" data-page="service"> が自動出力される
?>
    <article class="p-service">
        <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
            <h1 class="c-heading"><?php the_title(); ?></h1>
            <?php the_content(); ?>
        <?php endwhile; endif; ?>
    </article>
<?php get_footer(); ?>
```

### ② ページ専用SCSSを追加

```
src/assets/sass/pages/p-service.scss を作成
→ Gulp監視中なら自動で pages/index.scss に追記される
```

### ③ ページ専用JSを追加（任意）

```
src/assets/js/service.js を作成
→ WordPressのスラッグが "service" なら自動で読み込まれる
```

---

## 9. WordPress管理画面での設定

| 設定場所                                 | 設定内容                                                   |
| ---------------------------------------- | ---------------------------------------------------------- |
| 外観 → カスタマイズ → アナリティクス設定 | **GA4測定ID / GTMコンテナID** の入力                       |
| 外観 → メニュー                          | ヘッダーメニュー・フッターメニューの設定                   |
| 外観 → カスタマイズ → サイトアイコン     | Favicon・Apple Touch Icon の設定                           |
| 外観 → カスタマイズ → ロゴ               | カスタムロゴの設定                                         |
| 設定 → パーマリンク                      | 変更後は「変更を保存」で `llms.txt` のリライトルールを更新 |

---

## 10. 必須プラグイン

| プラグイン                         | 用途                                                      |
| ---------------------------------- | --------------------------------------------------------- |
| **Contact Form 7**                 | お問い合わせフォーム（`helper-frontend.php` と連携済み）  |
| **All-in-One WP Migration**        | バックアップ・移行（`admin-tools.php` で除外設定済み）    |
| **All-In-One Security & Firewall** | WAF・ログイン保護（テーマではカバーできないサーバー保護） |
| **WP Multibyte Patch**             | 日本語WordPress環境での文字化け防止                       |

> ⚠️ **Yoast SEO / All in One SEO / GA系プラグインは入れない。** テーマ内の `seo.php` や `analytics.php` で機能がサポートされており､競合してエラーになるため。

---

## 11. やってはいけないこと

| NG行為                                   | 理由                                                       |
| ---------------------------------------- | ---------------------------------------------------------- |
| `css/` `js/` `img/` を直接編集           | Gulp実行で上書きされる                                     |
| SEOプラグインのインストール              | `seo.php` と競合してSEOが壊れる                            |
| `style.css` のTheme情報以外を編集        | コンパイル済みファイルのため上書きされる                   |
| `functions.php` に大量のコードを直接書く | `inc/` に機能別ファイルを追加する設計のため                |
| `wp_head()` や `wp_footer()` を削除      | テーマ全機能が動作しなくなる                               |
| WordPress標準のjQueryをenqueue           | `performance.php` で削除済み（独自実装にjQuery依存は禁止） |

---

---

## 12. テストサーバーへの自動デプロイ(CI/CD)

本テーマは GitHub Actions を利用した自動デプロイ環境が構築されています。

### 運用フロー

1.  **ローカル開発**: `npm run dev` でコーディング・ビルド確認。
2.  **GitHubへ反映**: 変更を `main` ブランチに `git push` する。
3.  **自動デプロイ**: GitHub Actions が起動し､アセットのビルド（CSS/JS生成）からテストサーバーへのFTP転送までを自動で行います。

### 同期される範囲（重要）

GitHub経由で同期できるのは **「プログラムファイル」** のみです。

| 同期するもの（GitHub経由）                    | 同期しないもの（プラグイン等が必要）           |
| :-------------------------------------------- | :--------------------------------------------- |
| テーマファイル一式（PHP/CSS/JS/画像アセット） | **投稿記事・固定ページの内容（データベース）** |
| `inc/` フォルダ内の機能定義                   | カテゴリー・タグ・メニューの設定               |
| `src/` 配下のソースコード                     | 管理画面のカスタマイザー設定                   |
| 依存パッケージ（自動ビルド）                  | メディアライブラリに手動アップした画像         |

> [!TIP]
> **記事や設定の同期について**
> 開発中に作成したテスト記事や､管理画面での設定､画像データなどをサーバーへ移動させたい場合は､従来通り **All-in-One WP Migration** 等の移行プラグインを使用してデータベースのエクスポート/インポートを行ってください。
# Triggering deploy with corrected remote path
