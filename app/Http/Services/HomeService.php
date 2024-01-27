<?php

namespace App\Http\Services;

use App\Http\Resources\PostResource;
use App\Models\Group;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class HomeService
{
    public function postsIndex()
    {
        $userId = Auth::id();
        $posts = Post::query() // SELECT * FROM posts
        ->withCount('reactions') // SELECT COUNT(*) from reactions
        ->with([
            'comments' => function ($query) use ($userId) {
                $query->withCount('reactions'); // SELECT * FROM comments WHERE post_id IN (1, 2, 3...)
                // SELECT COUNT(*) from reactions
            },
            'reactions' => function ($query) use ($userId) {
                $query->where('user_id', $userId); // SELECT * from reactions WHERE user_id = ?
            }])
            ->latest()
            ->paginate(10);
        return PostResource::collection($posts);
    }

    public function groupsIndex()
    {
        $groups = Group::query()
            ->select(['groups.*', 'gu.status', 'gu.role'])
            ->join('group_users AS gu', 'gu.group_id', 'groups.id')
            ->where('gu.user_id', Auth::id())
            ->orderBy('gu.role')
            ->orderBy('name', 'desc')
            ->get();
        return $groups;
    }
}
