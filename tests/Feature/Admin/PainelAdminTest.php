<?php

declare(strict_types=1);

use App\Filament\Resources\Empresas\Pages\CreateEmpresa;
use App\Filament\Resources\Empresas\Pages\EditEmpresa;
use App\Filament\Resources\Empresas\Pages\ListEmpresas;
use App\Filament\Resources\FailedJobs\Pages\ListFailedJobs;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Widgets\EmpresasMaisAtivas;
use App\Filament\Widgets\UsoPorDia;
use App\Filament\Widgets\VisaoGeral;
use App\Models\Empresa;
use App\Models\FailedJob;
use App\Models\Simulacao;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->admin = User::factory()->create(['name' => 'Admin']);
    $this->admin->forceFill(['is_admin' => true])->save();
});

function jobComFalha(string $fila = 'simulacoes', string $job = 'App\\Jobs\\SimularItensJob'): FailedJob
{
    $uuid = (string) Str::uuid();
    DB::table('failed_jobs')->insert([
        'uuid'       => $uuid,
        'connection' => 'redis',
        'queue'      => $fila,
        'payload'    => json_encode(['uuid' => $uuid, 'displayName' => $job, 'job' => 'Illuminate\\Queue\\CallQueuedHandler@call', 'data' => ['commandName' => $job]]),
        'exception'  => "RuntimeException: A calculadora não respondeu.\n#0 /var/www/html/app/Jobs/SimularItensJob.php(42)",
        'failed_at'  => now(),
    ]);

    return FailedJob::query()->where('uuid', $uuid)->sole();
}

test('só administradores acessam o painel', function (): void {
    $this->get('/admin')->assertRedirect('/admin/login');

    $this->actingAs(User::factory()->create())->get('/admin')->assertForbidden();

    $this->actingAs($this->admin)->get('/admin')
        ->assertSuccessful()
        ->assertSee('Reforma360 · Admin');
});

test('o dashboard mostra as métricas de uso', function (): void {
    $empresa = Empresa::factory()->create(['razao_social' => 'Padaria Exemplo']);
    Simulacao::factory()->count(3)->for($empresa)->create();
    jobComFalha();
    $this->actingAs($this->admin);

    Livewire::test(VisaoGeral::class)
        ->assertSee('Uso da plataforma')
        ->assertSeeInOrder(['Simulações em 30 dias', '3'])
        ->assertSee('Veja em Operação → Jobs com falha');

    Livewire::test(UsoPorDia::class)->assertSee('Simulações e importações por dia');

    Livewire::test(EmpresasMaisAtivas::class)
        ->assertCanSeeTableRecords([$empresa])
        ->assertTableColumnStateSet('simulacoes_30_dias', 3, $empresa);
});

test('o Horizon segue o mesmo critério de administrador', function (): void {
    expect(Gate::forUser($this->admin)->allows('viewHorizon'))->toBeTrue()
        ->and(Gate::forUser(User::factory()->create())->allows('viewHorizon'))->toBeFalse();
});

test('is_admin não é atribuível em massa', function (): void {
    $usuario = User::factory()->create();
    $usuario->fill(['is_admin' => true])->save();

    expect($usuario->fresh()->is_admin)->toBeFalse();
});

test('o comando promove e rebaixa administradores', function (): void {
    $usuario = User::factory()->create(['email' => 'contador@example.com']);

    $this->artisan('reforma360:tornar-admin', ['email' => 'contador@example.com'])
        ->expectsOutput('contador@example.com agora é administrador.')
        ->assertSuccessful();
    expect($usuario->fresh()->is_admin)->toBeTrue();

    $this->artisan('reforma360:tornar-admin', ['email' => 'contador@example.com', '--remover' => true])->assertSuccessful();
    expect($usuario->fresh()->is_admin)->toBeFalse();

    $this->artisan('reforma360:tornar-admin', ['email' => 'nao@existe.com'])
        ->expectsOutput('Nenhum usuário com o e-mail nao@existe.com.')
        ->assertFailed();
});

test('lista e busca empresas com as contagens', function (): void {
    $padaria = Empresa::factory()->create(['razao_social' => 'Padaria Exemplo']);
    Simulacao::factory()->count(2)->for($padaria)->create();
    $outra = Empresa::factory()->create(['razao_social' => 'Mercado Central']);

    $this->actingAs($this->admin);

    Livewire::test(ListEmpresas::class)
        ->assertCanSeeTableRecords([$padaria, $outra])
        ->searchTable('Padaria')
        ->assertCanSeeTableRecords([$padaria])
        ->assertCanNotSeeTableRecords([$outra])
        ->assertTableColumnStateSet('simulacoes_count', 2, $padaria);
});

test('cria e edita empresa com CNPJ validado e guardado só com dígitos', function (): void {
    Empresa::factory()->create(['cnpj' => '11444777000161']);
    $this->actingAs($this->admin);

    Livewire::test(CreateEmpresa::class)
        ->fillForm(['razao_social' => 'Nova Ltda', 'cnpj' => '11.222.333/0001-82'])
        ->call('create')
        ->assertHasFormErrors(['cnpj']);

    Livewire::test(CreateEmpresa::class)
        ->fillForm(['razao_social' => 'Nova Ltda', 'cnpj' => '11.444.777/0001-61'])
        ->call('create')
        ->assertHasFormErrors(['cnpj' => 'unique']);

    Livewire::test(CreateEmpresa::class)
        ->fillForm(['razao_social' => 'Nova Ltda', 'cnpj' => '11.222.333/0001-81'])
        ->call('create')
        ->assertHasNoFormErrors();

    $empresa = Empresa::query()->where('razao_social', 'Nova Ltda')->sole();
    expect($empresa->cnpj)->toBe('11222333000181');

    Livewire::test(EditEmpresa::class, ['record' => $empresa->id])
        ->fillForm(['razao_social' => 'Nova Razão Ltda'])
        ->call('save')
        ->assertHasNoFormErrors();
    expect($empresa->fresh()->razao_social)->toBe('Nova Razão Ltda');
});

