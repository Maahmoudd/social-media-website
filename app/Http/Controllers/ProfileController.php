<?php
namespace App\Http\Controllers;
use App\Http\Requests\DeleteProfileRequest;
use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UpdateProfileImagesRequest;
use App\Http\Resources\UserResource;
use App\Http\Services\ProfileService;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class ProfileController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    public function index(User $user)
    {
        return Inertia::render('Profile/View', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => session('status'),
            'success' => session('success'),
            'user' => new UserResource($user)
        ]);
    }
    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $this->profileService->updateProfile($request);
        return to_route('profile', $user)
            ->with('success', 'Your profile details were updated.');
    }
    /**
     * Delete the user's account.
     */
    public function destroy(DeleteProfileRequest $request): RedirectResponse
    {
        $this->profileService->deleteProfile($request);
        return Redirect::to('/');
    }
    public function updateImage(UpdateProfileImagesRequest $request)
    {
        $success = $this->profileService->updateImages($request);
        return back()->with('success', $success);
    }
}
