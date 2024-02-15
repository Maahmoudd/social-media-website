<?php

namespace App\Http\Services;

use App\Models\Comment;
use App\Notifications\CommentDeleted;
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
        $post = $comment->post;
        $id = Auth::id();
        if ($comment->isOwner($id) || $post->isOwner($id)) {
            $comment->delete();

            if (!$comment->isOwner($id)) {
                $comment->user->notify(new CommentDeleted($comment, $post));
            }

            return response('', 204);
        }
    }

    public function updateComment($request, $comment)
    {
        $data = $request->validated();
        return $comment->update([
            'comment' => nl2br($data['comment'])
        ]);
    }
}
