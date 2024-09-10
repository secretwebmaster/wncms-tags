# 爲所有 Laravel Eloquent 模型加上 Tag 功能

此套件為您的模型提供可標籤行為。安裝完成後，您只需將 `HasTags` trait 添加到 Eloquent 模型，即可使其可標籤。

我們不僅止於一般的標籤功能，WNCMS 標籤包含多種功能。內建支持[標籤翻譯]、[多重標籤類型]及[排序功能]。

以下以 Post 模組爲例：

```php
// apply HasTags trait to a model
use Illuminate\Database\Eloquent\Model;
use Wncms\Tags\HasTags;

class Post extends Model
{
    use HasTags;

    // ...
}
```

```php

// 創建你的模型，以Post為例
$post = Post::create([
   'name' => '文章標題',
]);

// 添加標籤
$post->attachTag('標籤1'); //不指定類型
$post->attachTag('標籤2', '文章標籤'); //指定類型
$post->attachTags(['標籤3', '標籤4']);  //同時創建多個，不指定類型
$post->attachTags(['分類1','分類2'], '文章分類'); //同時創建多個，指定類型，最常用

// 移除標籤
$post->detachTag('標籤1');
$post->detachTag('標籤2', '文章標籤');
$post->detachTags(['標籤3', '標籤4']);
$post->detachTags(['分類1', '分類2'], '文章分類');

// 取得模型的所有標籤
$post->tags;

// 同步標籤
$post->syncTags(['標籤1', '標籤2']); //其他標籤將被移除

// 同步帶有類型的標籤
$post->syncTagsWithType(['標籤1', '標籤2'], '文章標籤');
$post->syncTagsWithType(['紅色', '黑色'], '顏色');

// 獲取帶有類型的標籤
$post->tagsWithType('文章標籤');
$post->tagsWithType('顏色');

// 獲取帶有任意一個標籤的模型
Post::withAnyTags(['標籤1', '標籤2'])->get();

// 獲取帶有所有標籤的模型，需同時擁有所有
Post::withAllTags(['標籤1', '標籤2'])->get();

// 獲取不帶有指定標籤的模型
Post::withoutTags(['標籤1', '標籤2'])->get();

// 翻譯標籤
$tag = Tag::findOrCreate('我的標籤');
$tag->setTranslation('name', 'en', 'My Tag');
$tag->setTranslation('name', 'fr', 'Mon tag');
$tag->setTranslation('name', 'nl', 'Mijn tag');
$tag->save();

// 獲取翻譯
$tag->getTranslation('name'); //返回我的標籤名稱，使用當前語言
$tag->getTranslation('name', 'fr'); //返回 Mon tag (可選語言參數)

// 使用標籤類型
$tag = Tag::findOrCreate('標籤1', '文章標籤');
$tag = Tag::findOrCreate('tag_slug_1'); // slug 不會因語言設定而改變
$tag->slug; //返回 "tag_slug_1"
```

## 需求

此套件需要 Laravel 8 或更高版本、PHP 8 或更高版本，以及支援 MySQL 相容功能的資料庫。

## 安裝

您可以通過 Composer 安裝此套件：

```bash
composer require secretwebmaster/wncms-tags
```

套件將會自動註冊。

您可以使用以下指令發佈 migrtation 檔案以及 config 配置文件：

```bash
php artisan vendor:publish --provider="Wncms\Tags\TagsServiceProvider" --tag="tags-migrations"
```

發佈 migrtation 檔案後，您可以通過執行 `migrate` 來創建 `tags` 和 `taggables` 資料表：

```bash
php artisan migrate
```