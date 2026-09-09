<?php

namespace Wncms\Tags\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Wncms\Tags\Test\Models\Post;
use Wncms\Tags\Tests\TestCase;

class PostTest extends TestCase
{
    protected function createPost(string $title = 'Title', string $content = 'Content'): Post
    {
        return Post::create([
            'title' => $title,
            'content' => $content,
        ]);
    }

    #[Test]
    public function a_post_can_be_created(): void
    {
        $post = $this->createPost();

        $this->assertTrue($post->exists);
    }

    #[Test]
    public function queued_tags_are_attached_after_the_model_is_created(): void
    {
        $post = new Post([
            'title' => 'Queued tags',
            'content' => 'Content',
        ]);

        $post->tags = 'queued-tag';
        $post->save();

        $this->assertSame(['queued-tag'], $post->fresh()->tags->pluck('name')->all());
    }

    #[Test]
    public function allowed_tags_can_be_set_and_extended_without_duplicates(): void
    {
        $post = new Post();

        $post->setAllowedTags(['news']);
        $post->addAllowedTag(['featured', 'news']);
        $post->addAllowedTag('archived');

        $this->assertSame(['news', 'featured', 'archived'], array_values($post->getAllowedTags()));
        $this->assertSame(['news', 'featured', 'archived'], array_values((new Post())->getAllowedTags()));
    }

    #[Test]
    public function a_post_can_be_fetched(): void
    {
        $this->createPost();

        $post = Post::query()->first();

        $this->assertTrue($post?->exists);
    }
}
