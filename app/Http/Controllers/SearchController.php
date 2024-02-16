<?php

namespace App\Http\Controllers;

use App\Http\Resources\GroupResource;
use App\Http\Resources\UserResource;
use App\Http\Services\SearchService;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected $searchService;
    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    public function search(Request $request, string $search = null)
    {
        if (!$search)
            return redirect(route('dashboard'));

        $searchResults = $this->searchService->search($search);
        if ($request->wantsJson()) {
            return $searchResults['posts'];
        }


        return inertia('Search', [
            'posts' => $searchResults['posts'],
            'search' => $search,
            'users' => UserResource::collection($searchResults['users']),
            'groups' => GroupResource::collection($searchResults['groups'])
        ]);
    }
}
