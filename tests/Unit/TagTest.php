<?php

namespace Wncms\Tags\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Wncms\Tags\Tag;
use Wncms\Tags\TagsServiceProvider;
use Wncms\Tags\Test\Models\Post;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Config;

class TagTest extends TestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array<int, class-string<\Illuminate\Support\ServiceProvider>>
     */
    protected function getPackageProviders($app)
    {
        return [
            TagsServiceProvider::class,
        ];
    }

    /**
     * Define environment setup.
     * Replacement of getEnvironmentSetup() method.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function defineEnvironment($app)
    {
        // Setup default database to use sqlite :memory:
        tap($app['config'], function (Repository $config) {
            $config->set('database.default', 'testbench');
            $config->set('database.connections.testbench', [
                'driver'   => 'sqlite',
                'database' => ':memory:',
                'prefix'   => '',
            ]);

            // Setup queue database connections.
            $config->set('queue.batching.database', 'testbench');
            $config->set('queue.failed.database', 'testbench');
        });
    }

    protected function setUp(): void
    {
        $this->afterApplicationCreated(function () {
            //test
            $this->loadMigrationsFrom(__DIR__ . '/../migrations');
            //vendor
            $this->loadMigrationsFrom(__DIR__ . '/../../vendor/secretwebmaster/wncms-translatable/migrations');
            //package
            $this->loadMigrationsFrom(__DIR__ . '/../../migrations');
        });

        $this->beforeApplicationDestroyed(function () {
            // Code before application destroyed.
        });

        parent::setUp();
    }
    
    protected function createPost($title = "Title", $content = "Content")
    {
        return Post::create([
            'title' => $title,
            'content' => $content,
        ]);
    }

    protected function createPosts($count, $withTags = false)
    {
        for($i = 0; $i < $count; $i++) {
            $post = $this->createPost("Post $i", "Content $i");
            if($withTags) {
                $post->attachTag("Tag $i");
            }
        }
        return Post::all();
    }

    /** @test */
    public function a_tag_can_be_created()
    {
        $tag = Tag::create([
            'name' => 'Laravel',
            'slug' => 'laravel',
        ]);

        $this->assertTrue($tag->exists);
    }

    /** @test */
    public function a_tag_can_be_created_through_post_model()
    {
        $post = $this->createPost();
        $post->attachTags(['Test tag 1']);
        $tag = $post->tags()->first();
        $this->assertTrue($tag->name == 'Test tag 1');
    }

    /** @test */
    public function multiple_tags_can_be_created_through_post_model()
    {
        $post = $this->createPost();
        $post->attachTags(['Test tag 1', 'Test tag 2', 'Test tag 3']);
        $this->assertEquals(3, $post->tags()->count());
    }

    /** @test */
    public function tag_relationship_can_be_loaded()
    {
        $post = $this->createPost();
        $post->attachTags(['Test tag 1']);
        $tag = $post->tags()->first();
        $this->assertTrue($post->tags->contains($tag));
    }

    /** @test */
    public function a_tag_can_be_translated()
    {
        $post = $this->createPost();
        $post->attachTags(['Test tag 1']);
        $tag = $post->tags()->first();
        $tag->setTranslation('name', 'zh_TW', '測試標籤 1');
        Config::set('app.locale', 'zh_TW');
        $this->assertEquals('測試標籤 1', $tag->name);
    }

    /** @test */
    public function model_can_be_filtered_by_tags_that_matches_all_items_in_array()
    {
        $post1 = $this->createPost(title: 'Post 1', content: 'This is the content of post 1');
        $post1->attachTags(['Tag1', 'Tag2', 'Tag3']);
        $post2 = $this->createPost(title: 'Post 2', content: 'This is the content of post 2');
        $post2->attachTags(['Tag1', 'Tag4', 'Tag5']);
        $posts = Post::withAllTags(['Tag1', 'Tag2'])->get();
        $this->assertEquals(1, $posts->count());
    }

    /** @test */
    public function model_can_be_filtered_by_tags_that_matches_any_item_in_array()
    {
        $post1 = $this->createPost(title: 'Post 1', content: 'This is the content of post 1');
        $post1->attachTags(['Tag1', 'Tag2', 'Tag3']);
        $post2 = $this->createPost(title: 'Post 2', content: 'This is the content of post 2');
        $post2->attachTags(['Tag1', 'Tag4', 'Tag5']);
        $posts = Post::withAllTags(['Tag1'])->get();
        $this->assertEquals(2, $posts->count());
    }

    /** @test */
    public function model_can_be_filtered_by_not_containing_specific_tags()
    {
        $post1 = $this->createPost(title: "Title 1");
        $post1->attachTags(['Tag1', 'Tag2', 'Tag3']);

        $post2 = $this->createPost(title: "Title 2");
        $post2->attachTags(['Tag1', 'Tag4', 'Tag5']);

        $post3 = $this->createPost(title: "Title 3");
        $post3->attachTags(['Tag1', 'Tag6', 'Tag7']);

        $posts = Post::withoutTags(['Tag2', 'Tag3'])->get();
        $this->assertEquals([2, 3], $posts->pluck('id')->toArray());
    }

    /** @test */
    public function it_can_filter_posts_by_all_tags_of_any_type()
    {
        // Create tags
        $tag1 = Tag::create(['name' => 'Tag 1', 'slug' => 'tag_1', 'type' => 'category']);
        $tag2 = Tag::create(['name' => 'Tag 2', 'slug' => 'tag_2', 'type' => 'category']);
        $tag3 = Tag::create(['name' => 'Tag 3', 'slug' => 'tag_3', 'type' => 'category']);

        // Create posts
        $post1 = Post::create(['title' => 'Post 1', 'content' => 'Content 1']);
        $post2 = Post::create(['title' => 'Post 2', 'content' => 'Content 2']);
        $post3 = Post::create(['title' => 'Post 3', 'content' => 'Content 3']);

        // Attach tags to posts
        $post1->attachTags([$tag1, $tag2]);
        $post2->attachTags([$tag1, $tag3]);
        $post3->attachTags([$tag2, $tag3]);

        // Test filtering with tags
        $filteredPosts = Post::withAllTagsOfAnyType([$tag1, $tag2])->get();

        // Assert that only the posts with both tag1 and tag2 are returned
        $this->assertTrue($filteredPosts->contains($post1));
        $this->assertFalse($filteredPosts->contains($post2)); // post2 has tag3, not tag2
        $this->assertFalse($filteredPosts->contains($post3)); // post3 has tag2, not tag1

        // Assert that the correct number of posts is returned
        $this->assertCount(1, $filteredPosts); // Only post1 should match
    }

    /** @test */
    public function a_tag_can_be_detached_from_post()
    {
        $post = $this->createPost();
        $post->attachTags(['Test tag 1']);
        $tag = $post->tags()->first();
        $post->detachTags($tag);
        $this->assertEquals(0, $post->tags()->count());
    }

    /** @test */
    public function multiple_tags_can_be_detached_from_post()
    {
        $post = $this->createPost();
        $post->attachTags(['Test tag 1', 'Test tag 2', 'Test tag 3']);
        $tags = $post->tags()->get();
        $post->detachTags($tags);
        $this->assertEquals(0, $post->tags()->count());
    }

    /** @test */
    public function multiple_tags_can_be_synced()
    {
        $post = $this->createPost();
        $post->attachTags(['Test tag 1', 'Test tag 2', 'Test tag 3']);
        $post->syncTags(['Test tag 4', 'Test tag 5']);
        $this->assertEquals(2, $post->tags()->count());
    }

    /** @test */
    public function multiple_tags_can_be_synced_with_type()
    {
        $post = $this->createPost();
        $post->attachTags(['Test tag 1', 'Test tag 2', 'Test tag 3']);
        $post->syncTags(['Test tag 4', 'Test tag 5'], 'post_category');
        $this->assertEquals(2, $post->tags()->count());
    }

    /** @test */
    public function string_can_be_converted_to_tag()
    {
        $tag = Tag::findFromString('Test tag 1');
        $this->assertNull($tag);
        $tag = Tag::findOrCreateFromString('Test tag 1');
        $this->assertTrue($tag->exists);
    }

    /** @test */
    public function string_can_be_converted_to_tag_in_any_type()
    {
        Tag::findOrCreate('Test tag 1', 'post_category');
        $tag = Tag::findFromStringOfAnyType('Test tag 1');
        $this->assertTrue($tag->count() > 0);
    }
}
