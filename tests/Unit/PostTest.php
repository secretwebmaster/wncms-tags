<?php

namespace Wncms\Tags\Tests\Unit;

use Orchestra\Testbench\TestCase;
use Wncms\Tags\Tag;
use Wncms\Tags\TagsServiceProvider;
use Wncms\Tags\Test\Models\Post;
use Illuminate\Config\Repository;
use Illuminate\Support\Facades\Config;

class PostTest extends TestCase
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

    /**
     * Get the application timezone.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return string|null
     */
    protected function getApplicationTimezone($app) 
    {
        return 'Asia/Taipei';
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
    public function a_post_can_be_created()
    {
        $post = $this->createPost();
        $this->assertTrue($post->exists);
    }

    /** @test */
    public function a_post_can_be_fetched()
    {
        $this->createPost();
        $post = Post::first();
        $this->assertTrue($post?->exists);
    }
}
