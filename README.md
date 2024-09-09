# 新增標籤及標籤行為到 WNCMS

此套件為您的模型提供可標籤行為。安裝完成後，您只需將 `HasTags` trait 添加到 Eloquent 模型，即可使其可標籤。

我們不僅止於一般的標籤功能，WNCMS 標籤包含多種功能。內建支持[標籤翻譯]、[多重標籤類型]及[排序功能]。

以下以Post模組爲例：

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

// 創建帶有標籤的模型
$post = Post::create([
   'name' => '文章標題',
   'tags' => ['第一個標籤', '第二個標籤'], //如果標籤不存在，將自動創建
]);

// 添加標籤
$post->attachTag('第三個標籤');
$post->attachTag('第三個標籤','類型');
$post->attachTags(['第四個標籤', '第五個標籤']);
$post->attachTags(['第四個標籤','第五個標籤'],'類型');

// 移除標籤
$post->detachTag('第三個標籤');
$post->detachTag('第三個標籤','類型');
$post->detachTags(['第四個標籤', '第五個標籤']);
$post->detachTags(['第四個標籤', '第五個標籤'],'類型');

// 取得模型的所有標籤
$post->tags;

// 同步標籤
$post->syncTags(['第一個標籤', '第二個標籤']); //其他標籤將被移除

// 同步帶有類型的標籤
$post->syncTagsWithType(['類別 1', '類別 2'], '類別'); 
$post->syncTagsWithType(['主題 1', '主題 2'], '主題'); 

// 獲取帶有類型的標籤
$post->tagsWithType('類別'); 
$post->tagsWithType('主題'); 

// 獲取帶有任意標籤的模型
Post::withAnyTags(['第一個標籤', '第二個標籤'])->get();

// 獲取帶有所有標籤的模型
Post::withAllTags(['第一個標籤', '第二個標籤'])->get();

// 獲取不帶有任何標籤的模型
Post::withoutTags(['第一個標籤', '第二個標籤'])->get();

// 翻譯標籤
$tag = Tag::findOrCreate('我的標籤');
$tag->setTranslation('name', 'fr', 'mon tag');
$tag->setTranslation('name', 'nl', 'mijn tag');
$tag->save();

// 獲取翻譯
$tag->translate('name'); //返回我的標籤名稱
$tag->translate('name', 'fr'); //返回 mon tag (可選語言參數)

// 使用標籤類型
$tag = Tag::findOrCreate('標籤 1', '我的類型');

// 標籤擁有 slug
$tag = Tag::findOrCreate('另一個標籤');
$tag->slug; //返回 "another-tag"

// 標籤是可排序的
$tag = Tag::findOrCreate('我的標籤');
$tag->order_column; //返回 1
$tag2 = Tag::findOrCreate('另一個標籤');
$tag2->order_column; //返回 2

// 調整標籤的排序
$tag->swapOrder($anotherTag);

```

## 需求

此套件需要 Laravel 8 或更高版本、PHP 8 或更高版本，以及支援 `json` 欄位和 MySQL 相容功能的資料庫。


## 安裝

您可以通過 Composer 安裝此套件：

```bash
composer require secretwebmaster/wncms-tags
```

套件將會自動註冊。

您可以使用以下指令發佈遷移檔案：
```bash
php artisan vendor:publish --provider="Wncms\Tags\TagsServiceProvider" --tag="tags-migrations"
```

發佈遷移檔案後，您可以通過執行遷移來創建 `tags` 和 `taggables` 資料表：
```bash
php artisan migrate
```

您可以選擇性地發佈配置文件：
```bash
php artisan vendor:publish --provider="Wncms\Tags\TagsServiceProvider" --tag="tags-config"
```