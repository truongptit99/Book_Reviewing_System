<?php

namespace Tests\Unit;

use App\Http\Controllers\AdminController;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    protected $adminController;
    public function setUp(): void
    {
        parent::setUp();
        $this->adminController = new AdminController();
    }

    public function tearDown(): void
    {
        $this->adminController = null;
        parent::tearDown();
    }

    public function testIndex()
    {
        $view = $this->adminController->index();

        $this->assertEquals('admin.layouts.layout', $view->getName());
    }
}
