<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;

class PostController extends Controller
{


    public function store(StorePostRequest $request)
    {
        $data = $request->validated();

        Post::create($data);

        return back();
    }


    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update($request->validated());
        return back();
    }


    public function destroy(Post $post)
    {
        //
    }
}
