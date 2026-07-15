<?php

declare(strict_types=1);

namespace Tests\Feature\Members;

use App\Models\Member;
use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MemberTest extends TestCase
{
    #[Test]
    public function theMembersIndexPageCanBeRendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/members');

        $response->assertOk();
    }

    #[Test]
    public function aGuestCannotViewTheMembersIndex(): void
    {
        $response = $this->get('/members');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function theIndexDisplaysExistingMembers(): void
    {
        $user = User::factory()->create();
        $members = Member::factory()->count(3)->create();

        $response = $this->actingAs($user)->get('/members');

        foreach ($members as $member) {
            $response->assertSee($member->name);
        }
    }

    #[Test]
    public function aMemberCanBeCreated(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/members', [
            'name' => 'Jane Doe',
            'clinical_level' => 'EMT',
        ]);

        $response->assertRedirect('/members');
        $response->assertSessionHas('flash', fn (array $flash) => ($flash['type'] ?? null) === 'success');
        $this->assertDatabaseHas('members', [
            'name' => 'Jane Doe',
            'clinical_level' => 'EMT',
        ]);
    }

    #[Test]
    public function aGuestCannotCreateAMember(): void
    {
        $response = $this->post('/members', [
            'name' => 'Jane Doe',
            'clinical_level' => 'EMT',
        ]);

        $response->assertRedirect('/login');
        $this->assertDatabaseMissing('members', ['name' => 'Jane Doe']);
    }

    #[Test]
    public function nameIsRequired(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/members', [
            'name' => '',
            'clinical_level' => 'EMT',
        ]);

        $response->assertSessionHasErrors('name');
    }

    #[Test]
    public function clinicalLevelIsRequired(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/members', [
            'name' => 'Jane Doe',
            'clinical_level' => '',
        ]);

        $response->assertSessionHasErrors('clinical_level');
    }

    #[Test]
    public function clinicalLevelMustBeValid(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/members', [
            'name' => 'Jane Doe',
            'clinical_level' => 'INVALID_LEVEL',
        ]);

        $response->assertSessionHasErrors('clinical_level');
    }

    #[Test]
    public function aMemberCanBeUpdated(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create(['name' => 'Original Name', 'clinical_level' => 'CFR', 'driver' => false]);

        $response = $this->actingAs($user)->put('/members/' . $member->id, [
            'name' => 'Updated Name',
            'clinical_level' => 'EMT',
            'driver' => '1',
        ]);

        $response->assertRedirect('/members');
        $response->assertSessionHas('flash', fn (array $flash) => ($flash['type'] ?? null) === 'success');
        $this->assertDatabaseHas('members', [
            'id' => $member->id,
            'name' => 'Updated Name',
            'clinical_level' => 'EMT',
            'driver' => 1,
        ]);
    }

    #[Test]
    public function aGuestCannotUpdateAMember(): void
    {
        $member = Member::factory()->create();

        $response = $this->put('/members/' . $member->id, [
            'name' => 'Hacked Name',
            'clinical_level' => 'EMT',
        ]);

        $response->assertRedirect('/login');
    }

    #[Test]
    public function theShowPageDisplaysMemberDetails(): void
    {
        $user = User::factory()->create();
        $member = Member::factory()->create(['name' => 'Alice Smith']);
        $duty = \App\Models\Duty::factory()->create(['name' => 'Weekend Shift']);
        $member->duties()->attach($duty);

        $response = $this->actingAs($user)->get('/members/' . $member->id);

        $response->assertOk();
        $response->assertSee('Alice Smith');
        $response->assertSee('Weekend Shift');
    }

    #[Test]
    public function aGuestCannotViewAMember(): void
    {
        $member = Member::factory()->create();

        $response = $this->get('/members/' . $member->id);

        $response->assertRedirect('/login');
    }
}
