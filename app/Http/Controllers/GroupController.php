<?php

namespace App\Http\Controllers;

use App\Http\Requests\InviteUsersRequest;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupImageRequest;
use App\Http\Requests\UpdateGroupRequest;
use App\Http\Resources\GroupResource;
use App\Http\Services\GroupService;
use App\Models\Group;
use Inertia\Inertia;

class GroupController extends Controller
{
    protected $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    public function profile(Group $group)
    {
        $group->load('currentUserGroup');
        return Inertia::render('Group/View', [
            'success' => session('success'),
            'group' => new GroupResource($group)
        ]);
    }

    public function store(StoreGroupRequest $request)
    {
        $group = $this->groupService->createGroup($request);
        return response(new GroupResource($group), 201);
    }

    public function show(Group $group)
    {
        //
    }

    public function update(UpdateGroupRequest $request, Group $group)
    {
        //
    }

    public function destroy(Group $group)
    {
        //
    }

    public function updateImage(UpdateGroupImageRequest $request, Group $group)
    {
        $success = $this->groupService->updateGroupImage($request->validated(), $group);
        return back()->with('success', $success);
    }

    public function inviteUsers(InviteUsersRequest $request, Group $group)
    {
        $this->groupService->inviteUsers($request->validated(), $group, $request->user, $request->groupUser);
        return back()->with('success', 'User was invited to join to group');
    }

    public function approveInvitation(string $token)
    {
        $groupUser = $this->groupService->approveInvitation($token);
        return redirect(route('group.profile', $groupUser->group))
            ->with('success', 'You accepted to join to group "'.$groupUser->group->name.'"');
    }

    public function join(Group $group)
    {
        $successMessage = $this->groupService->joinGroup($group);
        return back()->with('success', $successMessage);
    }
}
