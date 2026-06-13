<?php

namespace Tests\Feature;

use App\Actions\Installer\CompleteInstallation;
use App\Actions\Installer\TestDatabaseConnection;
use App\Services\Installer\InstallState;
use RuntimeException;
use Tests\TestCase;

class InstallerTest extends TestCase
{
    private string $envPath;

    private ?string $originalEnv = null;

    private string $lockPath;

    private string $recoveryPath;

    private bool $hadLock = false;

    private ?string $originalLock = null;

    private bool $hadRecovery = false;

    private ?string $originalRecovery = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->envPath = base_path('.env');
        $this->lockPath = storage_path('app/installed.lock');
        $this->recoveryPath = storage_path('app/installer-recovery.flag');
        $this->originalEnv = file_exists($this->envPath) ? file_get_contents($this->envPath) : null;
        $this->hadLock = file_exists($this->lockPath);
        $this->originalLock = $this->hadLock ? file_get_contents($this->lockPath) : null;
        $this->hadRecovery = file_exists($this->recoveryPath);
        $this->originalRecovery = $this->hadRecovery ? file_get_contents($this->recoveryPath) : null;

        if ($this->hadLock) {
            unlink($this->lockPath);
        }

        if ($this->hadRecovery) {
            unlink($this->recoveryPath);
        }
    }

    protected function tearDown(): void
    {
        if ($this->originalEnv === null) {
            if (file_exists($this->envPath)) {
                unlink($this->envPath);
            }
        } else {
            file_put_contents($this->envPath, $this->originalEnv);
        }

        if ($this->hadLock) {
            file_put_contents($this->lockPath, $this->originalLock ?? '');
        } elseif (file_exists($this->lockPath)) {
            unlink($this->lockPath);
        }

        if ($this->hadRecovery) {
            file_put_contents($this->recoveryPath, $this->originalRecovery ?? '');
        } elseif (file_exists($this->recoveryPath)) {
            unlink($this->recoveryPath);
        }

        parent::tearDown();
    }

    public function test_readiness_page_renders(): void
    {
        $this->get(route('install.readiness'))
            ->assertOk()
            ->assertSee('System Readiness')
            ->assertSee('MySQL');
    }

    public function test_uninstalled_root_redirects_to_installer(): void
    {
        $this->get(route('home'))
            ->assertRedirect(route('install.readiness'));
    }

    public function test_database_page_renders(): void
    {
        $this->get(route('install.database'))
            ->assertOk()
            ->assertSee('Database Setup')
            ->assertSee('Missing database drivers');
    }

    public function test_missing_env_recreates_installer_state(): void
    {
        unlink($this->envPath);

        $this->get(route('install.readiness'))->assertOk();

        $this->assertFileExists($this->envPath);
        $this->assertNotEmpty($this->envValue('APP_KEY'));
    }

    public function test_installer_uses_relative_links_and_vite_assets(): void
    {
        $layout = file_get_contents(resource_path('views/components/installer/layout.blade.php'));

        $this->assertStringContainsString('@vite', $layout);
        $this->assertStringContainsString("route('install.database')", file_get_contents(resource_path('views/installer/readiness.blade.php')));
        $this->assertStringNotContainsString('http://localhost', $layout);
        $this->assertStringNotContainsString('127.0.0.1', $layout);
    }

    public function test_missing_database_driver_shows_friendly_error(): void
    {
        $this->app->bind(TestDatabaseConnection::class, fn () => new class extends TestDatabaseConnection
        {
            public function __construct() {}

            public function handle(array $credentials): void
            {
                throw new RuntimeException('The pdo_sqlite PHP extension is not enabled for SQLite.');
            }
        });

        $this->from(route('install.database'))
            ->post(route('install.database.store'), [
                'connection' => 'sqlite',
                'database' => database_path('database.sqlite'),
            ])
            ->assertRedirect(route('install.database'))
            ->assertSessionHasErrors('database');
    }

    public function test_validation_errors_show_error_summary(): void
    {
        $this->from(route('install.database'))
            ->post(route('install.database.store'), ['connection' => 'mysql'])
            ->assertRedirect(route('install.database'));

        $this->get(route('install.database'))
            ->assertSee('Please fix the highlighted fields.');
    }

    public function test_database_credentials_persist_to_env_after_successful_test(): void
    {
        $this->app->bind(TestDatabaseConnection::class, fn () => new class extends TestDatabaseConnection
        {
            public function __construct() {}

            public function handle(array $credentials): void {}
        });

        $this->post(route('install.database.store'), [
            'connection' => 'mysql',
            'host' => 'db.internal',
            'port' => '3306',
            'database' => 'tempmail',
            'username' => 'tempmail_user',
            'password' => 'secret-value',
        ])->assertRedirect(route('install.admin'));

        $this->assertSame('mysql', $this->envValue('DB_CONNECTION'));
        $this->assertSame('db.internal', $this->envValue('DB_HOST'));
        $this->assertSame('tempmail', $this->envValue('DB_DATABASE'));
        $this->assertSame('tempmail_user', $this->envValue('DB_USERNAME'));
        $this->assertSame('secret-value', $this->envValue('DB_PASSWORD'));
    }

    public function test_admin_creation_runs_install_action_and_writes_lock(): void
    {
        $this->app->bind(TestDatabaseConnection::class, fn () => new class extends TestDatabaseConnection
        {
            public function __construct() {}

            public function handle(array $credentials): void {}
        });

        $this->app->bind(CompleteInstallation::class, fn () => new class extends CompleteInstallation
        {
            public static bool $called = false;

            public function __construct() {}

            public function handle(array $admin): void
            {
                self::$called = true;
                app(InstallState::class)->lock();
            }
        });

        $this->post(route('install.database.store'), [
            'connection' => 'mysql',
            'host' => 'db.internal',
            'port' => '3306',
            'database' => 'tempmail',
            'username' => 'tempmail_user',
            'password' => 'secret-value',
        ])->assertRedirect(route('install.admin'));

        $this->post(route('install.admin.store'), [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'Secure123!',
            'password_confirmation' => 'Secure123!',
        ])
            ->assertRedirect(route('login'));

        $this->assertFileExists($this->lockPath);
    }

    public function test_installed_app_redirects_away_from_installer(): void
    {
        app(InstallState::class)->lock();

        $this->get(route('install.readiness'))
            ->assertRedirect(route('login'));
    }

    public function test_stale_environment_recovery_does_not_reopen_installer_when_lock_exists(): void
    {
        app(InstallState::class)->lock();
        file_put_contents($this->recoveryPath, 'recovered_at=testing');

        $this->get(route('install.readiness'))
            ->assertRedirect(route('login'));
    }

    public function test_stale_environment_recovery_does_not_block_installed_pages(): void
    {
        app(InstallState::class)->lock();
        file_put_contents($this->recoveryPath, 'recovered_at=testing');

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in');
    }

    public function test_environment_recovery_blocks_non_installer_pages_without_install_lock(): void
    {
        file_put_contents($this->recoveryPath, 'recovered_at=testing');

        $this->get(route('login'))
            ->assertRedirect(route('install.readiness'));
    }

    public function test_successful_lock_clears_environment_recovery_state(): void
    {
        file_put_contents($this->recoveryPath, 'recovered_at=testing');

        app(InstallState::class)->lock();

        $this->assertFileExists($this->lockPath);
        $this->assertFileDoesNotExist($this->recoveryPath);
    }

    public function test_login_page_works_after_install(): void
    {
        app(InstallState::class)->lock();

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Sign in')
            ->assertSee('Forgot password?');
    }

    public function test_no_server_driven_component_dependency(): void
    {
        $composer = file_get_contents(base_path('composer.json'));
        $package = file_get_contents(base_path('package.json'));
        $disallowed = 'live'.'wire';

        $this->assertStringNotContainsString($disallowed, strtolower($composer.$package));
    }

    public function test_no_alpine_cdn(): void
    {
        foreach ($this->bladeFiles() as $file) {
            $this->assertStringNotContainsString('cdn.jsdelivr.net/npm/alpine', strtolower(file_get_contents($file)));
            $this->assertStringNotContainsString('unpkg.com/alpine', strtolower(file_get_contents($file)));
        }
    }

    public function test_no_hardcoded_loopback_address(): void
    {
        $paths = [
            app_path(),
            config_path(),
            resource_path('views'),
            base_path('.env.example'),
            base_path('routes'),
        ];

        foreach ($paths as $path) {
            $files = is_file($path) ? [$path] : $this->phpAndBladeFiles($path);

            foreach ($files as $file) {
                $this->assertStringNotContainsString('127.0.0.1', file_get_contents($file), $file);
                $this->assertStringNotContainsString('http://localhost', file_get_contents($file), $file);
            }
        }
    }

    private function envValue(string $key): ?string
    {
        foreach (file($this->envPath, FILE_IGNORE_NEW_LINES) ?: [] as $line) {
            if (str_starts_with($line, $key.'=')) {
                return trim(substr($line, strlen($key) + 1), "\"'");
            }
        }

        return null;
    }

    /**
     * @return array<int, string>
     */
    private function bladeFiles(): array
    {
        return $this->phpAndBladeFiles(resource_path('views'));
    }

    /**
     * @return array<int, string>
     */
    private function phpAndBladeFiles(string $directory): array
    {
        $files = [];
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if ($file->isFile() && preg_match('/\.(php|blade\.php|css|js)$/', $file->getPathname())) {
                $files[] = $file->getPathname();
            }
        }

        return $files;
    }
}
