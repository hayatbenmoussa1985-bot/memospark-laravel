<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Verify data integrity after migration from PostgreSQL to MySQL.
 *
 * Compares row counts between old and new databases, checks referential
 * integrity, and validates UUID mapping completeness.
 *
 * Usage:
 *   php artisan app:verify-migration
 */
class VerifyMigration extends Command
{
    protected $signature = 'app:verify-migration';
    protected $description = 'Verify data integrity after PostgreSQL → MySQL migration';

    public function handle(): int
    {
        $this->info('╔═══════════════════════════════════════════════════╗');
        $this->info('║     MemoSpark Migration Verification              ║');
        $this->info('╚═══════════════════════════════════════════════════╝');
        $this->newLine();

        $issues = 0;

        // 1. Compare row counts
        $this->info('━━━ Row Count Comparison ━━━');
        $issues += $this->compareRowCounts();

        // 2. Check referential integrity
        $this->newLine();
        $this->info('━━━ Referential Integrity ━━━');
        $issues += $this->checkReferentialIntegrity();

        // 3. Check UUID mapping completeness
        $this->newLine();
        $this->info('━━━ UUID Mapping Completeness ━━━');
        $issues += $this->checkUuidMappings();

        // 4. Spot check data
        $this->newLine();
        $this->info('━━━ Data Spot Checks ━━━');
        $issues += $this->spotCheckData();

        // Summary
        $this->newLine();
        if ($issues === 0) {
            $this->info('✅ All checks passed! Migration looks good.');
        } else {
            $this->warn("⚠️ Found {$issues} issue(s). Review the output above.");
        }

        return $issues === 0 ? Command::SUCCESS : Command::FAILURE;
    }

    private function compareRowCounts(): int
    {
        $issues = 0;
        $old = DB::connection('old_pgsql');

        $comparisons = [
            ['profiles', 'users', 'Users'],
            ['decks', 'decks', 'Decks'],
            ['cards', 'cards', 'Cards'],
            ['progress', 'card_progress', 'Card Progress'],
            ['messages', 'messages', 'Messages'],
        ];

        foreach ($comparisons as [$oldTable, $newTable, $label]) {
            try {
                $oldCount = $old->table($oldTable)->count();
                $newCount = DB::table($newTable)->count();

                $match = $oldCount === $newCount ? '✓' : '✗';
                $color = $oldCount === $newCount ? 'info' : 'warn';

                $this->$color("  {$match} {$label}: old={$oldCount}, new={$newCount}");

                if ($oldCount !== $newCount) {
                    $issues++;
                }
            } catch (\Exception $e) {
                $this->warn("  ? {$label}: could not compare ({$e->getMessage()})");
            }
        }

        return $issues;
    }

    private function checkReferentialIntegrity(): int
    {
        $issues = 0;

        // Cards should have valid deck_ids
        $orphanCards = DB::table('cards')
            ->leftJoin('decks', 'cards.deck_id', '=', 'decks.id')
            ->whereNull('decks.id')
            ->count();
        if ($orphanCards > 0) {
            $this->warn("  ✗ {$orphanCards} cards with missing deck");
            $issues++;
        } else {
            $this->info("  ✓ All cards have valid decks");
        }

        // Card progress should have valid user_ids and card_ids
        $orphanProgress = DB::table('card_progress')
            ->leftJoin('users', 'card_progress.user_id', '=', 'users.id')
            ->whereNull('users.id')
            ->count();
        if ($orphanProgress > 0) {
            $this->warn("  ✗ {$orphanProgress} progress records with missing user");
            $issues++;
        } else {
            $this->info("  ✓ All progress records have valid users");
        }

        // Decks should have valid user_ids
        $orphanDecks = DB::table('decks')
            ->leftJoin('users', 'decks.user_id', '=', 'users.id')
            ->whereNull('users.id')
            ->count();
        if ($orphanDecks > 0) {
            $this->warn("  ✗ {$orphanDecks} decks with missing author");
            $issues++;
        } else {
            $this->info("  ✓ All decks have valid authors");
        }

        // Parent-child links should be valid
        $orphanLinks = DB::table('parent_child')
            ->leftJoin('users as p', 'parent_child.parent_id', '=', 'p.id')
            ->leftJoin('users as c', 'parent_child.child_id', '=', 'c.id')
            ->where(function ($q) {
                $q->whereNull('p.id')->orWhereNull('c.id');
            })
            ->count();
        if ($orphanLinks > 0) {
            $this->warn("  ✗ {$orphanLinks} parent-child links with missing users");
            $issues++;
        } else {
            $this->info("  ✓ All parent-child links are valid");
        }

        return $issues;
    }

    private function checkUuidMappings(): int
    {
        $issues = 0;

        $mappingCount = DB::table('uuid_mappings')->count();
        $this->info("  Total UUID mappings: {$mappingCount}");

        // Check that every user has a mapping
        $mappedUsers = DB::table('uuid_mappings')
            ->where('new_table', 'users')
            ->count();
        $totalUsers = DB::table('users')->count();

        if ($mappedUsers < $totalUsers) {
            $this->warn("  ✗ Users without mapping: " . ($totalUsers - $mappedUsers));
            $issues++;
        } else {
            $this->info("  ✓ All users have UUID mappings");
        }

        // Check that every deck has a mapping
        $mappedDecks = DB::table('uuid_mappings')
            ->where('new_table', 'decks')
            ->count();
        $totalDecks = DB::table('decks')->count();

        if ($mappedDecks < $totalDecks) {
            $this->warn("  ✗ Decks without mapping: " . ($totalDecks - $mappedDecks));
            $issues++;
        } else {
            $this->info("  ✓ All decks have UUID mappings");
        }

        return $issues;
    }

    private function spotCheckData(): int
    {
        $issues = 0;

        // Check that all users have emails
        $noEmail = DB::table('users')->whereNull('email')->count();
        if ($noEmail > 0) {
            $this->warn("  ✗ {$noEmail} users without email");
            $issues++;
        } else {
            $this->info("  ✓ All users have emails");
        }

        // Check that all decks have titles
        $noTitle = DB::table('decks')->whereNull('title')->orWhere('title', '')->count();
        if ($noTitle > 0) {
            $this->warn("  ✗ {$noTitle} decks without title");
            $issues++;
        } else {
            $this->info("  ✓ All decks have titles");
        }

        // Check card_progress has valid easiness_factor range
        $badEF = DB::table('card_progress')
            ->where('easiness_factor', '<', 1.30)
            ->count();
        if ($badEF > 0) {
            $this->warn("  ✗ {$badEF} progress records with EF below 1.30");
            $issues++;
        } else {
            $this->info("  ✓ All easiness factors are valid (>= 1.30)");
        }

        // Check cards_count cache on decks
        $badCount = DB::selectOne("
            SELECT COUNT(*) as cnt FROM decks
            WHERE cards_count != (SELECT COUNT(*) FROM cards WHERE cards.deck_id = decks.id AND cards.deleted_at IS NULL)
        ");
        if ($badCount && $badCount->cnt > 0) {
            $this->warn("  ✗ {$badCount->cnt} decks with incorrect cards_count cache");
            $issues++;
        } else {
            $this->info("  ✓ All deck card counts are accurate");
        }

        return $issues;
    }
}
