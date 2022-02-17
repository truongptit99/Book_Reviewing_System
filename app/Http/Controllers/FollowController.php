<?php

namespace App\Http\Controllers;

use App\Notifications\FollowNotification;
use App\Repositories\Follow\FollowRepositoryInterface;
use App\Repositories\User\UserRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowController extends Controller
{
    protected $followRepo;
    protected $userRepo;

    public function __construct(FollowRepositoryInterface $followRepo, UserRepositoryInterface $userRepo)
    {
        $this->followRepo = $followRepo;
        $this->userRepo = $userRepo;
    }

    public function store(Request $request)
    {
        $relationship = $this->followRepo
            ->getRelationshipWithTrashed(Auth::id(), $request->id);
        if ($relationship->isEmpty()) {
            $this->followRepo->create([
                'follower_id' => Auth::id(),
                'followed_id' => $request->id,
            ]);
        } else {
            $this->followRepo->restoreRelationship(Auth::id(), $request->id);
        }

        $followed_user = $this->userRepo->find($request->id);
        $followed_user->notify(new FollowNotification(Auth::user(), $followed_user));

        return json_encode(['statusCode' => 200]);
    }

    public function destroy($id)
    {
        $this->followRepo->deleteRelationship(Auth::id(), $id);

        return json_encode(['statusCode' => 200]);
    }
}
