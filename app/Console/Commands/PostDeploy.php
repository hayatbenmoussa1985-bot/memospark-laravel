<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Post-deployment health check and setup.
 *
 * Verifies that the application is properly configured and all
 * required services are accessible. Run after each deployment.
 *
 * Usage:
 *   php artisan app:post-deploy
 */
class PostDeploy extends Command
{
    protected $signature = 'app:post-deploy';
    protected $description = 'Run post-deployment health checks and setup';

    public function handle(): int
    {
        $this->info('╔═══════════════════════════════════════════════════╗');
        $this->info('║     MemoSpark Post-Deployment Check               ║');
        $this->info('╚═══════════════════════════════════════════════════╝');
        $this->newLine();

        $issues = 0;

        // 1. Check environment
        $issues += $this->checkEnvironment();

        // 2. Check database connection
        $issues += $this->checkDatabase();

        // 3. Check required tables
        $issues += $this->checkTables();

        // 4. Check storage directories
        $issues += $this->checkStorage();

        // 5. Check key configs
        $issues += $this->checkConfigs();

        // 6. Ensure default data exists
        $issues += $this->ensureDefaults();

        // Summary
        $this->newLine();
        if ($issues === 0) {
            $this->info('✅ All checks passed! Application is ready.');
        } else {
            $this->warn("⚠️ Found {$issues} issue(s) to resolve.");
        }

        return $issues === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    private function checkEnvironment(): int
    {
        $this->info('━━━ Environment ━━━');
        $issues = 0;

        // APP_KEY
        if (empty(config('app.key'))) {
            $this->error('  ✗ APP_KEY is not set — run: php artisan key:generate');
            $issues++;
        } else {
            $this->info('  ✓ APP_KEY is set');
        }

        // APP_ENV
        $env = config('app.env');
        $this->info("  ✓ APP_ENV = {$env}");

        // Debug should be off in production
        if ($env === 'production' && config('app.debug')) {
            $this->warn('  ✗ APP_DEBUG is true in production — set to false');
            $issues++;
        } else {
            $this->info('  ✓ APP_DEBUG = ' . (config('app.debug') ? 'true' : 'false'));
        }

        // APP_URL
        $url = config('app.url');
        $this->info("  ✓ APP_URL = {$url}");

        return $issues;
    }

    private function checkDatabase(): int
    {
        $this->newLine();
        $this->info('━━━ Database ━━━');
        $issues = 0;

        try {
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
            $this->info("  ✓ Connected to MySQL: {$dbName}");
        } catch (\Exception $e) {
            $this->error('  ✗ MySQL connection failed: ' . $e->getMessage());
            $issues++;
            return $issues;
        }

        // Check pending migrations
        try {
            $exitCode = \Illuminate\Support\Facades\Artisan::call('migrate:status', [], new \Symfony\Component\Console\Output\BufferedOutput());
            $output = \Illuminate\Support\Facades\Artisan::output();
            $pendingCount = substr_count($output, 'Pending');

            if ($pendingCount > 0) {
                $this->warn("  ✗ {$pendingCount} pending migration(s) — run: php artisan migrate");
                $issues++;
            } else {
                $this->info('  ✓ All migrations are up to date');
            }
        } catch (\Exception $e) {
            $this->warn('  ? Could not check migrations: ' . $e->getMessage());
        }

        return $issues;
    }

    private function checkTables(): int
    {
        $this->newLine();
        $this->info('━━━ Required Tables ━━━');
        $issues = 0;

        $requiredTables = [
            'users', 'decks', 'cards', 'card_progress', 'study_sessions',
            'review_logs', 'categories', 'badges', 'user_badges', 'messages',
            'notifications', 'subscriptions', 'subscription_plans',
            'parent_child', 'activity_logs', 'audit_logs', 'app_settings',
            'blog_posts', 'reports', 'folders', 'personal_access_tokens',
        ];

        $missing = [];
        foreach ($requiredTables as $table) {
            if (!Schema::hasTable($table)) {
                $missing[] = $table;
            }
        }

        if (count($missing) > 0) {
            $this->warn("  ✗ Missing tables: " . implode(', ', $missing));
            $issues++;
        } else {
            $this->info("  ✓ All " . count($requiredTables) . " required tables exist");
        }

        return $issues;
    }

    private function checkStorage(): int
    {
        $this->newLine();
        $this->info('━━━ Storage ━━━');
        $issues = 0;

        // Check storage link
        if (!file_exists(public_path('storage'))) {
            $this->warn('  ✗ Storage link missing — run: php artisan storage:link');
            $issues++;
        } else {
            $this->info('  ✓ Storage link exists');
        }

        // Check writable directories
        $dirs = [
            storage_path('app'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
        ];

        foreach ($dirs as $dir) {
            if (!is_writable($dir)) {
                $shortDir = str_replace(base_path() . '/', '', $dir);
                $this->warn("  ✗ Not writable: {$shortDir}");
                $issues++;
            }
        }

        if ($issues === 0) {
            $this->info('  ✓ All storage directories are writable');
        }

        return $issues;
    }

    private function checkConfigs(): int
    {
        $this->newLine();
        $this->info('━━━ Configuration ━━━');
        $issues = 0;

        // Mail
        $mailer = config('mail.default');
        if ($mailer === 'log') {
            $this->warn('  ⚠ Mail driver is "log" — emails will not be sent');
        } else {
            $this->info("  ✓ Mail driver: {$mailer}");
        }

        // Session
        $sessionDriver = config('session.driver');
        $this->info("  ✓ Session driver: {$sessionDriver}");

        // Cache
        $cacheDriver = config('cache.default');
        $this->info("  ✓ Cache driver: {$cacheDriver}");

        return $issues;
    }

    private function ensureDefaults(): int
    {
        $this->newLine();
        $this->info('━━━ Default Data ━━━');
        $issues = 0;

        // Check that super admin exists
        $superAdmin = DB::table('users')->where('role', 'super_admin')->first();
        if (!$superAdmin) {
            $this->warn('  ✗ No super_admin user — run seeders or create manually');
            $issues++;
        } else {
            $this->info("  ✓ Super admin exists: {$superAdmin->email}");
        }

        // Check that permissions are seeded
        $permCount = DB::table('permissions')->count();
        if ($permCount === 0) {
            $this->warn('  ✗ Permissions not seeded — run: php artisan db:seed --class=PermissionSeeder');
            $issues++;
        } else {
            $this->info("  ✓ {$permCount} permissions seeded");
        }

        // Check subscription plans
        $planCount = DB::table('subscription_plans')->count();
        if ($planCount === 0) {
            $this->warn('  ⚠ No subscription plans — run: php artisan db:seed --class=SubscriptionPlanSeeder');
        } else {
            $this->info("  ✓ {$planCount} subscription plans");
        }

        // Row counts
        $this->newLine();
        $this->info('  Current data:');
        $tables = ['users', 'decks', 'cards', 'card_progress', 'badges', 'categories'];
        foreach ($tables as $table) {
            try {
                $count = DB::table($table)->count();
                $this->info(sprintf("    %-16s %s", $table, number_format($count)));
            } catch (\Exception $e) {
                // Table might not exist
            }
        }

        return $issues;
    }
}
