<?php

namespace App\Http\Services;

use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class CommentService
{
    public function createComment($request, $post)
    {
        $data = $request->validate([
            'comment' => ['required']
        ]);
        $comment = Comment::create([
            'post_id' => $post->id,
            'comment' => nl2br($data['comment']),
            'user_id' => Auth::id()
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
