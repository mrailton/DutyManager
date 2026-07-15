<?php

declare(strict_types=1);

namespace Tests\Feature\Users;

use App\Models\User;
use App\Notifications\UserInvited;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserTest extends TestCase
{
    #[Test]
    public function theUsersIndexPageCanBeRendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/users');

        $response->assertOk();
    }

    #[Test]
    public function aGuestCannotViewTheUsersIndex(): void
    {
        $response = $this->get('/users');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function theIndexDisplaysExistingUsers(): void
    {
        $admin = User::factory()->create();
        $other = User::factory()->create(['name' => 'Jane Smith']);

        $response = $this->actingAs($admin)->get('/users');

        $response->assertSee('Jane Smith');
    }

    #[Test]
    public function aUserCanBeCreatedAndInvited(): void
    {
        Notification::fake();

        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);

        $response->assertRedirect('/users');
        $response->assertSessionHas('flash', fn (array $flash) => ($flash['type'] ?? null) === 'success');
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);

        $createdUser = User::where('email', 'newuser@example.com')->first();
        Notification::assertSentTo($createdUser, UserInvited::class);
    }

    #[Test]
    public function aGuestCannotCreateAUser(): void
    {
        $response = $this->post('/users', [
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
        ]);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function emailMustBeUnique(): void
    {
        $admin = User::factory()->create();
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'Another User',
            'email' => 'taken@example.com',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[Test]
    public function softDeletedUserPromptsReactivateModal(): void
    {
        $admin = User::factory()->create();
        $user = User::factory()->create(['name' => 'Old Name', 'email' => 'reuse@example.com']);
        $user->delete();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => 'New User',
            'email' => 'reuse@example.com',
        ]);

        $response->assertSessionHas('reactivate_user', $user->id);
    }

    #[Test]
    public function aSoftDeletedUserCanBeReactivated(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $user = User::factory()->create(['name' => 'Old Name', 'email' => 'reuse@example.com']);
        $user->delete();

        $response = $this->actingAs($admin)->post('/users/' . $user->id . '/reactivate', [
            'name' => 'New User',
        ]);

        $response->assertRedirect('/users');
        $response->assertSessionHas('flash', fn (array $flash) => ($flash['type'] ?? null) === 'success');

        $user->refresh();
        $this->assertFalse($user->trashed());
        $this->assertEquals('New User', $user->name);

        Notification::assertSentTo($user, UserInvited::class);
    }

    #[Test]
    public function aGuestCannotReactivateAUser(): void
    {
        $user = User::factory()->create();
        $user->delete();

        $response = $this->post('/users/' . $user->id . '/reactivate', [
            'name' => 'Hacker',
        ]);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function nameIsRequired(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->post('/users', [
            'name' => '',
            'email' => 'newuser@example.com',
        ]);

        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function theShowPageDisplaysUserDetails(): void
    {
        $admin = User::factory()->create();
        $target = User::factory()->create(['name' => 'Target User']);

        $response = $this->actingAs($admin)->get('/users/' . $target->id);

        $response->assertOk();
        $response->assertSee('Target User');
        $response->assertSee('Edit User');
    }

    #[Test]
    public function aGuestCannotViewAUser(): void
    {
        $target = User::factory()->create();

        $response = $this->get('/users/' . $target->id);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function aUserCanBeUpdated(): void
    {
        $admin = User::factory()->create();
        $target = User::factory()->create(['name' => 'Old Name', 'email' => 'old@example.com']);

        $response = $this->actingAs($admin)->put('/users/' . $target->id, [
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);

        $response->assertRedirect('/users/' . $target->id);
        $response->assertSessionHas('flash', fn (array $flash) => ($flash['type'] ?? null) === 'success');
        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'name' => 'New Name',
            'email' => 'new@example.com',
        ]);
    }

    #[Test]
    public function aGuestCannotUpdateAUser(): void
    {
        $target = User::factory()->create();

        $response = $this->put('/users/' . $target->id, [
            'name' => 'Hacked Name',
            'email' => 'hacked@example.com',
        ]);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function aUserCanBeSoftDeleted(): void
    {
        $admin = User::factory()->create();
        $target = User::factory()->create();

        $response = $this->actingAs($admin)->delete('/users/' . $target->id);

        $response->assertRedirect('/users');
        $response->assertSessionHas('flash', fn (array $flash) => ($flash['type'] ?? null) === 'success');
        $this->assertSoftDeleted($target);
    }

    #[Test]
    public function aUserCannotDeleteThemselves(): void
    {
        $admin = User::factory()->create();

        $response = $this->actingAs($admin)->delete('/users/' . $admin->id);

        $response->assertSessionHas('flash', fn (array $flash) => ($flash['type'] ?? null) === 'danger');
        $this->assertNotSoftDeleted($admin);
    }

    #[Test]
    public function duplicateEmailsAreRejectedAtTheDatabaseLevel(): void
    {
        User::factory()->create(['email' => 'duplicate@example.com']);

        $this->expectException(QueryException::class);

        User::create([
            'name' => 'Duplicate User',
            'email' => 'duplicate@example.com',
            'password' => 'password',
        ]);
    }

    #[Test]
    public function aGuestCannotDeleteAUser(): void
    {
        $target = User::factory()->create();

        $response = $this->delete('/users/' . $target->id);

        $response->assertRedirect('/login');
    }
}
