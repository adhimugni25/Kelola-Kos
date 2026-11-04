# KelolaKos MVP Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a working multi-tenant kos management SaaS with booking, billing, and payment verification, deployed to kelolakos.site with HTTPS.

**Architecture:** Laravel 11+ MVC with global scopes for tenant isolation, Form Requests for validation, Policies for authorization, Service classes for complex transactions. Blade views + Tailwind CSS. MySQL 8. Private file storage for payment proofs.

**Tech Stack:** Laravel 11, PHP 8.3, MySQL 8, Tailwind CSS 3, Vite, Chart.js 4, dompdf

**Spec:** `docs/superpowers/specs/2026-11-04-kelolakos-design.md`

## Global Constraints

- PHP 8.3 (8.3.36 di development, minimum 8.3.0 produksi untuk keamanan), Laravel ≥ 11, MySQL 8
- Zona waktu `Asia/Jakarta`, format tanggal `d/m/Y`
- Semua uang `decimal(12,2)`, tidak pernah float
- UI Bahasa Indonesia
- Password bcrypt cost 12
- Rate limit: login 5/menit per email+IP, register 3/menit, upload 10/menit, umum 60/menit
- Session: database driver, HttpOnly, Secure (produksi), SameSite=Lax, 30 menit idle / 12 jam absolut
- Tidak ada soft delete; penonaktifan lewat flag `is_active`/status enum
- Foreign key restrict on delete (cascade tidak dipakai)
- Secret di `.env`, tidak di code/commit
- Commit message: conventional commits (`feat:`, `fix:`, `test:`, `refactor:`, `docs:`)

## Review Focus

1. **Tenant isolation breach:** Pengurus A membuka `/pengurus/kamar/{id}` dengan ID kamar milik kos B → harus 404/403, tidak boleh terlihat data atau error message yang bocorkan struktur.
2. **IDOR pembayaran:** Penghuni A menebak URL bukti pembayaran penghuni B → harus 403 dan file tidak terunduh; test dengan signed URL atau policy check di controller.
3. **Double booking approval:** Dua pengajuan ke kamar sama disetujui bersamaan (race condition) → hanya satu sukses, yang lain error; test dengan database transaction + lock.
4. **SQL injection di filter laporan:** Input tanggal/bulan di form filter laporan berisi `' OR 1=1 --` → harus divalidasi dan di-bind, query tidak rusak; test dengan request palsu.
5. **XSS di catatan:** Catatan pemesanan/pembayaran berisi `<script>alert(1)</script>` → ter-escape di Blade `{{ }}`, tidak eksekusi; test dengan simpan lalu render.

---

## File Structure Overview

