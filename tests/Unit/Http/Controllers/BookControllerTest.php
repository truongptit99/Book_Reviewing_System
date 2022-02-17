<?php

namespace Tests\Unit;

use App\Http\Controllers\BookController;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\Category;
use App\Models\Favorite;
use App\Models\Image;
use App\Models\Like;
use App\Models\Review;
use App\Models\User;
use App\Notifications\FavoriteBookNotification;
use App\Repositories\Book\BookRepository;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\Comment\CommentRepository;
use App\Repositories\Favorite\FavoriteRepository;
use App\Repositories\Image\ImageRepository;
use App\Repositories\Like\LikeRepository;
use App\Repositories\Review\ReviewRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Session;
use Mockery;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    protected $bookRepoMock;
    protected $categoryRepoMock;
    protected $imageRepoMock;
    protected $reviewRepoMock;
    protected $likeRepoMock;
    protected $favoriteRepoMock;
    protected $userRepoMock;
    protected $commentRepoMock;
    protected $bookController;

    public function setUp(): void
    {
        parent::setUp();
        $this->bookRepoMock = Mockery::mock(BookRepository::class);
        $this->categoryRepoMock = Mockery::mock(CategoryRepository::class);
        $this->imageRepoMock = Mockery::mock(ImageRepository::class);
        $this->reviewRepoMock = Mockery::mock(ReviewRepository::class);
        $this->likeRepoMock = Mockery::mock(LikeRepository::class);
        $this->favoriteRepoMock = Mockery::mock(FavoriteRepository::class);
        $this->userRepoMock = Mockery::mock(UserRepository::class);
        $this->commentRepoMock = Mockery::mock(CommentRepository::class);

        $this->bookController = new BookController(
            $this->bookRepoMock,
            $this->categoryRepoMock,
            $this->imageRepoMock,
            $this->reviewRepoMock,
            $this->likeRepoMock,
            $this->favoriteRepoMock,
            $this->userRepoMock,
            $this->commentRepoMock
        );
    }

    public function tearDown(): void
    {
        Mockery::close();
        $this->bookRepoMock = null;
        $this->categoryRepoMock = null;
        $this->imageRepoMock = null;
        $this->reviewRepoMock = null;
        $this->likeRepoMock = null;
        $this->favoriteRepoMock = null;
        $this->userRepoMock = null;
        $this->commentRepoMock = null;
        $this->bookController = null;
        parent::tearDown();
    }

    public function testIndex()
    {
        $books = Book::factory()->count(5)->make();

        $this->bookRepoMock
            ->shouldReceive('getAllBooksWithCategoriesAndImages')
            ->once()
            ->andReturn($books);

        $view = $this->bookController->index();

        $this->assertEquals('admin.books.index', $view->getName());
        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('books', $view->getData());
    }

    public function testCreate()
    {
        $categories = Category::factory()->count(5)->make();
        
        $this->categoryRepoMock
            ->shouldReceive('getAllSubcategoriesWithBooks')
            ->once()
            ->andReturn($categories);

        $view = $this->bookController->create();

        $this->assertEquals('admin.books.create', $view->getName());
        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('categories', $view->getData());
    }

    public function testStore()
    {
        $request = new StoreBookRequest([
            'category_id' => 2,
            'title' => 'a',
            'author' => 'a',
            'number_of_page' => 1,
            'published_date' => '2021-10-12',
            'image' => UploadedFile::fake()->image('a.jpg'),
        ]);
        $data = $request->all();
        $book = Book::factory()->make(["id" => 1]);

        $this->bookRepoMock
            ->shouldReceive('create')
            ->with($data)
            ->once()
            ->andReturn($book);

        $file = $data['image'];
        $image = [];
        $image['imageable_type'] = get_class($book);
        $image['imageable_id'] = $book->id;
        $image['path'] = $book->id . '_' . $file->getClientOriginalName();
        $this->imageRepoMock
            ->shouldReceive('create')
            ->with($image)
            ->once();

        $result = $this->bookController->store($request);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals(Session::get('success'), __('messages.add-book-success'));
    }

    public function testShow()
    {
        $book = Book::factory()->make(['id' => 1]);
        $reviews = Review::factory()->count(5)->make();
 
        $this->bookRepoMock
            ->shouldReceive('getBookWithRelationsById')
            ->with($book->id, ['category', 'image'])
            ->once();
        $this->reviewRepoMock
            ->shouldReceive('getReviewsWithUsersAndCommentsAndLikesByBookId')
            ->with($book->id)
            ->once()
            ->andReturn($reviews);

        $view = $this->bookController->show($book->id);

        $this->assertEquals('admin.books.show', $view->getName());
        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('book', $view->getData());
        $this->assertArrayHasKey('reviews', $view->getData());
    }

    public function testEdit()
    {
        $book = Book::factory()->make(['id' => 1]);
        $categories = Category::factory()->count(5)->make();

        $this->bookRepoMock
            ->shouldReceive('getBookWithRelationsById')
            ->with($book->id, ['category', 'image'])
            ->once();
        $this->categoryRepoMock
            ->shouldReceive('getAllSubcategoriesWithBooks')
            ->once()
            ->andReturn($categories);

        $view = $this->bookController->edit($book->id);

        $this->assertEquals('admin.books.edit', $view->getName());
        $this->assertIsArray($view->getData());
        $this->assertArrayHasKey('book', $view->getData());
        $this->assertArrayHasKey('categories', $view->getData());
    }

    public function testDestroy()
    {
        $book = Book::factory()->make(['id' => 1]);
        $userIds = [1, 2, 3, 4, 5];
        $user = User::factory()->make(['id' => 1]);

        $this->favoriteRepoMock
            ->shouldReceive('getUserIdsByFavoriteBookId')
            ->with($book->id)
            ->once()
            ->andReturn($userIds);
        $this->bookRepoMock
            ->shouldReceive('find')
            ->with($book->id)
            ->once()
            ->andReturn($book);
        $this->userRepoMock
            ->shouldReceive('find')
            ->andReturn($user);

        Notification::fake();
        $this->bookRepoMock
            ->shouldReceive('delete')
            ->with($book->id)
            ->once();

        $result = $this->bookController->destroy($book->id);

        Notification::assertSentTo($user, FavoriteBookNotification::class);
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals(Session::get('success'), __('messages.delete-book-success'));
    }

    public function testSearchByCategoryIfLoggedIn()
    {
        $category = Category::factory()->make(['id' => 1]);
        $books = Book::factory()->count(5)->make();
        $user = User::factory()->make(['id' => 1]);

        $this->categoryRepoMock
            ->shouldReceive('find')
            ->with($category->id)
            ->once()
            ->andReturn($category);
        $this->bookRepoMock
            ->shouldReceive('searchBooksByCategoryId')
            ->with($category->id)
            ->once()
            ->andReturn($books);
        $this->be($user);
        $this->likeRepoMock
            ->shouldReceive('getLikedBookIdsByUserId')
            ->with($user->id)
            ->once();
        $this->favoriteRepoMock
            ->shouldReceive('getFavoriteBookIdsByUserId')
            ->with($user->id)
            ->once();

        $view = $this->bookController->searchByCategory($category->id);
        $data = $view->getData();

        $this->assertEquals('user.search_book', $view->getName());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('category', $data);
        $this->assertArrayHasKey('books', $data);
        $this->assertArrayHasKey('likes', $data);
        $this->assertArrayHasKey('favorites', $data);
        $this->assertArrayHasKey('categoryChildren', $data);
    }

    public function testSearchByCategoryIfNotLoggedIn()
    {
        $category = Category::factory()->make(['id' => 1]);
        $books = Book::factory()->count(5)->make();

        $this->categoryRepoMock
            ->shouldReceive('find')
            ->with($category->id)
            ->once()
            ->andReturn($category);
        $this->bookRepoMock
            ->shouldReceive('searchBooksByCategoryId')
            ->with($category->id)
            ->once()
            ->andReturn($books);

        $view = $this->bookController->searchByCategory($category->id);
        $data = $view->getData();

        $this->assertEquals('user.search_book', $view->getName());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('category', $data);
        $this->assertArrayHasKey('books', $data);
        $this->assertArrayHasKey('categoryChildren', $data);
    }

    public function testGetDetailIfLoggedIn()
    {
        $book = Book::factory()->make(['id' => 1]);
        $reviews = Review::factory()->count(5)->make();
        $user = User::factory()->make(['id' => 1]);
        $like = Like::factory()->count(1)->make();
        $favorite = Favorite::factory()->count(1)->make();

        $this->bookRepoMock
            ->shouldReceive('getBookWithRelationsById')
            ->with($book->id, ['category', 'image', 'likes'])
            ->once()
            ->andReturn($book);
        $this->reviewRepoMock
            ->shouldReceive('getReviewsWithUsersAndCommentsAndLikesByBookId')
            ->with($book->id)
            ->once()
            ->andReturn($reviews);
        $this->be($user);
        $this->likeRepoMock
            ->shouldReceive('getLikeOfUserForBook')
            ->with($book->id, $user->id)
            ->once()
            ->andReturn($like);
        $this->favoriteRepoMock
            ->shouldReceive('getFavoriteOfUserForBook')
            ->with($book->id, $user->id)
            ->once()
            ->andReturn($favorite);

        $view = $this->bookController->getDetail($book->id);
        $data = $view->getData();

        $this->assertEquals('book-detail', $view->getName());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('book', $data);
        $this->assertArrayHasKey('likedBook', $data);
        $this->assertArrayHasKey('favoritedBook', $data);
        $this->assertArrayHasKey('reviews', $data);
        $this->assertArrayHasKey('totalReviewDisplay', $data);
        $this->assertArrayHasKey('avarageRating', $data);
    }

    public function testGetDetailIfNotLoggedIn()
    {
        $book = Book::factory()->make(['id' => 1]);
        $reviews = Review::factory()->count(5)->make();

        $this->bookRepoMock
            ->shouldReceive('getBookWithRelationsById')
            ->with($book->id, ['category', 'image', 'likes'])
            ->once()
            ->andReturn($book);
        $this->reviewRepoMock
            ->shouldReceive('getReviewsWithUsersAndCommentsAndLikesByBookId')
            ->with($book->id)
            ->once()
            ->andReturn($reviews);

        $view = $this->bookController->getDetail($book->id);
        $data = $view->getData();

        $this->assertEquals('book-detail', $view->getName());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('book', $data);
        $this->assertArrayHasKey('reviews', $data);
        $this->assertArrayHasKey('totalReviewDisplay', $data);
        $this->assertArrayHasKey('avarageRating', $data);
    }

    public function testSearchByTitleIfLoggedIn()
    {
        $request = new Request(['title' => 'a']);
        $books = Book::factory()->count(5)->make();
        $user = User::factory()->make(['id' => 1]);

        $this->bookRepoMock
            ->shouldReceive('searchBooksByTitle')
            ->with($request->title)
            ->once()
            ->andReturn($books);
        $this->be($user);
        $this->likeRepoMock
            ->shouldReceive('getLikedBookIdsByUserId')
            ->with($user->id)
            ->once();
        $this->favoriteRepoMock
            ->shouldReceive('getFavoriteBookIdsByUserId')
            ->with($user->id)
            ->once();
        
        $view = $this->bookController->searchByTitle($request);
        $data = $view->getData();

        $this->assertEquals('user.index', $view->getName());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('books', $data);
        $this->assertArrayHasKey('likes', $data);
        $this->assertArrayHasKey('favorites', $data);
        $this->assertArrayHasKey('categoryChildren', $data);
    }

    public function testSearchByTitleIfNotLoggedIn()
    {
        $request = new Request(['title' => 'a']);
        $books = Book::factory()->count(5)->make();

        $this->bookRepoMock
            ->shouldReceive('searchBooksByTitle')
            ->with($request->title)
            ->once()
            ->andReturn($books);
        
        $view = $this->bookController->searchByTitle($request);
        $data = $view->getData();

        $this->assertEquals('user.index', $view->getName());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('books', $data);
        $this->assertArrayHasKey('categoryChildren', $data);
    }

    public function testStatistic()
    {
        $this->likeRepoMock
            ->shouldReceive('getLikesStatistic')
            ->once();
        $this->reviewRepoMock
            ->shouldReceive('getReviewsStatistic')
            ->once();
        $this->commentRepoMock
            ->shouldReceive('getCommentsStatistic')
            ->once();

        $view = $this->bookController->statistic();
        $data = $view->getData();

        $this->assertEquals('admin.books.book_statistic', $view->getName());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('likes', $data);
        $this->assertArrayHasKey('reviews', $data);
        $this->assertArrayHasKey('comments', $data);
    }
}
