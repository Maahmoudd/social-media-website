<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateCommentRequest;
use App\Http\Requests\ReactionRequest;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\CommentResource;
use App\Http\Services\CommentService;
use App\Http\Services\PostService;
use App\Http\Services\ReactionService;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{

    protected $postService, $reactionService, $commentService;

    public function __construct(PostService $postService,
                                ReactionService $reactionService,
                                CommentService $commentService)
    {
        $this->postService = $postService;
        $this->reactionService = $reactionService;
        $this->commentService = $commentService;
    }

    public function store(StorePostRequest $request)
    {
        $this->postService->storePost($request);
        return back();
    }

    public function view(Post $post)
    {
        $post = $this->postService->viewPost($post);
        return inertia('Post/View', [
            'post' => $post
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $this->postService->updatePost($request, $post);
        return back();
    }

    public function destroy(Post $post)
    {
        $deleted = $this->postService->deletePost($post);
        if (!$deleted){
            return response("You don't have permission to delete this post", 403);
        }
    }

    public function downloadAttachment(PostAttachment $attachment)
    {
        // TODO check if user has permission to download that attachment

        return response()
            ->download(Storage::disk('public')
                ->path($attachment->path), $attachment->name);
    }

    public function postReaction(ReactionRequest $request, Post $post)
    {
        $reaction = $this->reactionService->makeReaction($request, $post);
        return response([
            'num_of_reactions' => $reaction['reactions'],
            'current_user_has_reaction' => $reaction['hasReaction']
        ]);
    }

    public function createComment(CreateCommentRequest $request, Post $post)
    {
        $comment = $this->commentService->createComment($request->validated(), $post);
        return response(new CommentResource($comment), 201);
    }

    public function deleteComment(Comment $comment)
    {
        $this->commentService->deleteComment($comment);
        return response("You don't have permission to delete this comment.", 403);
    }

    public function updateComment(UpdateCommentRequest $request, Comment $comment)
    {
        $comment = $this->commentService->updateComment($request, $comment);
        return new CommentResource($comment);
    }

    public function commentReaction(ReactionRequest $request, Comment $comment)
    {
        $reaction = $this->reactionService->makeReaction($request, $comment);
        return response([
            'num_of_reactions' => $reaction['reactions'],
            'current_user_has_reaction' => $reaction['hasReaction']
        ]);
    }

    public function aiPostContent(Request $request)
    {
        $result = $this->postService->aiPost($request);
        return response([
            'content' => $result->choices[0]->message->content
        ]);
    }
}