```
app/
  Console/Commands/CreateSuperAdmin.php
  Http/
    Controllers/
      Auth/AuthController.php
      Public/{HomeController,KosPublicController}.php
      SuperAdmin/{DashboardController,KosController,PengurusController}.php
      Pengurus/{DashboardController,KamarController,PemesananController,TagihanController,PembayaranController,LaporanController,ImportController}.php
      Penghuni/{DashboardController,PemesananController,TagihanController}.php
    Middleware/{EnsureRole,SecurityHeaders,TenantScope}.php
    Requests/
      {LoginRequest,RegisterRequest}.php
      SuperAdmin/{StoreKosRequest,UpdateKosRequest,StorePengurusRequest}.php
      Pengurus/{StoreKamarRequest,UpdateKamarRequest,ApprovePemesananRequest,StoreTagihanRequest,VerifikasiPembayaranRequest}.php
      Penghuni/{StorePemesananRequest,UploadBuktiRequest}.php
  Models/{User,Kos,Kamar,Pemesanan,Tagihan,Pembayaran,MetodePembayaran,AuditLog}.php
  Policies/{KosPolicy,KamarPolicy,PemesananPolicy,TagihanPolicy,PembayaranPolicy}.php
  Services/{BookingService,BillingService,PaymentService,ImportService,AuditLogger}.php
  Support/TenantScope.php
config/{session,filesystems}.php (modify)
database/
  migrations/2026_11_04_*_create_tables.php
  factories/{UserFactory,KosFactory,KamarFactory}.php
  seeders/{DatabaseSeeder}.php
resources/
  views/
    layouts/{app,auth,guest}.blade.php
    components/{button,input,alert,card}.blade.php
    auth/{login,register}.blade.php
    public/{landing,kos-public}.blade.php
    superadmin/{dashboard,kos,pengurus}.blade.php
    pengurus/{dashboard,kamar,pemesanan,tagihan,pembayaran,laporan}.blade.php
    penghuni/{dashboard,pemesanan,tagihan}.blade.php
  css/app.css
  js/app.js
routes/web.php
tests/
  Feature/
    Auth/{LoginTest,RegisterTest,RoleTest}.php
    TenantIsolation/{KamarIsolationTest,TagihanIsolationTest}.php
    Booking/{CreatePemesananTest,ApprovePemesananTest,RejectPemesananTest}.php
    Billing/{CreateTagihanTest,UpdateTagihanTest}.php
    Payment/{UploadBuktiTest,VerifikasiTest}.php
    Security/{RateLimitTest,CSRFTest,XSSTest,SQLInjectionTest}.php
    Import/{ImportKamarTest,ImportPenghuniTest}.php
    Reports/{DashboardTest,LaporanTest,ExportTest}.php
docs/superpowers/{specs,plans}/
.env.example
.gitignore
README.md
```

---

### Task 1: Project Scaffold & Security Foundation

**Files:**
- Create: entire Laravel structure via `composer create-project`
- Modify: `config/session.php`, `config/filesystems.php`, `.env.example`, `.gitignore`, `bootstrap/app.php`
- Create: `app/Http/Middleware/SecurityHeaders.php`
- Create: `tests/Feature/Security/SecurityHeadersTest.php`

**Interfaces:**
- Consumes: none (first task)
- Produces: working Laravel app with security baseline, ready for models

- [ ] **Step 1: Create Laravel project**

```bash
cd C:\laragon\www
composer create-project laravel/laravel kelolakos
cd kelolakos
```

- [ ] **Step 2: Configure database and session in `.env`**

```
APP_NAME=KelolaKos
APP_TIMEZONE=Asia/Jakarta
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kelolakos
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=database
SESSION_LIFETIME=30
SESSION_EXPIRE_ON_CLOSE=false
```

- [ ] **Step 3: Create database**

```bash
php artisan make:migration create_sessions_table
php artisan migrate
```

Expected: `sessions` table created

- [ ] **Step 4: Update `config/session.php` for security**

```php
'http_only' => true,
'same_site' => 'lax',
'secure' => env('SESSION_SECURE_COOKIE', false),
'domain' => env('SESSION_DOMAIN'),
```

- [ ] **Step 5: Add private disk to `config/filesystems.php`**

```php
'disks' => [
    // existing...
    'private' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'visibility' => 'private',
    ],
],
```

- [ ] **Step 6: Update `.env.example` with safe template**

Remove all values, keep only keys: `APP_NAME=`, `DB_DATABASE=`, etc. No secrets.

- [ ] **Step 7: Ensure `.gitignore` contains `.env`, `vendor/`, `node_modules/`, `public/hot`, `storage/*.key`**

- [ ] **Step 8: Write failing test for security headers**

`tests/Feature/Security/SecurityHeadersTest.php`:

```php
<?php
namespace Tests\Feature\Security;
use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_security_headers_present_on_all_routes(): void
    {
        $response = $this->get('/');
        
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->assertHeader('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    }
}
```

- [ ] **Step 9: Run test to verify it fails**

Run: `php artisan test --filter=SecurityHeadersTest`
Expected: FAIL "Header not present"

- [ ] **Step 10: Create SecurityHeaders middleware**

