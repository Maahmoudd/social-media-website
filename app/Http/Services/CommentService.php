<?php

namespace App\Http\Services;

use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentService
{
    public function createComment($request, $post)
    {

        $comment = Comment::create([
            'post_id' => $post->id,
            'comment' => nl2br($request['comment']),
            'user_id' => Auth::id(),
            'parent_id' => $request['parent_id'] ?: null
        ]);
        return $comment;
    }

    public function deleteComment($comment)
    {
        return $comment->delete();
    }

    public function updateComment($request, $comment)
    {
        $data = $request->validated();
        return $comment->update([
            'comment' => nl2br($data['comment'])
        ]);
    }
}
