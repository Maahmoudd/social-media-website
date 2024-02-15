<?php

namespace App\Http\Services;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use App\Notifications\CommentCreated;
use App\Notifications\ReactionAddedOnComment;
use App\Notifications\ReactionAddedOnPost;
use Illuminate\Support\Facades\Auth;

class ReactionService
{
    public function makeReaction($request, $object): array
    {
        $data = $request->validated();
        $userId = Auth::id();
        $reaction = $object->reactions()->where('user_id', $userId)->first();
        if ($reaction) {
            $hasReaction = false;
            $reaction->delete();
        } else {
            $hasReaction = true;
            $objectType = get_class($object) === Comment::class ? Comment::class : Post::class;
            Reaction::create([
                'object_id' => $object->id,
                'object_type' => $objectType,
                'user_id' => $userId,
                'type' => $data['reaction']
            ]);
            if (!$object->isOwner($userId)) {
                $user = User::where('id', $userId)->first();
                if ($objectType === Comment::class) {
                    $object->user->notify(new ReactionAddedOnComment($object->post, $object, $user));
                }
                else $object->user->notify(new ReactionAddedOnPost($object, $user));
            }
        }
        $reactions = $object->reactions()->count();
        return compact('hasReaction' , 'reactions');
    }

}
