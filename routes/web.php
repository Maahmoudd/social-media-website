<?php

use App\Http\Controllers\GroupController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\UserController;
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

        Route::prefix('user')
            ->as('user.')
            ->controller(UserController::class)
            ->group(function (){
               Route::post('/follow/{user}', 'follow')
                   ->name('follow');
            });

        Route::prefix('posts')
            ->as('post.')
            ->controller(PostController::class)
            ->group(function () {
                Route::get('/{post}', 'view')
                    ->name('view');
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
                Route::post('/ai-post', 'aiPostContent')
                    ->name('aiContent');
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
                Route::put('/{group:slug}', 'update')
                    ->name('update');
                Route::post('/update-images/{group:slug}', 'updateImage')
                    ->name('updateImages');
                Route::post('/invite/{group:slug}', 'inviteUsers')
                    ->name('inviteUsers');
                Route::get('/approve-invitation/{token}', 'approveInvitation')
                    ->name('approveInvitation');
                Route::post('/join/{group:slug}', 'join')
                    ->name('join');
                Route::post('/approve-request/{group:slug}','approveRequest')
                    ->name('approveRequest');
                Route::post('/change-role/{group:slug}', 'changeRole')
                    ->name('changeRole');
                Route::delete('/remove-user/{group:slug}', 'removeUser')
                    ->name('removeUser');
        });

        Route::get('/search/{search?}', [SearchController::class, 'search'])
            ->name('search');

});

require __DIR__.'/auth.php';
