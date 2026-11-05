<?php

namespace Tests\Feature\Models;

use App\Models\Kamar;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengurus_only_sees_kamar_from_own_kos(): void
    {
        $kosA = Kos::factory()->create();
        $kosB = Kos::factory()->create();
        $pengurusA = User::factory()->create(['role' => 'pengurus', 'kos_id' => $kosA->id]);

        Kamar::factory()->count(2)->create(['kos_id' => $kosA->id]);
        Kamar::factory()->count(3)->create(['kos_id' => $kosB->id]);

        $this->actingAs($pengurusA);

        $this->assertCount(2, Kamar::all());
        $this->assertSame([$kosA->id], Kamar::query()->pluck('kos_id')->unique()->values()->all());
    }

    public function test_penghuni_is_not_scoped_by_tenant(): void
    {
        $kosA = Kos::factory()->create();
        $kosB = Kos::factory()->create();
        $penghuni = User::factory()->create(['role' => 'penghuni']);

        Kamar::factory()->create(['kos_id' => $kosA->id]);
        Kamar::factory()->create(['kos_id' => $kosB->id]);

        $this->actingAs($penghuni);

        $this->assertCount(2, Kamar::all());
    }

    public function test_super_admin_is_not_scoped_by_tenant(): void
    {
        $kosA = Kos::factory()->create();
        $kosB = Kos::factory()->create();
        $superAdmin = User::factory()->create(['role' => 'super_admin']);

        Kamar::factory()->create(['kos_id' => $kosA->id]);
        Kamar::factory()->create(['kos_id' => $kosB->id]);

        $this->actingAs($superAdmin);

        $this->assertCount(2, Kamar::all());
    }
}
