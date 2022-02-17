<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Http\Requests\CreateReviewRequest;
use App\Http\Requests\EditReviewRequest;
use App\Repositories\Comment\CommentRepositoryInterface;
use App\Repositories\Like\LikeRepositoryInterface;
use App\Repositories\Review\ReviewRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    protected $reviewRepo;
    protected $commentRepo;
    protected $likeRepo;

    public function __construct(
        ReviewRepositoryInterface $reviewRepo,
        CommentRepositoryInterface $commentRepo,
        LikeRepositoryInterface $likeRepo
    ) {
        $this->reviewRepo = $reviewRepo;
        $this->commentRepo = $commentRepo;
        $this->likeRepo = $likeRepo;
    }

    public function store(CreateReviewRequest $request)
    {
        $data = $request->all();
        $data['display'] = config('app.display');
        $data['user_id'] = Auth::id();
        $this->reviewRepo->create($data);

        return redirect()->back()->with('success', __('messages.create-review-success'));
    }

    public function update(EditReviewRequest $request, Review $review)
    {
        $this->authorize('update', $review);
        $this->reviewRepo->update($review->id, $request->all());

        return redirect()->back()->with('success', __('messages.edit-review-success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Review  $review
     * @return \Illuminate\Http\Response
     */
    public function destroy(Review $review)
    {
        $this->authorize('delete', $review);
        $this->reviewRepo->delete($review->id);

        return redirect()->back()->with('success', __('messages.delete-review-success'));
    }

    public function hide($id)
    {
        $this->reviewRepo->hideReviewById($id);
        $this->commentRepo->hideCommentsByReviewId($id);

        return response()->json(['success' => __('messages.hide-review-success')]);
    }

    public function view($id)
    {
        $this->reviewRepo->showReviewById($id);
        $this->commentRepo->showCommentsByReviewId($id);

        return response()->json(['success' => __('messages.show-review-success')]);
    }

    public function rate($id)
    {
        $like = $this->likeRepo->getLikeOfUserForReview($id, Auth::id());
        if (count($like)) {
            $this->likeRepo->dislikeBookOrReview($id, 'App\Models\Review', Auth::id());

            return redirect()->back()->with('success', __('messages.un-rate-review-success'));
        } else {
            $this->likeRepo->create([
                'user_id' => Auth::id(),
                'likeable_type' => 'App\Models\Review',
                'likeable_id' => $id,
            ]);
        }

        return redirect()->back()->with('success', __('messages.rate-review-success'));
    }
}
