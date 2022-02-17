<?php

namespace Tests\Unit;

use App\Http\Controllers\HomeController;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use App\Repositories\Book\BookRepository;
use App\Repositories\Category\CategoryRepository;
use App\Repositories\Favorite\FavoriteRepository;
use App\Repositories\Like\LikeRepository;
use Illuminate\Http\RedirectResponse;
use Mockery;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    protected $bookRepoMock;
    protected $categoryRepoMock;
    protected $likeRepoMock;
    protected $favoriteRepoMock;
    protected $homeController;

    public function setUp(): void
    {
        parent::setUp();
        $this->bookRepoMock = Mockery::mock(BookRepository::class);
        $this->categoryRepoMock = Mockery::mock(CategoryRepository::class);
        $this->likeRepoMock = Mockery::mock(LikeRepository::class);
        $this->favoriteRepoMock = Mockery::mock(FavoriteRepository::class);
        $this->homeController = new HomeController(
            $this->bookRepoMock,
            $this->categoryRepoMock,
            $this->likeRepoMock,
            $this->favoriteRepoMock
        );
    }

    public function tearDown(): void
    {
        Mockery::close();
        $this->bookRepoMock = null;
        $this->categoryRepoMock = null;
        $this->likeRepoMock = null;
        $this->favoriteRepoMock = null;
        $this->homeController = null;
        parent::tearDown();
    }

    public function testIndexIfLoggedIn()
    {
        $books = Book::factory()->count(5)->make();
        $categoryChildren = Category::factory()->count(5)->make();
        $user = User::factory()->make(['id' => 1]);

        $this->bookRepoMock
            ->shouldReceive('getAllBooksWithImagesAndLikesAndRates')
            ->once()
            ->andReturn($books);
        $this->categoryRepoMock
            ->shouldReceive('getAllSubcategoriesWithBooks')
            ->once()
            ->andReturn($categoryChildren);
        $this->be($user);
        $this->likeRepoMock
            ->shouldReceive('getLikedBookIdsByUserId')
            ->with($user->id)
            ->once();
        $this->favoriteRepoMock
            ->shouldReceive('getFavoriteBookIdsByUserId')
            ->with($user->id)
            ->once();

        $view = $this->homeController->index();
        $data = $view->getData();

        $this->assertEquals('user.index', $view->getName());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('books', $data);
        $this->assertArrayHasKey('likes', $data);
        $this->assertArrayHasKey('favorites', $data);
        $this->assertArrayHasKey('categoryChildren', $data);
    }

    public function testIndexIfNotLoggedIn()
    {
        $books = Book::factory()->count(5)->make();
        $categoryChildren = Category::factory()->count(5)->make();

        $this->bookRepoMock
            ->shouldReceive('getAllBooksWithImagesAndLikesAndRates')
            ->once()
            ->andReturn($books);
        $this->categoryRepoMock
            ->shouldReceive('getAllSubcategoriesWithBooks')
            ->once()
            ->andReturn($categoryChildren);

        $view = $this->homeController->index();
        $data = $view->getData();

        $this->assertEquals('user.index', $view->getName());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('books', $data);
        $this->assertArrayHasKey('categoryChildren', $data);
    }

    public function testChangeLanguage()
    {
        $lang = 'vi';
        $result = $this->homeController->changeLanguage($lang);

        $this->assertInstanceOf(RedirectResponse::class, $result);
    }
}
