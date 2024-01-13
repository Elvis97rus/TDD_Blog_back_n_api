<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Post\StoreRequest;
use App\Http\Requests\Api\Post\UpdateRequest;
use App\Http\Resources\Post\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        return PostResource::collection($posts)->resolve();
    }

    public function show(Post $post)
    {
        return PostResource::make($post)->resolve();
    }

    public function destroy(Post $post)
    {
        $post->delete();

        return response()->json([
            'message' => 'deleted'
        ]);
    }

    public function update(UpdateRequest $request, Post $post)
    {
        $data = $request->validated();

        if (isset($data['image']) && !empty($data['image'])){
            $data['image'] = Storage::disk('local')->put('/images', $data['image']);
        }

        $post->update($data);
        return PostResource::make($post)->resolve();
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        if (isset($data['image']) && !empty($data['image'])){
            $data['image'] = Storage::disk('local')->put('/images', $data['image']);
        }
//        dd($path);
        $post = Post::create($data);

        return PostResource::make($post)->resolve();
    }
}
