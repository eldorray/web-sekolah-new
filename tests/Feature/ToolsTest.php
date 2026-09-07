<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AdiwiyataAssessment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ToolsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin', 'is_active' => true]);
    }

    public function test_adiwiyata_progress_is_public(): void
    {
        $this->get('/adiwiyata')
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('public/Adiwiyata')
                ->where('canEdit', false)
                ->has('tree'));
    }

    public function test_guests_cannot_change_a_folder_status(): void
    {
        $this->post('/adiwiyata/save', [
            'folder_key' => 'IKPA/1',
            'status' => 'ok',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseCount('adiwiyata_assessments', 0);
    }

    public function test_guru_cannot_change_a_folder_status(): void
    {
        $guru = User::factory()->create(['role' => 'guru', 'is_active' => true]);

        $this->actingAs($guru)->post('/adiwiyata/save', [
            'folder_key' => 'IKPA/1',
            'status' => 'ok',
        ])->assertForbidden();

        $this->assertDatabaseCount('adiwiyata_assessments', 0);
    }

    public function test_admin_can_save_and_reset_assessments(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->get('/adiwiyata')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('canEdit', true));

        $this->actingAs($admin)->post('/adiwiyata/save', [
            'folder_key' => 'IKPA/1',
            'status' => 'ok',
            'note' => 'SK sudah ditandatangani.',
        ])
            ->assertRedirect()
            ->assertInertiaFlash('toast.message', 'Penilaian tersimpan.');

        $this->assertDatabaseHas('adiwiyata_assessments', [
            'folder_key' => 'IKPA/1',
            'status' => 'ok',
        ]);

        $this->actingAs($admin)->post('/adiwiyata/reset')->assertRedirect();
        $this->assertDatabaseCount('adiwiyata_assessments', 0);
    }

    public function test_unknown_folder_keys_are_rejected(): void
    {
        $this->actingAs($this->admin())->post('/adiwiyata/save', [
            'folder_key' => 'FOLDER/PALSU',
            'status' => 'ok',
        ])->assertSessionHasErrors('folder_key');

        $this->assertSame(0, AdiwiyataAssessment::query()->count());
    }

    public function test_the_ai_tool_is_gone(): void
    {
        $this->get('/ypdh-ai')->assertNotFound();
        $this->post('/ypdh-ai/chat')->assertNotFound();
    }
}
