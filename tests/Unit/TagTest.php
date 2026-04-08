<?php

namespace Wncms\Tags\Tests\Unit;

use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Wncms\Tags\Tag;
use Wncms\Tags\Test\Models\Post;
use Wncms\Tags\Tests\TestCase;

class TagTest extends TestCase
{
    protected function createPost(string $title = 'Title', string $content = 'Content'): Post
    {
        return Post::create([
            'title' => $title,
            'content' => $content,
        ]);
    }

    #[Test]
    public function a_tag_can_be_created(): void
    {
        $tag = Tag::create([
            'name' => 'Laravel',
            'slug' => 'laravel',
        ]);

        $this->assertTrue($tag->exists);
    }

    #[Test]
    public function a_tag_can_be_created_through_post_model(): void
    {
        $post = $this->createPost();

        $post->attachTags(['Test tag 1']);

        $this->assertSame('Test tag 1', $post->tags()->first()?->name);
    }

    #[Test]
    public function multiple_tags_can_be_created_through_post_model(): void
    {
        $post = $this->createPost();

        $post->attachTags(['Test tag 1', 'Test tag 2', 'Test tag 3']);

        $this->assertCount(3, $post->tags()->get());
    }

    #[Test]
    public function tag_relationship_can_be_loaded(): void
    {
        $post = $this->createPost();
        $post->attachTags(['Test tag 1']);

        $tag = $post->tags()->first();

        $this->assertTrue($post->tags->contains($tag));
    }

    #[Test]
    public function a_tag_can_be_translated(): void
    {
        $post = $this->createPost();
        $post->attachTags(['Test tag 1']);

        $tag = $post->tags()->firstOrFail();
        $tag->setTranslation('name', 'zh_TW', '測試標籤 1');

        Config::set('app.locale', 'zh_TW');

        $this->assertSame('測試標籤 1', $tag->fresh()->name);
    }

    #[Test]
    public function a_translated_tag_can_be_found_by_the_translated_name(): void
    {
        $tag = Tag::findOrCreate('Original', 'keyword');
        $tag->setTranslation('name', 'zh_TW', '關鍵字');

        $found = Tag::findFromString('關鍵字', 'keyword', 'zh_TW');

        $this->assertNotNull($found);
        $this->assertSame($tag->id, $found->id);
    }

    #[Test]
    public function keyword_typed_tags_can_be_filtered_with_any_tags_scope(): void
    {
        $post1 = $this->createPost(title: 'Post 1', content: 'This is the content of post 1');
        $post1->attachTags(['Laravel'], 'keyword');

        $post2 = $this->createPost(title: 'Post 2', content: 'This is the content of post 2');
        $post2->attachTags(['PHP'], 'keyword');

        $posts = Post::withAnyTags(['Laravel'], 'keyword')->get();

        $this->assertSame([$post1->id], $posts->pluck('id')->all());
    }

    #[Test]
    public function model_can_be_filtered_by_tags_that_matches_all_items_in_array(): void
    {
        $post1 = $this->createPost(title: 'Post 1', content: 'This is the content of post 1');
        $post1->attachTags(['Tag1', 'Tag2', 'Tag3']);

        $post2 = $this->createPost(title: 'Post 2', content: 'This is the content of post 2');
        $post2->attachTags(['Tag1', 'Tag4', 'Tag5']);

        $posts = Post::withAllTags(['Tag1', 'Tag2'])->get();

        $this->assertCount(1, $posts);
        $this->assertSame([$post1->id], $posts->pluck('id')->all());
    }

    #[Test]
    public function model_can_be_filtered_by_tags_that_matches_any_item_in_array(): void
    {
        $post1 = $this->createPost(title: 'Post 1', content: 'This is the content of post 1');
        $post1->attachTags(['Tag1', 'Tag2', 'Tag3']);

        $post2 = $this->createPost(title: 'Post 2', content: 'This is the content of post 2');
        $post2->attachTags(['Tag1', 'Tag4', 'Tag5']);

        $posts = Post::withAnyTags(['Tag1'])->get();

        $this->assertCount(2, $posts);
    }

