<?php

namespace App\Repositories\Book;

use App\Models\Book;
use App\Repositories\BaseRepository;

class BookRepository extends BaseRepository implements BookRepositoryInterface
{
    public function getModel()
    {
        return Book::class;
    }

    public function getAllBooksWithCategoriesAndImages()
    {
        return $this->model->with(['category', 'image'])
            ->orderBy('updated_at', 'DESC')
            ->paginate(config('app.paginate'));
    }

    public function getBookWithRelationsById($id, $relations)
    {
        return $this->model->with($relations)->findOrFail($id);
    }

    public function prepareGetAllBooks()
    {
        return $this->model->with(['image', 'likes', 'reviews.user'])
            ->withCount('likes as total_like')
            ->withCount('reviews as total_review')
            ->withSum('reviews as total_rate', 'rate');
    }

    public function getAllBooksWithImagesAndLikesAndRates()
    {
        return $this->prepareGetAllBooks()->paginate(config('app.paginate_opt2'));
    }

    public function searchBooksByCategoryId($id)
    {
        return $this->prepareGetAllBooks()
            ->where('category_id', $id)
            ->paginate(config('app.paginate_opt2'));
    }

    public function searchBooksByTitle($title)
    {
        return $this->prepareGetAllBooks()
            ->where('title', 'like', '%' . $title . '%')
            ->paginate(config('app.paginate_opt2'));
    }

    public function getFavoriteBooksByUserId($user_id)
    {
        return $this->prepareGetAllBooks()
            ->join('favorites', 'favorites.book_id', 'books.id')
            ->where('user_id', $user_id)
            ->paginate(config('app.paginate_opt2'));
    }
}
