<?php

namespace App\Http\Controllers;

use App\Repositories\Follow\FollowRepositoryInterface;
use App\Repositories\Review\ReviewRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    protected $userRepo;
    protected $followRepo;
    protected $reviewRepo;

    public function __construct(
        UserRepositoryInterface $userRepo,
        FollowRepositoryInterface $followRepo,
        ReviewRepositoryInterface $reviewRepo
    ) {
        $this->userRepo = $userRepo;
        $this->followRepo = $followRepo;
        $this->reviewRepo = $reviewRepo;
    }

    public function index()
    {
        $users = $this->userRepo->getUsersIsNotAdmin();

        return view('admin.users.index', compact('users'));
    }

    public function show($id)
    {
        $user = $this->userRepo->showUserProfile($id);
        $relationship = $this->followRepo->getRelationship(Auth::id(), $id);
        $reviews = $this->reviewRepo->getReviewsHistoryByUserId($id);

        return view('user.profile', compact('user', 'relationship', 'reviews'));
    }

    public function changeUserStatus($id)
    {
        $user = $this->userRepo->find($id);
        $status = $user->is_active;
        $this->userRepo->changeUserStatus($id, $status);
        if ($status == config('app.is_active')) {
            return redirect()->route('users.index')->with('success', __('messages.disable-user-success'));
        } else {
            return redirect()->route('users.index')->with('success', __('messages.enable-user-success'));
        }
    }
    
    public function destroy($id)
    {
        $this->userRepo->delete($id);

        return redirect()->route('users.index')->with('success', __('messages.delete-user-success'));
    }
}
