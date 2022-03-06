<?php

namespace App\Repositories\Like;

use App\Models\Like;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class LikeRepository extends BaseRepository implements LikeRepositoryInterface
{
    public function getModel()
    {
        return Like::class;
    }

    public function dislikeBookOrReview($likeable_id, $likeable_type, $user_id)
    {
        return $this->model
            ->where('user_id', $user_id)
            ->where('likeable_id', $likeable_id)
            ->where('likeable_type', $likeable_type)
            ->delete();
    }

    public function getLikedBookIdsByUserId($user_id)
    {
        return $this->model->where('user_id', $user_id)
            ->where('likeable_type', 'App\Models\Book')
            ->pluck('likeable_id')
            ->toArray();
    }

    public function getLikeOfUserForBook($book_id, $user_id)
    {
        return $this->model->where('user_id', $user_id)
            ->where('likeable_type', 'App\Models\Book')
            ->where('likeable_id', $book_id)
            ->get();
    }

    public function getLikeOfUserForReview($review_id, $user_id)
    {
        return $this->model->where('user_id', $user_id)
            ->where('likeable_type', 'App\Models\Review')
            ->where('likeable_id', $review_id)
            ->get();
    }

    public function getLikesStatistic()
    {
        return $this->model
            ->select(DB::raw('month(created_at) as month, count(*) as total_like'))
            ->where(DB::raw('year(created_at)'), 2021)
            ->where('likeable_type', 'App\Models\Book')
            ->groupBy(DB::raw('month(created_at)'))
            ->get();
    }
}