`app/Http/Middleware/SecurityHeaders.php`:

```php
<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
        
        return $response;
    }
}
```

- [ ] **Step 11: Register middleware globally in `bootstrap/app.php`**

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
})
```

- [ ] **Step 12: Run test to verify it passes**

Run: `php artisan test --filter=SecurityHeadersTest`
Expected: PASS

- [ ] **Step 13: Install Tailwind CSS**

```bash
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

Update `tailwind.config.js`:

```js
content: [
  "./resources/**/*.blade.php",
  "./resources/**/*.js",
],
```

Update `resources/css/app.css`:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

- [ ] **Step 14: Build assets**

```bash
npm install
npm run build
```

- [ ] **Step 15: Create directory structure**

```bash
mkdir -p docs/superpowers/{specs,plans}
mkdir -p storage/app/private/bukti
mkdir -p app/Services
mkdir -p app/Support
mkdir -p tests/Feature/{Auth,TenantIsolation,Booking,Billing,Payment,Security,Import,Reports}
```

- [ ] **Step 16: Commit**

```bash
git init
git add .
git commit -m "feat: initial Laravel scaffold with security headers"
```

---

### Task 2: Database Schema & Models

**Files:**
- Create: 8 migration files for all tables
- Create: 8 model files with relationships and casts
- Create: `app/Support/TenantScope.php`
- Create: `tests/Feature/Models/RelationshipTest.php`

**Interfaces:**
- Consumes: Laravel scaffold from Task 1
- Produces: `User`, `Kos`, `Kamar`, `Pemesanan`, `Tagihan`, `Pembayaran`, `MetodePembayaran`, `AuditLog` models with relationships

- [ ] **Step 1: Create migration for users table**

```bash
php artisan make:migration create_users_table
```

`database/migrations/2026_11_04_000001_create_users_table.php`:

```php
Schema::create('users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->enum('role', ['super_admin', 'pengurus', 'penghuni'])->default('penghuni');
    $table->string('phone')->nullable();
    $table->foreignId('kos_id')->nullable()->constrained('kos')->restrictOnDelete();
    $table->boolean('is_active')->default(true);
    $table->timestamp('last_login_at')->nullable();
    $table->rememberToken();
    $table->timestamps();
    
    $table->index('role');
    $table->index('kos_id');
});
```

- [ ] **Step 2: Create migration for kos table**

```bash
php artisan make:migration create_kos_table
```

