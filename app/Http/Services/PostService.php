<?php

namespace App\Http\Services;

use App\Models\Post;
use App\Models\PostAttachment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PostService
{
    public function storePost($request)
    {
        $data = $request->validated();
        $user = $request->user();
        DB::beginTransaction();
        $allFilePaths = [];
        try {
            $post = Post::create($data);
            /** @var \Illuminate\Http\UploadedFile[] $files */
            $files = $data['attachments'] ?? [];
            foreach ($files as $file) {
                $path = $file->store('attachments/' . $post->id, 'public');
                $allFilePaths[] = $path;
                PostAttachment::create([
                    'post_id' => $post->id,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'created_by' => $user->id
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            foreach ($allFilePaths as $path) {
                Storage::disk('public')->delete($path);
            }
            DB::rollBack();
            throw $e;
        }
    }

    public function updatePost($request, $post)
    {
        $user = $request->user();
        DB::beginTransaction();
        $allFilePaths = [];
        try {
            $data = $request->validated();
            $post->update($data);
            $deleted_ids = $data['deleted_file_ids'] ?? []; // 1, 2, 3, 4
            $attachments = PostAttachment::query()
                ->where('post_id', $post->id)
                ->whereIn('id', $deleted_ids)
                ->get();
            foreach ($attachments as $attachment) {
                $attachment->delete();
            }
            /** @var \Illuminate\Http\UploadedFile[] $files */
            $files = $data['attachments'] ?? [];
            foreach ($files as $file) {
                $path = $file->store('attachments/' . $post->id, 'public');
                $allFilePaths[] = $path;
                PostAttachment::create([
                    'post_id' => $post->id,
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'mime' => $file->getMimeType(),
                    'size' => $file->getSize(),
                    'created_by' => $user->id
                ]);
            }
            DB::commit();
        } catch (\Exception $e) {
            foreach ($allFilePaths as $path) {
                Storage::disk('public')->delete($path);
            }
            DB::rollBack();
            throw $e;
        }
    }

    public function deletePost($post)
    {
        $id = Auth::id();
        if ($post->user_id !== $id) {
            return response("You don't have permission to delete this post", 403);
        }
        return $post->delete();
    }
}