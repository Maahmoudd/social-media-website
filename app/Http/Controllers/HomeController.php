<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Http\Services\HomeService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HomeController extends Controller
{

    protected $homeService;

    public function __construct(HomeService $homeService)
    {
        $this->homeService = $homeService;
    }

    public function index(Request $request)
    {
        $posts = $this->homeService->index();
        if ($request->wantsJson()) {
            return $posts;
        }

        return Inertia::render('Home', [
            'posts' => $posts
        ]);
    }
}
