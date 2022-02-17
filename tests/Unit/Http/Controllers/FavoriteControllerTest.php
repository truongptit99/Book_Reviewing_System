<?php

namespace Tests\Unit;

use App\Http\Controllers\FavoriteController;
use App\Models\Book;
use App\Models\Like;
use App\Models\User;
use App\Repositories\Book\BookRepository;
use App\Repositories\Favorite\FavoriteRepository;
use App\Repositories\Like\LikeRepository;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class FavoriteControllerTest extends TestCase
{
    protected $favoriteRepoMock;
    protected $bookRepoMock;
    protected $likeRepoMock;
    protected $favoriteController;

    public function setUp(): void
    {
        parent::setUp();
        $this->favoriteRepoMock = Mockery::mock(FavoriteRepository::class);
        $this->bookRepoMock = Mockery::mock(BookRepository::class);
        $this->likeRepoMock = Mockery::mock(LikeRepository::class);
        $this->favoriteController = new FavoriteController(
            $this->favoriteRepoMock,
            $this->bookRepoMock,
            $this->likeRepoMock
        );
    }

    public function tearDown(): void
    {
        Mockery::close();
        $this->favoriteRepoMock = null;
        $this->bookRepoMock = null;
        $this->likeRepoMock = null;
        $this->favoriteController = null;
        parent::tearDown();
    }

    public function testStore()
    {
        $request = new Request(['book_id' => 1]);
        $user = User::factory()->make(['id' => 1]);

        $this->be($user);
        $this->favoriteRepoMock
            ->shouldReceive('create')
            ->with([
                'user_id' => $user->id,
                'book_id' => $request->book_id,
            ])
            ->once();

        $result = $this->favoriteController->store($request);

        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString('{"statusCode":200}', $result);
    }

    public function testDestroy()
    {
        $book_id = 1;
        $user = User::factory()->make(['id' => 1]);

        $this->be($user);
        $this->favoriteRepoMock
            ->shouldReceive('forceDeleteFavorite')
            ->with($book_id, $user->id)
            ->once();

        $result = $this->favoriteController->destroy($book_id);

        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString('{"statusCode":200}', $result);
    }

    public function testIndex()
    {
        $user = User::factory()->make(['id' => 1]);

        $this->be($user);
        $this->bookRepoMock
            ->shouldReceive('getFavoriteBooksByUserId')
            ->with($user->id)
            ->once();
        $this->likeRepoMock
            ->shouldReceive('getLikedBookIdsByUserId')
            ->with($user->id)
            ->once();

        $view = $this->favoriteController->index();
        $data = $view->getData();

        $this->assertEquals('user.favorite_books', $view->getName());
        $this->assertIsArray($data);
        $this->assertArrayHasKey('books', $data);
        $this->assertArrayHasKey('likes', $data);
        $this->assertArrayHasKey('categoryChildren', $data);
    }
}
