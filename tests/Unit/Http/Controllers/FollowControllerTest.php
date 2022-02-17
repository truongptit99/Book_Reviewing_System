<?php

namespace Tests\Unit;

use App\Http\Controllers\FollowController;
use App\Models\Follow;
use App\Models\User;
use App\Repositories\Follow\FollowRepository;
use App\Repositories\User\UserRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Mockery;
use Tests\TestCase;

class FollowControllerTest extends TestCase
{
    protected $followRepoMock;
    protected $userRepoMock;
    protected $followController;

    public function setUp(): void
    {
        parent::setUp();
        $this->followRepoMock = Mockery::mock(FollowRepository::class);
        $this->userRepoMock = Mockery::mock(UserRepository::class);
        $this->followController = new FollowController(
            $this->followRepoMock,
            $this->userRepoMock
        );
    }

    public function tearDown(): void
    {
        Mockery::close();
        $this->followRepoMock = null;
        $this->userRepoMock = null;
        $this->followController = null;
        parent::tearDown();
    }

    public function testDestroy()
    {
        $user = User::factory()->make(['id' => 1]);
        $id = 2;

        $this->be($user);
        $this->followRepoMock
            ->shouldReceive('deleteRelationship')
            ->with($user->id, $id)
            ->once();

        $result = $this->followController->destroy($id);

        $this->assertJson($result);
        $this->assertJsonStringEqualsJsonString('{"statusCode":200}', $result);
    }
}
