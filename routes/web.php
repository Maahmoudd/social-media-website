<?php
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
        Route::post('/profile/update-images', [ProfileController::class, 'updateImage'])
            ->name('profile.updateImages');
    //    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])
            ->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])
            ->name('profile.destroy');

        Route::post('/posts', [PostController::class, 'store'])
            ->name('post.create');
        Route::put('/posts/{post}', [PostController::class, 'update'])
            ->name('post.update');
        Route::delete('/posts/{post}', [PostController::class, 'destroy'])
            ->name('post.destroy');
        Route::get('/post/download/{attachment}', [PostController::class, 'downloadAttachment'])
            ->name('post.download');
        Route::post('/post/{post}/reaction', [PostController::class, 'postReaction'])
            ->name('post.reaction');

});

require __DIR__.'/auth.php';