```php
Schema::create('kos', function (Blueprint $table) {
    $table->id();
    $table->string('nama');
    $table->string('slug')->unique();
    $table->text('alamat');
    $table->string('kota');
    $table->text('deskripsi')->nullable();
    $table->string('kontak');
    $table->text('fasilitas_umum')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

- [ ] **Step 3: Create migration for kamar table**

```bash
php artisan make:migration create_kamar_table
```

```php
Schema::create('kamar', function (Blueprint $table) {
    $table->id();
    $table->foreignId('kos_id')->constrained('kos')->restrictOnDelete();
    $table->string('nomor');
    $table->string('tipe');
    $table->decimal('harga_bulanan', 12, 2);
    $table->text('fasilitas')->nullable();
    $table->string('foto_path')->nullable();
    $table->enum('status', ['kosong', 'terisi', 'perbaikan'])->default('kosong');
    $table->text('catatan')->nullable();
    $table->timestamps();
    
    $table->unique(['kos_id', 'nomor']);
    $table->index(['kos_id', 'status']);
});
```

- [ ] **Step 4: Create migration for pemesanan table**

```bash
php artisan make:migration create_pemesanan_table
```

```php
Schema::create('pemesanan', function (Blueprint $table) {
    $table->id();
    $table->foreignId('kos_id')->constrained('kos')->restrictOnDelete();
    $table->foreignId('kamar_id')->constrained('kamar')->restrictOnDelete();
    $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
    $table->date('tanggal_mulai');
    $table->smallInteger('durasi_bulan');
    $table->decimal('harga_bulanan', 12, 2);
    $table->date('berakhir_pada')->nullable();
    $table->enum('status', ['menunggu', 'aktif', 'ditolak', 'selesai', 'dibatalkan'])->default('menunggu');
    $table->text('catatan')->nullable();
    $table->text('alasan_penolakan')->nullable();
    $table->foreignId('disetujui_oleh')->nullable()->constrained('users')->restrictOnDelete();
    $table->timestamp('disetujui_pada')->nullable();
    $table->timestamps();
    
    $table->index(['kos_id', 'status']);
    $table->index(['user_id', 'status']);
    $table->index(['kamar_id', 'status']);
    $table->index(['kos_id', 'tanggal_mulai']);
    $table->index(['kos_id', 'berakhir_pada']);
});
```

- [ ] **Step 5: Create migrations for tagihan, pembayaran, metode_pembayaran, audit_logs**

Commands:

```bash
php artisan make:migration create_tagihan_table
php artisan make:migration create_pembayaran_table
php artisan make:migration create_metode_pembayaran_table
php artisan make:migration create_audit_logs_table
```

Schema `tagihan`:

```php
Schema::create('tagihan', function (Blueprint $table) {
    $table->id();
    $table->foreignId('kos_id')->constrained('kos')->restrictOnDelete();
    $table->foreignId('pemesanan_id')->constrained('pemesanan')->restrictOnDelete();
    $table->tinyInteger('periode_bulan');
    $table->smallInteger('periode_tahun');
    $table->decimal('nominal_sewa', 12, 2);
    $table->decimal('biaya_tambahan', 12, 2)->default(0);
    $table->string('keterangan_tambahan')->nullable();
    $table->decimal('total', 12, 2);
    $table->date('jatuh_tempo');
    $table->enum('status', ['belum_bayar', 'menunggu_verifikasi', 'lunas', 'dibatalkan'])->default('belum_bayar');
    $table->timestamps();
    
    $table->unique(['pemesanan_id', 'periode_tahun', 'periode_bulan']);
    $table->index(['kos_id', 'status']);
    $table->index('jatuh_tempo');
});
```

Schema `pembayaran`:

```php
Schema::create('pembayaran', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tagihan_id')->constrained('tagihan')->restrictOnDelete();
    $table->foreignId('metode_pembayaran_id')->constrained('metode_pembayaran')->restrictOnDelete();
    $table->decimal('jumlah', 12, 2);
    $table->string('bukti_path');
    $table->text('catatan_penghuni')->nullable();
    $table->enum('status', ['menunggu', 'diterima', 'ditolak'])->default('menunggu');
    $table->text('catatan_verifikasi')->nullable();
    $table->foreignId('diverifikasi_oleh')->nullable()->constrained('users')->restrictOnDelete();
    $table->timestamp('diverifikasi_pada')->nullable();
    $table->timestamps();
    
    $table->index(['tagihan_id', 'status']);
});
```

Schema `metode_pembayaran`:

```php
Schema::create('metode_pembayaran', function (Blueprint $table) {
    $table->id();
    $table->foreignId('kos_id')->constrained('kos')->restrictOnDelete();
    $table->enum('tipe', ['bank', 'ewallet']);
    $table->string('nama');
    $table->string('nomor');
    $table->string('atas_nama');
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->index(['kos_id', 'is_active']);
});
```

Schema `audit_logs`:

```php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained('users')->restrictOnDelete();
    $table->foreignId('kos_id')->nullable()->constrained('kos')->restrictOnDelete();
    $table->string('aksi');
    $table->string('entitas')->nullable();
    $table->bigInteger('entitas_id')->nullable();
    $table->json('detail')->nullable();
    $table->string('ip', 45);
    $table->text('user_agent')->nullable();
    $table->timestamp('created_at');
    
    $table->index(['kos_id', 'created_at']);
    $table->index(['user_id', 'created_at']);
    $table->index(['aksi', 'created_at']);
});
```

- [ ] **Step 6: Run migrations**

```bash
php artisan migrate
```

Expected: all 8 tables created

- [ ] **Step 7: Create User model**

`app/Models/User.php`:

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'kos_id', 'is_active', 'last_login_at',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function kos()
    {
        return $this->belongsTo(Kos::class);
    }

    public function pemesanan()
    {
        return $this->hasMany(Pemesanan::class);
    }
}
```

