<?php

namespace App\Http\Services;

use App\Http\Resources\PostResource;
use App\Models\Follower;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileService
{

    public function updateProfile($request)
    {
        $request->user()->fill($request->validated());
        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }
        $request->user()->save();
        return $request->user();
    }

    public function updateImages($request)
    {
        $data = $request->validated();
        $user = $request->user();
        $avatar = $data['avatar'] ?? null;
        /** @var \Illuminate\Http\UploadedFile $cover */
        $cover = $data['cover'] ?? null;

        $success = '';
        if ($cover) {
            if ($user->cover_path) {
                Storage::disk('public')->delete($user->cover_path);
            }
            $path = $cover->store('user-'.$user->id, 'public');
            $user->update(['cover_path' => $path]);
            $success = 'Your cover image was updated';
        }

        if ($avatar) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $path = $avatar->store('user-'.$user->id, 'public');
            $user->update(['avatar_path' => $path]);
            $success = 'Your avatar image was updated';
        }
        return $success;
    }

    public function deleteProfile($request)
    {
        $request->validated();
        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function profileFollower($user)
    {
        $isCurrentUserFollower = false;
        if (!Auth::guest()) {
            $isCurrentUserFollower = Follower::where('user_id', $user->id)->where('follower_id', Auth::id())->exists();
        }
        $followerCount = Follower::where('user_id', $user->id)->count();
        return compact('isCurrentUserFollower', 'followerCount');
    }

    public function followingPosts($request, $user)
    {
        $posts = Post::postsForTimeline(Auth::id())
            ->where('user_id', $user->id)
            ->paginate(10);

        $posts = PostResource::collection($posts);
        if ($request->wantsJson()) {
            return $posts;
        }

        $followers = User::query()
            ->select('users.*')
            ->join('followers AS f', 'f.follower_id', 'users.id')
            ->where('f.user_id', $user->id)
            ->get();

        $followings = User::query()
            ->select('users.*')
            ->join('followers AS f', 'f.user_id', 'users.id')
            ->where('f.follower_id', $user->id)
            ->get();

        return compact('posts', 'followers', 'followings');
    }
}
