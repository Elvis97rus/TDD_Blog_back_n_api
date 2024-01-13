<?php

namespace App\Http\Controllers;

use App\Http\Requests\Post\StoreRequest;
use App\Http\Requests\Post\UpdateRequest;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        return view('posts.index', compact('posts'));
    }

    public function show(Post $post)
    {
        return view('posts.show', compact('post'));
    }

    public function destroy(Post $post)
    {
        $post->delete();
    }

    public function update(UpdateRequest $request, Post $post)
    {
        $data = $request->validated();

        if (isset($data['image']) && !empty($data['image'])){
            $data['image'] = Storage::disk('local')->put('/images', $data['image']);
        }

        $post->update($data);
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();

        if (isset($data['image']) && !empty($data['image'])){
            $data['image'] = Storage::disk('local')->put('/images', $data['image']);
        }
//        dd($path);
        Post::create($data);
    }
}