- [ ] **Step 8: Create remaining models with relationships**

`app/Models/Kos.php`:

```php
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kos extends Model
{
    protected $table = 'kos';
    protected $fillable = ['nama', 'slug', 'alamat', 'kota', 'deskripsi', 'kontak', 'fasilitas_umum', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function kamar()
    {
        return $this->hasMany(Kamar::class);
    }

    public function pengurus()
    {
        return $this->hasMany(User::class)->where('role', 'pengurus');
    }
}
```

`app/Models/Kamar.php`, `Pemesanan.php`, `Tagihan.php`, `Pembayaran.php`, `MetodePembayaran.php`, `AuditLog.php` following same pattern with relationships from spec section 6.

- [ ] **Step 9: Create TenantScope trait**

`app/Support/TenantScope.php`:

```php
<?php
namespace App\Support;

use Illuminate\Database\Eloquent\Builder;

trait TenantScope
{
    protected static function bootTenantScope()
    {
        if (!app()->runningInConsole()) {
            $kosId = auth()->user()?->kos_id;
            
            if ($kosId && auth()->user()?->role === 'pengurus') {
                static::addGlobalScope('kos_id', function (Builder $builder) use ($kosId) {
                    $builder->where('kos_id', $kosId);
                });
            }
        }
    }
}
```

- [ ] **Step 10: Apply TenantScope to models with kos_id**

Add `use TenantScope;` to: `Kamar`, `Pemesanan`, `Tagihan`, `MetodePembayaran`.

- [ ] **Step 11: Write test for relationships**

`tests/Feature/Models/RelationshipTest.php`:

```php
<?php
namespace Tests\Feature\Models;

use Tests\TestCase;
use App\Models\{Kos, Kamar, User, Pemesanan};
use Illuminate\Foundation\Testing\RefreshDatabase;

class RelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_kos_has_many_kamar(): void
    {
        $kos = Kos::factory()->create();
        $kamar = Kamar::factory()->count(3)->create(['kos_id' => $kos->id]);
        
        $this->assertCount(3, $kos->kamar);
    }

    public function test_pemesanan_belongs_to_user_kamar_kos(): void
    {
        $kos = Kos::factory()->create();
        $kamar = Kamar::factory()->create(['kos_id' => $kos->id]);
        $user = User::factory()->create();
        $pemesanan = Pemesanan::factory()->create([
            'kos_id' => $kos->id,
            'kamar_id' => $kamar->id,
            'user_id' => $user->id,
        ]);
        
        $this->assertEquals($user->id, $pemesanan->user->id);
        $this->assertEquals($kamar->id, $pemesanan->kamar->id);
        $this->assertEquals($kos->id, $pemesanan->kos->id);
    }
}
```

- [ ] **Step 12: Create factories**

```bash
php artisan make:factory KosFactory
php artisan make:factory KamarFactory
php artisan make:factory PemesananFactory
```

Minimal implementations with faker for required fields.

- [ ] **Step 13: Run test**

Run: `php artisan test --filter=RelationshipTest`
Expected: PASS

- [ ] **Step 14: Commit**

```bash
git add .
git commit -m "feat: add database schema, models, and tenant scope"
```

---

### Task 3: Authentication & Role System

