// 【WordPress完全版】Gulpfile.js (2026 Edition)
// 機能: Sassコンパイル / Source Maps / 0px保持 / WebP変換 / エラー通知(安定版) / ブラウザ同期(Proxy) / PHP更新

import fs from 'fs';
import path from 'path';
import gulp from 'gulp';
import browserSync from 'browser-sync';
import gulpSass from 'gulp-sass';
import * as dartSass from 'sass';
import nodeNotifier from 'node-notifier';
import plumber from 'gulp-plumber';
import postcss from 'gulp-postcss';
import autoprefixer from 'autoprefixer';
import cssSorter from 'css-declaration-sorter';
import cssnano from 'cssnano';
import uglify from 'gulp-uglify';
import mergeRules from 'postcss-merge-rules';
import webp from 'gulp-webp';
import replace from 'gulp-replace';
import { deleteAsync } from 'del';
import watch from 'gulp-watch';
import gulpIf from 'gulp-if';
import sourcemaps from 'gulp-sourcemaps';

// 【設定】ローカルWordPressのURLを自動取得（Local Sitesフォルダ名から推測）
const siteNameMatch = process.cwd().match(/Local Sites[\\/]([^\\/]+)/);
const siteName = siteNameMatch ? siteNameMatch[1] : "localhost";
const projectUrl = `http://${siteName}.local/`; 

const sass = gulpSass(dartSass);
const browserSyncInstance = browserSync.create();

// フォルダ構成（WordPressテーマ内の構成に合わせて変更してください）
// ※ settingsフォルダなどは手動管理のためここには含めません
const scssDirs = ["layout", "components", "pages"]; 
const baseDir = "./src/assets/sass/";

// 共通のエラーハンドラ（通知付き・安定版）
function errorHandler(err) {
    console.error(err.message); 
    
    nodeNotifier.notify({
        title: 'Gulp Error',
        message: err.message,
        sound: 'Basso',
        wait: false
    });

    if (this && this.emit) {
        this.emit('end');
    } else {
        process.exit(1);
    }
}

// インデックス自動生成（アルファベット順）
function generateIndexScss(done) {
    scssDirs.forEach(dir => {
        const fullPath = path.join(baseDir, dir);
        if (fs.existsSync(fullPath) && fs.lstatSync(fullPath).isDirectory()) {
            const files = fs.readdirSync(fullPath)
                .filter(file => file.endsWith('.scss') && file !== 'index.scss');
            
            files.sort();

            const importStatements = files
                .map(file => `@use "${file.replace('.scss', '')}";`)
                .join('\n');
            
            fs.writeFileSync(
                path.join(fullPath, 'index.scss'),
                `/* Auto-generated index.scss for ${dir} */\n${importStatements}`
            );
        }
    });
    done();
}

// Sassコンパイル（Source Maps & 0px保護対応）
function compileSass() {
    return gulp.src(path.join(baseDir, 'style.scss'))
        .pipe(plumber({ errorHandler }))
        .pipe(sourcemaps.init()) // Source Maps開始
        .pipe(sass())
        .pipe(postcss([
            autoprefixer(),
            cssSorter(),
            mergeRules(),
            cssnano({
                preset: [
                    'default',
                    { discardZero: false }, // clamp内の0pxを守る設定
                ],
            }),
        ]))
        .pipe(sourcemaps.write('.')) // Source Maps書き出し
        .pipe(gulp.dest("./css/")) // WordPressの構成に合わせて出力先を確認してください
        .pipe(browserSyncInstance.stream())
        .on('end', () => {
            console.log('✅ Sass Compiled!');
        });
}

// Critical CSSコンパイル（インライン用のためSource Mapsなし）
function compileCritical() {
    return gulp.src(path.join(baseDir, 'critical.scss'))
        .pipe(plumber({ errorHandler }))
        .pipe(sass())
        .pipe(postcss([
            autoprefixer(),
            cssSorter(),
            mergeRules(),
            cssnano({
                preset: [
                    'default',
                    { discardZero: false },
                ],
            }),
        ]))
        .pipe(gulp.dest("./css/"))
        .on('end', () => {
            console.log('✅ Critical CSS Compiled!');
        });
}

// JS圧縮
function formatJS() {
    return gulp.src("./src/assets/js/**/*.js")
        .pipe(plumber({ errorHandler }))
        .pipe(uglify())
        .pipe(gulp.dest("./js/"))
        .pipe(browserSyncInstance.stream());
}

// 画像処理判定
function isWebPConvertible(file) {
    return !file.extname.endsWith('.svg');
}

// 画像WebP変換
function copyImage() {
    return gulp.src("./src/assets/img/**/*.{png,jpg,jpeg,svg}")
        .pipe(plumber({ errorHandler }))
        .pipe(gulpIf(isWebPConvertible, webp()))
        .pipe(gulp.dest("./img/"))
        .pipe(browserSyncInstance.stream())
        .on('end', async () => {
            await deleteAsync(["./img/**/*.{png,jpg,jpeg,gif}"]);
        });
}

// PHPファイル内の画像パスをwebpに更新
function updatePhp() {
    return gulp.src("./**/*.php")
        .pipe(plumber({ errorHandler }))
        .pipe(replace(/\.(png|jpg)/g, '.webp'))
        .pipe(gulp.dest("./")) // 上書き保存（テーマ直下などを想定）
        .pipe(browserSyncInstance.stream())
        .on('end', () => {
            console.log('✅ PHP Updated!');
        });
}

// 監視タスク
function watchFiles() {
    watch([baseDir + "**/*.scss", "!" + baseDir + "**/index.scss"], gulp.series(generateIndexScss, gulp.parallel(compileSass, compileCritical)));
    watch("./src/assets/js/**/*.js", gulp.series(formatJS));
    watch("./src/assets/img/**/*", gulp.series(copyImage));
    
    // PHPファイルの変更時はブラウザをリロード
    watch("./**/*.php").on('change', browserSyncInstance.reload);
}

// ブラウザ起動（WordPress用Proxy設定）
function browserInit(done) {
    browserSyncInstance.init({
        proxy: projectUrl, // LocalなどのURLへ転送
        notify: false
    });
    done();
}

export const generateIndexScssTask = generateIndexScss;
export const compileSassTask = compileSass;
export const compileCriticalTask = compileCritical;
export const watchTask = watchFiles;
export const browserInitTask = browserInit;
export const formatJSTask = formatJS;
export const updatePhpTask = updatePhp;

// コマンド登録
export const dev = gulp.parallel(browserInit, watchFiles);
export const build = gulp.parallel(formatJS, compileSass, compileCritical, copyImage, updatePhp);