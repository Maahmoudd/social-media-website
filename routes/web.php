<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;


Route::get('/', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');
Route::get('/u/{user:username}', [ProfileController::class, 'index'])
    ->name('profile');

Route::middleware('auth')
    ->group(function () {
        Route::prefix('profile')->as('profile.')->group(function () {
            Route::post('/update-images', [ProfileController::class, 'updateImage'])
                ->name('updateImages');
            Route::patch('/', [ProfileController::class, 'update'])
                ->name('update');
            Route::delete('/', [ProfileController::class, 'destroy'])
                ->name('destroy');
        });

        Route::prefix('posts')->as('post.')->group(function () {
            Route::post('/', [PostController::class, 'store'])
                ->name('create');
            Route::put('/{post}', [PostController::class, 'update'])
                ->name('update');
            Route::delete('/{post}', [PostController::class, 'destroy'])
                ->name('destroy');
            Route::get('/download/{attachment}', [PostController::class, 'downloadAttachment'])
                ->name('download');
            Route::post('/{post}/reaction', [PostController::class, 'postReaction'])
                ->name('reaction');
            Route::post('/{post}/comment', [PostController::class, 'createComment'])
                ->name('comment.create');
        });
        Route::prefix('comment')->as('comment.')->group(function () {
            Route::delete('/{comment}', [PostController::class, 'deleteComment'])
                ->name('delete');
            Route::put('//{comment}', [PostController::class, 'updateComment'])
                ->name('update');
            Route::post('/{comment}/reaction', [PostController::class, 'commentReaction'])
                ->name('reaction');
        });
        Route::post('/group', [GroupController::class, 'store'])
            ->name('group.create');
});

require __DIR__.'/auth.php';
