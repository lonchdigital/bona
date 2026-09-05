<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Models\VisitRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class VisitRequestAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_list_names_and_opens_requests_directly_with_local_creation_time(): void
    {
        $olderRequest = VisitRequest::create([
            'name' => 'Стара заявка',
            'phone' => '+38(067)111-11-11',
            'status_id' => 1,
            'form_title' => 'Консультація',
            'created_at' => '2026-09-05 09:15:00',
            'updated_at' => '2026-09-05 09:15:00',
        ]);

        $newerRequest = VisitRequest::create([
            'name' => 'Нова заявка',
            'phone' => '+38(067)222-22-22',
            'status_id' => 1,
            'form_title' => 'Виклик майстра',
            'created_at' => '2026-09-05 12:30:00',
            'updated_at' => '2026-09-05 12:30:00',
        ]);

        $response = $this->actingAs($this->admin())
            ->get(route('admin.visit-request.list.page'));

        $response->assertOk()
            ->assertSee('<h2 class="mb-2 page-title">Заявки</h2>', false)
            ->assertDontSee('Список запитів')
            ->assertSee('Дата і час')
            ->assertSee('05.09.2026 15:30')
            ->assertSee('data-visit-request-row', false)
            ->assertSee('data-href="'.route('admin.visit-request.details.page', $newerRequest).'"', false)
            ->assertSee('class="visit-request-row__link text-dark"', false)
            ->assertSeeInOrder([$newerRequest->name, $olderRequest->name]);
    }

    public function test_empty_request_list_has_an_intentional_empty_state(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.visit-request.list.page'))
            ->assertOk()
            ->assertSee('Заявок поки немає.');
    }

    private function admin(): User
    {
        DB::table('roles')->insertOrIgnore([
            'id' => Role::ADMIN_ROLE_ID,
            'role' => 'Admin',
            'role_slug' => 'admin',
        ]);

        $admin = User::factory()->create();
        $admin->update(['role_id' => Role::ADMIN_ROLE_ID]);

        return $admin;
    }
}
