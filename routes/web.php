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
Route::get('/g/{group:slug}', [GroupController::class, 'profile'])
    ->name('group.profile');

Route::middleware('auth')
    ->group(function () {
        Route::prefix('profile')
            ->as('profile.')
            ->controller(ProfileController::class)
            ->group(function () {
                Route::post('/update-images', 'updateImage')
                    ->name('updateImages');
                Route::patch('/', 'update')
                    ->name('update');
                Route::delete('/', 'destroy')
                    ->name('destroy');
        });

        Route::prefix('posts')
            ->as('post.')
            ->controller(PostController::class)
            ->group(function () {
                Route::post('/', 'store')
                    ->name('create');
                Route::put('/{post}', 'update')
                    ->name('update');
                Route::delete('/{post}', 'destroy')
                    ->name('destroy');
                Route::get('/download/{attachment}', 'downloadAttachment')
                    ->name('download');
                Route::post('/{post}/reaction', 'postReaction')
                    ->name('reaction');
                Route::post('/{post}/comment', 'createComment')
                    ->name('comment.create');
        });
        Route::prefix('comment')
            ->as('comment.')
            ->controller(PostController::class)
            ->group(function () {
                Route::delete('/{comment}', 'deleteComment')
                    ->name('delete');
                Route::put('//{comment}', 'updateComment')
                    ->name('update');
                Route::post('/{comment}/reaction', 'commentReaction')
                    ->name('reaction');
        });
        Route::prefix('group')
            ->as('group.')
            ->controller(GroupController::class)
            ->group(function (){
                Route::post('/', 'store')
                    ->name('create');
                Route::post('/update-images/{group:slug}', 'updateImage')
                    ->name('updateImages');
                Route::post('/invite/{group:slug}', 'inviteUsers')
                    ->name('inviteUsers');
                Route::get('/approve-invitation/{token}', 'approveInvitation')
                    ->name('approveInvitation');
                Route::post('/join/{group:slug}', 'join')
                    ->name('join');
        });

});

require __DIR__.'/auth.php';