test('lista e filtra usuários por administrador', function (): void {
    $cliente = User::factory()->create();
    $this->actingAs($this->admin);

    Livewire::test(ListUsers::class)
        ->assertCanSeeTableRecords([$this->admin, $cliente])
        ->filterTable('is_admin', true)
        ->assertCanSeeTableRecords([$this->admin])
        ->assertCanNotSeeTableRecords([$cliente]);
});

test('edita usuário, promove a administrador e troca a empresa', function (): void {
    $cliente = User::factory()->create();
    $outraEmpresa = Empresa::factory()->create();
    $this->actingAs($this->admin);

    Livewire::test(EditUser::class, ['record' => $cliente->id])
        ->fillForm(['name' => 'Novo Nome', 'empresa_id' => $outraEmpresa->id, 'is_admin' => true])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($cliente->fresh())
        ->name->toBe('Novo Nome')
        ->empresa_id->toBe($outraEmpresa->id)
        ->is_admin->toBeTrue();
});

test('o administrador não remove o próprio acesso nem se exclui', function (): void {
    $this->actingAs($this->admin);

    Livewire::test(EditUser::class, ['record' => $this->admin->id])
        ->assertFormFieldIsDisabled('is_admin')
        ->assertActionHidden('delete')
        ->fillForm(['name' => 'Admin Renomeado'])
        ->call('save');

    expect($this->admin->fresh())
        ->name->toBe('Admin Renomeado')
        ->is_admin->toBeTrue();
});

test('cria usuário e envia o link para definir a senha', function (): void {
    Notification::fake();
    $empresa = Empresa::factory()->create();
    $this->actingAs($this->admin);

    Livewire::test(CreateUser::class)
        ->fillForm(['name' => 'Contadora', 'email' => 'contadora@example.com', 'empresa_id' => $empresa->id, 'is_admin' => false])
        ->call('create')
        ->assertHasNoFormErrors();

    $usuario = User::query()->where('email', 'contadora@example.com')->sole();
    expect($usuario->empresa_id)->toBe($empresa->id)->and($usuario->is_admin)->toBeFalse();
    Notification::assertSentTo($usuario, ResetPassword::class);
});

test('envia redefinição de senha pela lista de usuários', function (): void {
    Notification::fake();
    $cliente = User::factory()->create();
    $this->actingAs($this->admin);

    Livewire::test(ListUsers::class)
        ->callAction(TestAction::make('enviarRedefinicaoDeSenha')->table($cliente))
        ->assertNotified('Link de redefinição enviado.');

    Notification::assertSentTo($cliente, ResetPassword::class);
});

test('lista jobs com falha e mostra os detalhes', function (): void {
    $job = jobComFalha();
    $this->actingAs($this->admin);

    Livewire::test(ListFailedJobs::class)
        ->assertCanSeeTableRecords([$job])
        ->assertTableColumnStateSet('job', 'SimularItensJob', $job)
        ->assertTableColumnStateSet('erro', 'RuntimeException: A calculadora não respondeu.', $job)
        ->mountAction(TestAction::make('detalhes')->table($job))
        ->assertMountedActionModalSee('SimularItensJob.php(42)');
});

test('reprocessa e descarta jobs com falha', function (): void {
    Queue::fake();
    $reprocessar = jobComFalha();
    $descartar = jobComFalha('importacoes', 'App\\Jobs\\ProcessarImportacaoJob');
    $this->actingAs($this->admin);

    Livewire::test(ListFailedJobs::class)
        ->callAction(TestAction::make('reprocessar')->table($reprocessar))
        ->assertNotified('Job enviado de volta para a fila.')
        ->callAction(TestAction::make('descartar')->table($descartar))
        ->assertNotified('Job descartado.');

    expect(FailedJob::query()->count())->toBe(0)
        ->and(Queue::pushedRaw())->toHaveCount(1);
});

test('reprocessa em lote', function (): void {
    Queue::fake();
    $jobs = collect([jobComFalha(), jobComFalha()]);
    $this->actingAs($this->admin);

    Livewire::test(ListFailedJobs::class)
        ->selectTableRecords($jobs->pluck('id')->all())
        ->callAction(TestAction::make('reprocessarSelecionados')->table()->bulk());

    expect(FailedJob::query()->count())->toBe(0);
});

test('usuário comum não usa os recursos do painel', function (): void {
    $this->actingAs(User::factory()->create());

    $this->get('/admin/empresas')->assertForbidden();
    $this->get('/admin/users')->assertForbidden();
    $this->get('/admin/jobs-com-falha')->assertForbidden();
});

test('o menu do app mostra o link do painel só para administradores', function (): void {
    $this->actingAs($this->admin)->get(route('produtos.index'))->assertSee('>Admin</a>', escape: false);
    $this->actingAs(User::factory()->create())->get(route('produtos.index'))->assertDontSee('>Admin</a>', escape: false);
});
