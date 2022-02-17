<?php

namespace Tests\Unit;

use App\Http\Controllers\ReviewController;
use App\Http\Requests\CreateReviewRequest;
use App\Http\Requests\EditReviewRequest;
use App\Models\Like;
use App\Models\Review;
use App\Models\User;
use App\Repositories\Comment\CommentRepository;
use App\Repositories\Like\LikeRepository;
use App\Repositories\Review\ReviewRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Session;
use Mockery;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
{
    protected $reviewRepoMock;
    protected $commentRepoMock;
    protected $likeRepoMock;
    protected $reviewController;

    public function setUp(): void
    {
        parent::setUp();
        $this->reviewRepoMock = Mockery::mock(ReviewRepository::class);
        $this->commentRepoMock = Mockery::mock(CommentRepository::class);
        $this->likeRepoMock = Mockery::mock(LikeRepository::class);
        $this->reviewController = new ReviewController(
            $this->reviewRepoMock,
            $this->commentRepoMock,
            $this->likeRepoMock
        );
    }

    public function tearDown(): void
    {
        Mockery::close();
        $this->reviewRepoMock = null;
        $this->commentRepoMock = null;
        $this->likeRepoMock = null;
        $this->reviewController = null;
        parent::tearDown();
    }

    public function testStore()
    {
        $request = new CreateReviewRequest([
            'content' => 'ok',
            'rate' => config('app.max-rating'),
        ]);
        $user = User::factory()->make(['id' => 1]);
        $this->be($user);
        $data = $request->all();
        $data['display'] = config('app.display');
        $data['user_id'] = $user->id;

        $this->reviewRepoMock
            ->shouldReceive('create')
            ->with($data)
            ->once();
        
        $result = $this->reviewController->store($request);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals(Session::get('success'), __('messages.create-review-success'));
    }

    public function testUpdate()
    {
        $request = new EditReviewRequest([
            'content' => 'ok',
            'rate' => config('app.max-rating'),
        ]);
        $review = Review::factory()->make(['id' => 1]);

        Gate::shouldReceive('authorize')
            ->with('update', $review)
            ->once();
        $this->reviewRepoMock
            ->shouldReceive('update')
            ->with($review->id, $request->all())
            ->once();

        $result = $this->reviewController->update($request, $review);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals(Session::get('success'), __('messages.edit-review-success'));
    }

    public function testDestroy()
    {
        $review = Review::factory()->make(['id' => 1]);

        Gate::shouldReceive('authorize')
            ->with('delete', $review)
            ->once();
        $this->reviewRepoMock
            ->shouldReceive('delete')
            ->with($review->id)
            ->once();

        $result = $this->reviewController->destroy($review);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals(Session::get('success'), __('messages.delete-review-success'));
    }

    public function testHide()
    {
        $id = 1;

        $this->reviewRepoMock
            ->shouldReceive('hideReviewById')
            ->with($id)
            ->once();
        $this->commentRepoMock
            ->shouldReceive('hideCommentsByReviewId')
            ->with($id)
            ->once();

        $result = $this->reviewController->hide($id);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals($result->getData(), (object) [
            'success' => __('messages.hide-review-success')
        ]);
    }

    public function testView()
    {
        $id = 1;

        $this->reviewRepoMock
            ->shouldReceive('showReviewById')
            ->with($id)
            ->once();
        $this->commentRepoMock
            ->shouldReceive('showCommentsByReviewId')
            ->with($id)
            ->once();

        $result = $this->reviewController->view($id);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals($result->getData(), (object) [
            'success' => __('messages.show-review-success')
        ]);
    }

    public function testRateIfExist()
    {
        $id = 1;
        $user = User::factory()->make(['id' => 1]);
        $like = Like::factory()->count(1)->make();

        $this->likeRepoMock
            ->shouldReceive('getLikeOfUserForReview')
            ->with($id, $user->id)
            ->once()
            ->andReturn($like);
        $this->be($user);
        $this->likeRepoMock
            ->shouldReceive('dislikeBookOrReview')
            ->with($id, 'App\Models\Review', $user->id)
            ->once();

        $result = $this->reviewController->rate($id);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals(Session::get('success'), __('messages.un-rate-review-success'));
    }

    public function testRateIfNotExist()
    {
        $id = 1;
        $user = User::factory()->make(['id' => 1]);
        $like = new Collection();

        $this->likeRepoMock
            ->shouldReceive('getLikeOfUserForReview')
            ->with($id, $user->id)
            ->once()
            ->andReturn($like);
        $this->be($user);
        $this->likeRepoMock
            ->shouldReceive('create')
            ->with([
                'user_id' => $user->id,
                'likeable_type' => 'App\Models\Review',
                'likeable_id' => $id,
            ])
            ->once();

        $result = $this->reviewController->rate($id);

        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals(Session::get('success'), __('messages.rate-review-success'));
    }
}