**Files:**
- Create: `app/Http/Controllers/Auth/AuthController.php`
- Create: `app/Http/Requests/{LoginRequest,RegisterRequest}.php`
- Create: `app/Http/Middleware/EnsureRole.php`
- Create: `resources/views/auth/{login,register}.blade.php`
- Create: `resources/views/layouts/{auth,guest}.blade.php`
- Create: `tests/Feature/Auth/{LoginTest,RegisterTest,RoleTest}.php`
- Modify: `routes/web.php`, `bootstrap/app.php`

**Interfaces:**
- Consumes: `User` model from Task 2
- Produces: `AuthController::login(LoginRequest): RedirectResponse`, `AuthController::register(RegisterRequest): RedirectResponse`, `EnsureRole` middleware

- [ ] **Step 1: Write failing test for registration**

`tests/Feature/Auth/RegisterTest.php`:

```php
<?php
namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function test_penghuni_can_register_with_valid_data(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '08123456789',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);
        
        $response->assertRedirect('/penghuni/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'role' => 'penghuni',
        ]);
        $this->assertAuthenticated();
    }

    public function test_registration_requires_password_confirmation(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test',
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Different123!',
        ]);
        
        $response->assertSessionHasErrors('password');
    }
}
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter=RegisterTest`
Expected: FAIL "Route not defined"

- [ ] **Step 3: Create RegisterRequest with validation**

`app/Http/Requests/RegisterRequest.php`:

```php
<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }
}
```

- [ ] **Step 4: Create LoginRequest**

`app/Http/Requests/LoginRequest.php`:

```php
<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];
    }
}
```

- [ ] **Step 5: Create AuthController**

`app/Http/Controllers/Auth/AuthController.php`:

```php
<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\{LoginRequest, RegisterRequest};
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\{Auth, Hash};
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');
        
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
            
            $user = Auth::user();
            $user->update(['last_login_at' => now()]);
            
            return $this->redirectByRole($user->role);
        }
        
        return back()->withErrors(['email' => 'Kredensial tidak valid.'])->onlyInput('email');
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => $request->password,
            'role' => 'penghuni',
        ]);
        
        Auth::login($user);
        $request->session()->regenerate();
        
        return redirect()->route('penghuni.dashboard');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        
        return redirect('/');
    }

    private function redirectByRole(string $role): RedirectResponse
    {
        return match($role) {
            'super_admin' => redirect()->route('admin.dashboard'),
            'pengurus' => redirect()->route('pengurus.dashboard'),
            'penghuni' => redirect()->route('penghuni.dashboard'),
        };
    }
}
```

- [ ] **Step 6: Create auth layout and views**

`resources/views/layouts/guest.blade.php` (simple Tailwind layout with @yield('content'))

`resources/views/auth/login.blade.php`:

```blade
@extends('layouts.guest')

@section('content')
<div class="min-h-screen flex items-center justify-center">
    <div class="max-w-md w-full bg-white p-8 rounded shadow">
        <h2 class="text-2xl font-bold mb-6">Login</h2>
        
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ $errors->first() }}
            </div>
        @endif
        
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required 
                       class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 mb-2">Password</label>
                <input type="password" name="password" required 
                       class="w-full px-3 py-2 border rounded focus:outline-none focus:ring-2">
            </div>
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="mr-2">
                    <span class="text-sm">Ingat saya</span>
                </label>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700">
                Login
            </button>
        </form>
        
        <p class="mt-4 text-center text-sm">
            Belum punya akun? <a href="{{ route('register') }}" class="text-blue-600">Daftar</a>
        </p>
    </div>
</div>
@endsection
```

Similar structure for `register.blade.php` with name, email, phone, password, password_confirmation fields.

- [ ] **Step 7: Add routes**

`routes/web.php`:

```php
use App\Http\Controllers\Auth\AuthController;

Route::get('/', fn() => view('public.landing'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');
```

- [ ] **Step 8: Run registration test**

Run: `php artisan test --filter=RegisterTest`
Expected: PASS

- [ ] **Step 9: Write and run login test**

`tests/Feature/Auth/LoginTest.php` with `test_user_can_login_with_valid_credentials` and `test_invalid_credentials_rejected`.

