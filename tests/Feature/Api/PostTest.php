<?php

namespace Tests\Feature\Api;

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
        $this->withHeaders([
            'accept' => 'application/json'
        ]);
    }

    /** @test */
    public function a_post_can_be_deleted_by_auth_user_only()
    {
        $post = Post::factory()->create();
        $res = $this->delete('/api/posts/' . $post->id);

        $res->assertUnauthorized();
        $this->assertDatabaseCount('posts',1);
    }

    /** @test */
    public function a_post_can_be_deleted_by_auth_user()
    {
        $this->withoutExceptionHandling();

        $user = User::factory()->create();
        $post = Post::factory()->create();

        $res = $this->actingAs($user)->delete('/api/posts/' . $post->id);

        $res->assertOk();

        $this->assertDatabaseCount('posts',0);

        $res->assertJson([
            'message' => 'deleted'
        ]);
    }

    /** @test */
    public function response_for_route_posts_show_is_view_post_show_with_single_post()
    {
        $this->withoutExceptionHandling();

        $post = Post::factory()->create();

        $res = $this->get('/api/posts/' . $post->id);

        $res->assertJson([
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'image' => $post->image,
        ]);
    }

    /** @test */
    public function response_for_route_posts_index_is_view_post_index_with_posts()
    {
        $this->withoutExceptionHandling();

        $posts = Post::factory(10)->create();

        $res = $this->get('/api/posts');

        $res->assertOk();

        $json = $posts->map(function ($post){
            return [
                'id' => $post->id,
                'title' => $post->title,
                'description' => $post->description,
                'image' => $post->image,
            ];
        })->toArray();

        $res->assertJson($json);
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

        $res = $this->patch('/api/posts/' . $post->id, $data);

//        $res->assertOk();
        $res->assertJson([
            'id' => $post->id,
            'title' => $data['title'],
            'description' => $data['description'],
            'image' => 'images/' . $file->hashName(),
        ]);
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

        $res = $this->post('/api/posts', $data);

        $res->assertStatus(422);

        $res->assertInvalid('image');
        $res->assertJsonValidationErrors([
            'image' => 'The image field must be a file.'
        ]);
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

        $res = $this->post('/api/posts', $data);

//        dd($res->getContent());
//        //API validation error
        $res->assertStatus(422);
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


        $res = $this->post('/api/posts', $data);

        $this->assertDatabaseCount('posts', 1);

        $post = Post::first();

        $this->assertEquals($data['title'], $post->title);
        $this->assertEquals($data['description'], $post->description);
        $this->assertEquals('images/' . $file->hashName(), $post->image);

        Storage::disk('local')->assertExists($post->image);

        $res->assertJson([
            'id' => $post->id,
            'title' => $post->title,
            'description' => $post->description,
            'image' => $post->image,
            'created_at' => $post->created_at->format('Y-m-d'),
            'updated_at' => $post->updated_at->format('Y-m-d'),
        ]);
    }
}
