<?php

namespace App\Http\Services;

use App\Models\Follower;
use App\Notifications\FollowUser;
use Illuminate\Support\Facades\Auth;

class UserService
{
    public function follow($request, $user)
    {
        if ($request['follow']) {
            $message = 'You followed user "'.$user->name.'"';
            Follower::create([
                'user_id' => $user->id,
                'follower_id' => Auth::id()
            ]);
        } else {
            $message = 'You unfollowed user "'.$user->name.'"';
            Follower::query()
                ->where('user_id', $user->id)
                ->where('follower_id', Auth::id())
                ->delete();
        }

        $user->notify(new FollowUser(Auth::getUser(), $request['follow']));
        return $message;
    }
}
