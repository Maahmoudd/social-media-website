<?php

namespace App\Http\Services;

use App\Http\Resources\PostResource;
use App\Models\Group;
use App\Models\Post;
use App\Models\User;

class SearchService
{
    public function search($search)
    {
        $users = User::query()
            ->where('name', 'like', "%$search%")
            ->orWhere('username', 'like', "%$search%")
            ->latest()
            ->get();

        $groups = Group::query()
            ->where('name', 'like', "%$search%")
            ->orWhere('about', 'like', "%$search%")
            ->latest()
            ->get();

        $posts = Post::query()
            ->where('body', 'like', "%$search%")
            ->latest()
            ->paginate(20);

        $posts = PostResource::collection($posts);

        return compact('users', 'groups', 'posts');
    }


}