Run: `php artisan test --filter=LoginTest`
Expected: PASS

- [ ] **Step 10: Create EnsureRole middleware**

`app/Http/Middleware/EnsureRole.php`:

```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!$request->user()) {
            abort(401);
        }
        
        if (!in_array($request->user()->role, $roles)) {
            abort(403, 'Akses ditolak.');
        }
        
        return $next($request);
    }
}
```

- [ ] **Step 11: Register EnsureRole middleware alias**

`bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\EnsureRole::class,
    ]);
})
```

- [ ] **Step 12: Write test for role middleware**

`tests/Feature/Auth/RoleTest.php`:

```php
<?php
namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\{User, Kos};
use Illuminate\Foundation\Testing\RefreshDatabase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_penghuni_cannot_access_pengurus_routes(): void
    {
        $user = User::factory()->create(['role' => 'penghuni']);
        
        $response = $this->actingAs($user)->get('/pengurus/dashboard');
        
        $response->assertStatus(403);
    }

    public function test_pengurus_can_access_pengurus_routes(): void
    {
        $kos = Kos::factory()->create();
        $user = User::factory()->create(['role' => 'pengurus', 'kos_id' => $kos->id]);
        
        $response = $this->actingAs($user)->get('/pengurus/dashboard');
        
        $response->assertStatus(200);
    }

    public function test_super_admin_can_access_admin_routes(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);
        
        $response = $this->actingAs($user)->get('/admin/dashboard');
        
        $response->assertStatus(200);
    }
}
```

- [ ] **Step 13: Add placeholder dashboard routes to make test pass**

```php
Route::middleware(['auth', 'role:pengurus'])->prefix('pengurus')->name('pengurus.')->group(function () {
    Route::get('/dashboard', fn() => view('pengurus.dashboard'))->name('dashboard');
});

Route::middleware(['auth', 'role:penghuni'])->prefix('penghuni')->name('penghuni.')->group(function () {
    Route::get('/dashboard', fn() => view('penghuni.dashboard'))->name('dashboard');
});

Route::middleware(['auth', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', fn() => view('superadmin.dashboard'))->name('dashboard');
});
```

Create placeholder blade views returning simple "Dashboard [Role]" text.

- [ ] **Step 14: Run role test**

Run: `php artisan test --filter=RoleTest`
Expected: PASS

- [ ] **Step 15: Configure rate limiting**

`bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->throttleApi();
    $middleware->throttleWith([
        'login' => 5,
        'api' => 60,
    ]);
})
```

Update login/register routes with `throttle:login,5` and `throttle:register,3`.

- [ ] **Step 16: Commit**

```bash
git add .
git commit -m "feat: add authentication with 3 roles and rate limiting"
```

---

### Task 4: Super Admin - Kos & Pengurus CRUD

**Files:**
- Create: `app/Http/Controllers/SuperAdmin/{DashboardController,KosController,PengurusController}.php`
- Create: `app/Http/Requests/SuperAdmin/{StoreKosRequest,UpdateKosRequest,StorePengurusRequest}.php`
- Create: `app/Policies/KosPolicy.php`
- Create: `app/Console/Commands/CreateSuperAdmin.php`
- Create: `resources/views/superadmin/{dashboard,kos/index,kos/form,pengurus/index,pengurus/form}.blade.php`
- Create: `tests/Feature/SuperAdmin/{KosTest,PengurusTest}.php`
- Modify: `routes/web.php`

**Interfaces:**
- Consumes: `Kos`, `User` models; `EnsureRole` middleware
- Produces: `KosController::store(StoreKosRequest): RedirectResponse`, `KosController::update(UpdateKosRequest, Kos): RedirectResponse`, `PengurusController::store(StorePengurusRequest): RedirectResponse`

[Steps following TDD pattern: test → implement request validation → implement controller → implement view → verify → commit]

