<?php

namespace App\Http\Services;

use App\Models\Post;
use Illuminate\Support\Facades\Auth;

class HomeService
{
    public function index()
    {
        $userId = Auth::id();
        $posts = Post::query()
            ->withCount('reactions')
            ->withCount('comments')
            ->with([
                'comments' => function ($query) use ($userId) {
                    $query
                        ->whereNull('parent_id')
                        ->withCount('reactions')
                        ->withCount('comments')
                        ->with([
                            'reactions' => function ($query) use ($userId) {
                                $query->where('user_id', $userId);
                            }
                        ]);
                },
                'reactions' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                }])
            ->latest()
            ->paginate(20);
        return $posts;
    }
}
