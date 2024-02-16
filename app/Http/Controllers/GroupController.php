<?php

namespace App\Http\Controllers;

use App\Http\Enums\GroupUserRole;
use App\Http\Requests\InviteUsersRequest;
use App\Http\Requests\StoreGroupRequest;
use App\Http\Requests\UpdateGroupImageRequest;
use App\Http\Requests\UserGroupRequest;
use App\Http\Resources\GroupResource;
use App\Http\Resources\GroupUserResource;
use App\Http\Resources\PostAttachmentResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Http\Services\GroupService;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Post;
use App\Models\PostAttachment;
use App\Models\User;
use App\Notifications\RoleChanged;
use App\Notifications\UserRemovedFromGroup;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    protected $groupService;

    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }

    public function profile(Request $request, Group $group)
    {
        $group->load('currentUserGroup');

        $userId = Auth::id();

        if ($group->hasApprovedUser($userId)) {
            $posts = Post::postsForTimeline($userId)
                ->where('group_id', $group->id)
                ->paginate(10);
            $posts = PostResource::collection($posts);
        } else {
            return Inertia::render('Group/View', [
                'success' => session('success'),
                'group' => new GroupResource($group),
                'posts' => null,
                'users' => [],
                'requests' => []
            ]);
        }

        if ($request->wantsJson()) {
            return $posts;
        }
        $users = User::query()->select(['users.*', 'gu.role', 'gu.status', 'gu.group_id'])
            ->join('group_users AS gu', 'gu.user_id', 'users.id')
            ->orderBy('users.name')
            ->where('group_id', $group->id)
            ->get();
        $requests = $group->pendingUsers()->orderBy('name')->get();
        $photos = PostAttachment::query()
            ->select('post_attachments.*')
            ->join('posts AS p', 'p.id', 'post_attachments.post_id')
            ->where('p.group_id', $group->id)
            ->where('mime', 'like', 'image/%')
            ->latest()
            ->get();
        return Inertia::render('Group/View', [
            'success' => session('success'),
            'group' => new GroupResource($group),
            'posts' => $posts,
            'users' => GroupUserResource::collection($users),
            'requests' => UserResource::collection($requests),
            'photos' => PostAttachmentResource::collection($photos)
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

    public function update(StoreGroupRequest $request, Group $group)
    {
        $group->update($request->validated());
        return back()->with('success', "Group was updated");
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

    public function approveRequest(UserGroupRequest $request, Group $group)
    {
        if (!$group->isAdmin(Auth::id())) {
            return response("You don't have permission to perform this action", 403);
        }
        $data = $request->validated();
        $response = $this->groupService->approveRequest($data, $group);
        return back()->with('success', 'User "'.$response['user']->name.'" was '.($response['approved'] ? 'approved' : 'rejected'));
    }

    public function removeUser(Request $request, Group $group)
    {
        if (!$group->isAdmin(Auth::id())) {
            return response("You don't have permission to perform this action", 403);
        }
        $this->groupService->removeUser($request, $group);
        return back();
    }

    public function changeRole(Request $request, Group $group)
    {
        if (!$group->isAdmin(Auth::id())) {
            return response("You don't have permission to perform this action", 403);
        }

        $data = $request->validate([
            'user_id' => ['required'],
            'role' => ['required', Rule::enum(GroupUserRole::class)]
        ]);

        $user_id = $data['user_id'];
        if ($group->isOwner($user_id)) {
            return response("You can't change role of the owner of the group", 403);
        }

        $groupUser = GroupUser::where('user_id', $user_id)
            ->where('group_id', $group->id)
            ->first();

        if ($groupUser) {
            $groupUser->role = $data['role'];
            $groupUser->save();

            $groupUser->user->notify(new RoleChanged($group, $data['role']));
        }
        return back();
    }
}
