<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Duty;
use App\Models\Member;
use App\Models\User;
use App\Models\Vehicle;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SearchTest extends TestCase
{
    #[Test]
    public function anAuthenticatedUserCanSearch(): void
    {
        $user = User::factory()->create();

        $response = $this->htmxSearch('test', $user);

        $response->assertOk();
    }

    #[Test]
    public function aGuestCannotSearch(): void
    {
        $response = $this->get('/search?q=test');

        $response->assertRedirect('/login');
    }

    #[Test]
    public function anEmptyQueryReturnsNoContent(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/search?q=', ['HX-Request' => 'true']);

        $response->assertOk();
        $response->assertContent('');
    }

    #[Test]
    public function searchesDutiesByName(): void
    {
        $user = User::factory()->create();
        Duty::factory()->create(['name' => 'Mountain Rescue']);
        Duty::factory()->create(['name' => 'River Patrol']);

        $response = $this->htmxSearch('Mountain', $user);

        $response->assertSee('Mountain Rescue');
        $response->assertDontSee('River Patrol');
    }

    #[Test]
    public function searchesDutiesByOrganiser(): void
    {
        $user = User::factory()->create();
        Duty::factory()->create(['name' => 'Duty A', 'organiser' => 'Alice Smith']);
        Duty::factory()->create(['name' => 'Duty B', 'organiser' => 'Bob Jones']);

        $response = $this->htmxSearch('Alice', $user);

        $response->assertSee('Duty A');
        $response->assertDontSee('Duty B');
    }

    #[Test]
    public function searchesMembersByName(): void
    {
        $user = User::factory()->create();
        Member::factory()->create(['name' => 'Charlie Brown']);
        Member::factory()->create(['name' => 'Daisy Duke']);

        $response = $this->htmxSearch('Charlie', $user);

        $response->assertSee('Charlie Brown');
        $response->assertDontSee('Daisy Duke');
    }

    #[Test]
    public function searchesVehiclesByCallsign(): void
    {
        $user = User::factory()->create();
        Vehicle::factory()->create(['callsign' => 'ALPHA-1', 'name' => 'First Responder']);
        Vehicle::factory()->create(['callsign' => 'BRAVO-2', 'name' => 'Support Unit']);

        $response = $this->htmxSearch('ALPHA', $user);

        $response->assertSee('ALPHA-1');
        $response->assertDontSee('BRAVO-2');
    }

    #[Test]
    public function searchesVehiclesByName(): void
    {
        $user = User::factory()->create();
        Vehicle::factory()->create(['callsign' => 'UNIT-1', 'name' => 'Ambulance']);
        Vehicle::factory()->create(['callsign' => 'UNIT-2', 'name' => 'Fire Truck']);

        $response = $this->htmxSearch('Ambulance', $user);

        $response->assertSee('UNIT-1');
        $response->assertDontSee('UNIT-2');
    }

    #[Test]
    public function unmatchedSearchReturnsNoResultsMessage(): void
    {
        $user = User::factory()->create();

        $response = $this->htmxSearch('zzzznotfound', $user);

        $response->assertSee('No results found');
        $response->assertSee('zzzznotfound');
    }

    #[Test]
    public function searchResultsAreLimitedToFivePerType(): void
    {
        $user = User::factory()->create();

        Duty::factory()->count(6)->create(['name' => 'Searchable Duty']);

        $response = $this->htmxSearch('Searchable', $user);

        $response->assertOk();
    }

    #[Test]
    public function searchResultsIncludeLinksToShowPages(): void
    {
        $user = User::factory()->create();
        $duty = Duty::factory()->create(['name' => 'Search Test Duty']);

        $response = $this->htmxSearch('Search Test', $user);

        $response->assertSee(route('duties.show', $duty));
    }

    #[Test]
    public function searchIsCaseInsensitive(): void
    {
        $user = User::factory()->create();
        Duty::factory()->create(['name' => 'Night Watch']);

        $response = $this->htmxSearch('night', $user);

        $response->assertSee('Night Watch');
    }

    #[Test]
    public function searchReturnsHtmxFragment(): void
    {
        $user = User::factory()->create();
        Duty::factory()->create(['name' => 'Fragment Test']);

        $response = $this->htmxSearch('Fragment', $user);

        $response->assertOk();
        $response->assertSee('Fragment Test');
        $response->assertDontSee('</x-layout.app>');
    }

    #[Test]
    public function nonHtmxRequestRedirectsToDashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/search?q=anything');

        $response->assertRedirect('/');
    }
    private function htmxSearch(string $query, User $user)
    {
        return $this->actingAs($user)->get('/search?q=' . urlencode($query), ['HX-Request' => 'true']);
    }
}
