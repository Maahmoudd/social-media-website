<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

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
        $id = Auth::id();

        if ($post->user_id !== $id) {
            return response("You don't have the permission to delete this post", 403);
        }

        $post->delete();
        return back();
    }
}
