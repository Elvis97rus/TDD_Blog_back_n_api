<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }


    /** @test */
    public function a_post_can_be_deleted_by_auth_user_only()
    {
        $post = Post::factory()->create();
        $res = $this->delete('/posts/' . $post->id);

        $res->assertRedirect();
        $this->assertDatabaseCount('posts',1);
    }

    /** @test */
    public function a_post_can_be_deleted_by_auth_user()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();

        $post = Post::factory()->create();

        $res = $this->actingAs($user)->delete('/posts/' . $post->id);

        $res->assertOk();

        $this->assertDatabaseCount('posts',0);
    }

    /** @test */
    public function response_for_route_posts_show_is_view_post_show_with_single_post()
    {
        $this->withoutExceptionHandling();

        $post = Post::factory()->create();

        $res = $this->get('/posts/' . $post->id);

        $res->assertViewIs('posts.show');

        $res->assertSeeText('SHOW');
        $res->assertSeeText($post->title);
        $res->assertSeeText($post->description);
    }

    /** @test */
    public function response_for_route_posts_index_is_view_post_index_with_posts()
    {
        $this->withoutExceptionHandling();

        $posts = Post::factory(10)->create();

        $res = $this->get('/posts');

        $res->assertViewIs('posts.index');

        $res->assertSeeText('TEST');

        $titles = $posts->pluck('title')->toArray();
//        dd($titles);
        $res->assertSeeText($titles);
    }

    /** @test */
    public function a_post_can_be_updated()
    {
        $this->withoutExceptionHandling();

        $post = Post::factory()->create();
        $file = File::create('sample.jpg');

        $data = [
            'title' => 'Test edited',
            'description' => 'some description',
            'image' => $file
        ];

        $res = $this->patch('/posts/' . $post->id, $data);

        $res->assertOk();

        $updatedPost = Post::first();
        $this->assertEquals($data['title'], $updatedPost->title);
        $this->assertEquals($data['description'], $updatedPost->description);
        $this->assertEquals('images/' . $file->hashName(), $updatedPost->image);

        $this->assertEquals($post->id, $updatedPost->id);
    }

    /** @test */
    public function attribute_image_is_file_for_storing_post()
    {
//        $this->withoutExceptionHandling();

        $data = [
            'title' => 'Test Tielte',
            'description' => 'some description',
            'image' => 'sdfdsdf',
        ];

        $res = $this->post('/posts', $data);

        $res->assertRedirect();

        $res->assertInvalid('image');
    }

    /** @test */
    public function attribute_title_is_required_for_storing_post()
    {
//        $this->withoutExceptionHandling();

        $data = [
            'title' => '',
            'description' => 'some description',
            'image' => '',
        ];

        $res = $this->post('/posts', $data);

        $res->assertRedirect();
        $res->assertInvalid('title');
    }


/** @test */
    public function a_post_can_be_stored()
    {
        $file = File::create('img.jpg');

        $this->withoutExceptionHandling();
        $data = [
            'title' => 'some title',
            'description' => 'some description',
            'image' => $file,
        ];


        $res = $this->post('/posts', $data);
//        $this->
        $res->assertOk();

        $this->assertDatabaseCount('posts', 1);

        $post = Post::first();

        $this->assertEquals($data['title'], $post->title);
        $this->assertEquals($data['description'], $post->description);
        $this->assertEquals('images/' . $file->hashName(), $post->image);

        Storage::disk('local')->assertExists($post->image);
    }
}