    #[Test]
    public function model_can_be_filtered_by_not_containing_specific_tags(): void
    {
        $post1 = $this->createPost(title: 'Title 1');
        $post1->attachTags(['Tag1', 'Tag2', 'Tag3']);

        $post2 = $this->createPost(title: 'Title 2');
        $post2->attachTags(['Tag1', 'Tag4', 'Tag5']);

        $post3 = $this->createPost(title: 'Title 3');
        $post3->attachTags(['Tag1', 'Tag6', 'Tag7']);

        $posts = Post::withoutTags(['Tag2', 'Tag3'])->get();

        $this->assertSame([$post2->id, $post3->id], $posts->pluck('id')->all());
    }

    #[Test]
    public function it_can_filter_posts_by_all_tags_of_any_type(): void
    {
        $tag1 = Tag::create(['name' => 'Tag 1', 'slug' => 'tag_1', 'type' => 'category']);
        $tag2 = Tag::create(['name' => 'Tag 2', 'slug' => 'tag_2', 'type' => 'keyword']);
        $tag3 = Tag::create(['name' => 'Tag 3', 'slug' => 'tag_3', 'type' => 'keyword']);

        $post1 = Post::create(['title' => 'Post 1', 'content' => 'Content 1']);
        $post2 = Post::create(['title' => 'Post 2', 'content' => 'Content 2']);
        $post3 = Post::create(['title' => 'Post 3', 'content' => 'Content 3']);

        $post1->attachTags([$tag1, $tag2]);
        $post2->attachTags([$tag1, $tag3]);
        $post3->attachTags([$tag2, $tag3]);

        $filteredPosts = Post::withAllTagsOfAnyType([$tag1, $tag2])->get();

        $this->assertTrue($filteredPosts->contains($post1));
        $this->assertFalse($filteredPosts->contains($post2));
        $this->assertFalse($filteredPosts->contains($post3));
        $this->assertCount(1, $filteredPosts);
    }

    #[Test]
    public function a_tag_can_be_detached_from_post(): void
    {
        $post = $this->createPost();
        $post->attachTags(['Test tag 1']);

        $tag = $post->tags()->firstOrFail();
        $post->detachTags($tag);

        $this->assertCount(0, $post->fresh()->tags);
    }

    #[Test]
    public function multiple_tags_can_be_detached_from_post(): void
    {
        $post = $this->createPost();
        $post->attachTags(['Test tag 1', 'Test tag 2', 'Test tag 3']);

        $tags = $post->tags()->get();
        $post->detachTags($tags);

        $this->assertCount(0, $post->fresh()->tags);
    }

    #[Test]
    public function multiple_tags_can_be_synced(): void
    {
        $post = $this->createPost();
        $post->attachTags(['Test tag 1', 'Test tag 2', 'Test tag 3']);

        $post->syncTags(['Test tag 4', 'Test tag 5']);

        $this->assertSame(['Test tag 4', 'Test tag 5'], $post->fresh()->tags->pluck('name')->all());
    }

    #[Test]
    public function multiple_tags_can_be_synced_with_type(): void
    {
        $post = $this->createPost();
        $post->attachTags(['Test tag 1', 'Test tag 2'], 'post_tag');
        $post->attachTags(['Test tag 3'], 'post_category');

        $post->syncTagsWithType(['Test tag 4', 'Test tag 5'], 'post_category');

        $this->assertSame(['Test tag 1', 'Test tag 2', 'Test tag 4', 'Test tag 5'], $post->fresh()->tags->pluck('name')->sort()->values()->all());
        $this->assertSame(['Test tag 4', 'Test tag 5'], $post->fresh()->tagsWithType('post_category')->pluck('name')->sort()->values()->all());
    }
}
