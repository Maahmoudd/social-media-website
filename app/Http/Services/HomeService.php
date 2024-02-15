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
        $posts = Post::postsForTimeline($userId)->paginate(10);
        return PostResource::collection($posts);
    }

    public function groupsIndex()
    {
        $groups = Group::query()
            ->with('currentUserGroup')
            ->select(['groups.*'])
            ->join('group_users AS gu', 'gu.group_id', 'groups.id')
            ->where('gu.user_id', Auth::id())
            ->orderBy('gu.role')
            ->orderBy('name', 'desc')
            ->get();
        return $groups;
    }
}
