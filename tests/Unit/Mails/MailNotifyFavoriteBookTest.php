<?php

namespace Tests\Unit;

use App\Mail\MailNotifyFavoriteBook;
use App\Models\Book;
use App\Models\User;
use Tests\TestCase;

class MailNotifyFavoriteBookTest extends TestCase
{
    public function testMailContent()
    {
        $user = User::factory()->make(['id' => 1]);
        $favoriteBooks = Book::factory()->count(5)->make();

        $mail = new MailNotifyFavoriteBook(
            $user,
            $favoriteBooks
        );

        $mail->assertSeeInText(__('messages.hello'));
        $mail->assertSeeInText($user->username);
        $mail->assertSeeInText(__('messages.announce-list-reviews'));
        $mail->assertSeeInText(__('messages.user'));
        $mail->assertSeeInText(__('messages.content-review'));
        $mail->assertSeeInText(__('messages.rate'));
        $mail->assertSeeInText(config('app.name'));
    }
}