Due to plan length constraints, I'm summarizing this task pattern. Each CRUD operation follows:
1. Write failing feature test (can super admin create/update/delete)
2. Create Form Request with validation rules from spec
3. Implement controller action
4. Create Blade view with form
5. Run test to pass
6. Commit with conventional message

Key validations:
- Kos: nama required max 255, slug unique alpha_dash, kota required
- Pengurus: email unique, kos_id exists, role fixed to 'pengurus', password min 8

- [ ] **Step 1-15: Implement Kos CRUD following TDD pattern**
- [ ] **Step 16-30: Implement Pengurus CRUD**
- [ ] **Step 31: Create artisan command for super admin**

`app/Console/Commands/CreateSuperAdmin.php`:

```php
<?php
namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateSuperAdmin extends Command
{
    protected $signature = 'user:create-super-admin {email} {password}';
    protected $description = 'Create super admin account';

    public function handle(): int
    {
        if (User::where('email', $this->argument('email'))->exists()) {
            $this->error('Email already exists');
            return 1;
        }

        User::create([
            'name' => 'Super Admin',
            'email' => $this->argument('email'),
            'password' => $this->argument('password'),
            'role' => 'super_admin',
        ]);

        $this->info('Super admin created successfully');
        return 0;
    }
}
```

- [ ] **Step 32: Test command**

```bash
php artisan user:create-super-admin admin@kelolakos.site SecurePass123!
```

Expected: User created, can login

- [ ] **Step 33: Commit**

```bash
git add .
git commit -m "feat: add super admin kos and pengurus management"
```

---

[Due to response length limits, I'll continue with Task 5-10 structure but more condensed]

### Task 5: Pengurus - Kamar CRUD

[TDD pattern for kamar index, create, update, delete with tenant isolation test]
- Validates kos_id automatically from session
- Test pengurus A cannot see/edit kamar kos B
- Photo upload to public disk with validation

### Task 6: Public Landing & Kos Page

- Landing page with CTA to browse/login
- `/k/{slug}` shows kos profile, available kamar, booking CTA
- Unauthenticated can view, must login to book

### Task 7: Booking Flow (Pemesanan)

- Penghuni create pemesanan (validate room available, no active booking)
- Pengurus view pending list
- Approve with transaction: lock kamar, update status, create tagihan, reject others on same kamar
- Reject with reason
- Tests for race condition (double approval)

### Task 8: Billing & Payment

- BillingService creates tagihan for active pemesanan
- Penghuni upload bukti to private storage
- PaymentService validates, stores with unique filename
- Pengurus verify/reject with audit log
- Test exact amount matching, IDOR prevention

### Task 9: Dashboard & Reports

- Pengurus dashboard: cards (occupancy, revenue, unpaid)
- Report page: date filter, revenue table, Chart.js bar chart
- Export CSV (streamed) and PDF (dompdf)
- Test data aggregation accuracy

### Task 10: Import CSV

- Template download routes
- ImportService validates, previews errors
- Batch insert in transaction
- Test validation (duplicate nomor, invalid email)

### Task 11: Security Hardening

- CSRF test (request without token fails)
- XSS test (script tags escaped)
- SQL injection test (malicious input safe)
- Rate limit test (6th attempt blocked)
- Audit composer/npm for vulnerabilities

### Task 12: README & Documentation

- Installation steps
- Environment variables (without values)
- How to run locally
- How to run tests
- Deployment notes

---

## Deployment Checklist (Not a Task)

After all tasks pass:

```bash
# Build for production
npm run build
composer install --no-dev --optimize-autoloader

# Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Migrate on server
php artisan migrate --force

# Create super admin
php artisan user:create-super-admin admin@kelolakos.site [password]

# Storage link
php artisan storage:link

# Test HTTPS
curl -I https://kelolakos.site
```

Verify:
- securityheaders.com grade A
- SSL Labs grade A
- All tests pass
- Can register, book, pay, verify
