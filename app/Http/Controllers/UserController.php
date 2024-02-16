<?php

namespace App\Http\Controllers;

use App\Http\Requests\FollowRequest;
use App\Http\Services\UserService;
use App\Models\User;

class UserController extends Controller
{
    protected $userService;
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function follow(FollowRequest $request, User $user)
    {
        $message = $this->userService->follow($request->validated(), $user);
        return back()->with('success', $message);
    }

}
