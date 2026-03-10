<?php

namespace App\Console\Commands;

use App\Enums\UserRole;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Migrate data from old PostgreSQL database to new MySQL database.
 *
 * Old schema: UUID-based PostgreSQL (Supabase/Neon on o2switch)
 * New schema: BIGINT AI + UUID columns on MySQL
 *
 * Mapping table: uuid_mappings stores old_uuid → new_id + new_uuid
 * for iOS app transition.
 *
 * Usage:
 *   php artisan app:migrate-data                    # Run all steps
 *   php artisan app:migrate-data --step=users       # Run one step
 *   php artisan app:migrate-data --dry-run          # Preview without writing
 *   php artisan app:migrate-data --fresh            # Truncate new tables first
 */
class MigrateData extends Command
{
    protected $signature = 'app:migrate-data
                            {--step= : Run a specific step (users, decks, cards, progress, badges, messages, activity, subscriptions, all)}
                            {--dry-run : Preview the migration without writing data}
                            {--fresh : Truncate new tables before migrating}';

    protected $description = 'Migrate data from old PostgreSQL database to new MySQL database';

    /**
     * UUID → new ID mapping cache (populated during migration).
     */
    private array $userMap = [];       // old_uuid => new_id
    private array $deckMap = [];       // old_uuid => new_id
    private array $cardMap = [];       // old_uuid => new_id
    private array $badgeMap = [];      // old_uuid => new_id
    private array $childMap = [];      // old_child_uuid => new_user_id
    private array $parentMap = [];     // old_parent_uuid => new_user_id (profile user_id)
    private array $categoryMap = [];   // old_uuid => new_id
    private array $folderMap = [];     // old_uuid => new_id

    private bool $dryRun = false;

    public function handle(): int
    {
        $this->dryRun = $this->option('dry-run');
        $step = $this->option('step') ?? 'all';

        $this->info('╔═══════════════════════════════════════════════════╗');
        $this->info('║     MemoSpark Data Migration: PostgreSQL → MySQL  ║');
        $this->info('╚═══════════════════════════════════════════════════╝');
        $this->newLine();

        if ($this->dryRun) {
            $this->warn('DRY RUN mode — no data will be written.');
            $this->newLine();
        }

        // Test connections
        if (!$this->testConnections()) {
            return Command::FAILURE;
        }

        // Create UUID mapping table if not exists
        $this->createMappingTable();

        // Optionally truncate new tables
        if ($this->option('fresh') && !$this->dryRun) {
            if ($this->confirm('This will DELETE all data in the new database. Continue?')) {
                $this->truncateNewTables();
            } else {
                return Command::FAILURE;
            }
        }

        $steps = [
            'users' => 'migrateUsers',
            'decks' => 'migrateDecks',
            'cards' => 'migrateCards',
            'progress' => 'migrateProgress',
            'badges' => 'migrateBadges',
            'messages' => 'migrateMessages',
            'activity' => 'migrateActivityLogs',
            'subscriptions' => 'migrateSubscriptions',
        ];

        if ($step === 'all') {
            foreach ($steps as $name => $method) {
                $this->runStep($name, $method);
            }
        } elseif (isset($steps[$step])) {
            // Rebuild ID maps from uuid_mappings table before running a single step
            $this->rebuildMapsFromMappingTable();
            $this->runStep($step, $steps[$step]);
        } else {
            $this->error("Unknown step: {$step}. Available: " . implode(', ', array_keys($steps)));
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('Migration complete!');
        $this->showSummary();

        return Command::SUCCESS;
    }

    // ═══════════════════════════════════════════════════
    // Connection & Setup
    // ═══════════════════════════════════════════════════

    private function testConnections(): bool
    {
        $this->info('Testing database connections...');

        try {
            $oldCount = DB::connection('old_pgsql')->table('profiles')->count();
            $this->info("  ✓ Old PostgreSQL: {$oldCount} profiles found");
        } catch (\Exception $e) {
            $this->error("  ✗ Old PostgreSQL connection failed: " . $e->getMessage());
            return false;
        }

        try {
            DB::connection('mysql')->getPdo();
            $this->info("  ✓ New MySQL: connected");
        } catch (\Exception $e) {
            $this->error("  ✗ New MySQL connection failed: " . $e->getMessage());
            return false;
        }

        $this->newLine();
        return true;
    }

    private function createMappingTable(): void
    {
        if ($this->dryRun) return;

        DB::statement("
            CREATE TABLE IF NOT EXISTS uuid_mappings (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                old_uuid CHAR(36) NOT NULL,
                old_table VARCHAR(100) NOT NULL,
                new_table VARCHAR(100) NOT NULL,
                new_id BIGINT UNSIGNED NOT NULL,
                new_uuid CHAR(36) NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE KEY uk_old (old_table, old_uuid),
                INDEX idx_new (new_table, new_id)
            )
        ");
    }

    private function truncateNewTables(): void
    {
        $this->warn('Truncating new tables...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $tables = [
            'uuid_mappings', 'activity_logs', 'audit_logs', 'review_logs',
            'card_progress', 'messages', 'notifications', 'user_badges',
            'deck_favorites', 'deck_folder', 'folders', 'cards', 'decks',
            'parent_child', 'subscriptions', 'admin_permissions', 'badges',
            'categories', 'subscription_plans', 'users',
        ];

        foreach ($tables as $table) {
            try {
                DB::table($table)->truncate();
            } catch (\Exception $e) {
                // Table might not exist yet, skip
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->info('  Done.');
        $this->newLine();
    }

    private function rebuildMapsFromMappingTable(): void
    {
        $mappings = DB::table('uuid_mappings')->get();

        foreach ($mappings as $m) {
            match ($m->old_table) {
                'profiles' => $this->userMap[$m->old_uuid] = $m->new_id,
                'children' => $this->childMap[$m->old_uuid] = $m->new_id,
                'parents' => $this->parentMap[$m->old_uuid] = $m->new_id,
                'decks' => $this->deckMap[$m->old_uuid] = $m->new_id,
                'cards' => $this->cardMap[$m->old_uuid] = $m->new_id,
                'badges' => $this->badgeMap[$m->old_uuid] = $m->new_id,
                'categories' => $this->categoryMap[$m->old_uuid] = $m->new_id,
                'library_folders' => $this->folderMap[$m->old_uuid] = $m->new_id,
                default => null,
            };
        }

        $this->info("Rebuilt maps: " . count($this->userMap) . " users, "
            . count($this->deckMap) . " decks, "
            . count($this->cardMap) . " cards");
    }

    private function runStep(string $name, string $method): void
    {
        $this->newLine();
        $this->info("━━━ Step: {$name} ━━━");
        $start = microtime(true);

        $this->$method();

        $duration = round(microtime(true) - $start, 2);
        $this->info("  Completed in {$duration}s");
    }

    private function storeMapping(string $oldUuid, string $oldTable, string $newTable, int $newId, ?string $newUuid = null): void
    {
        if ($this->dryRun) return;

        DB::table('uuid_mappings')->insertOrIgnore([
            'old_uuid' => $oldUuid,
            'old_table' => $oldTable,
            'new_table' => $newTable,
            'new_id' => $newId,
            'new_uuid' => $newUuid,
        ]);
    }

    // ═══════════════════════════════════════════════════
    // Step 1: Users (profiles + children + parents → users + parent_child)
    // ═══════════════════════════════════════════════════

    private function migrateUsers(): void
    {
        $old = DB::connection('old_pgsql');

        // 1a. Migrate profiles → users
        $profiles = $old->table('profiles')->get();
        $this->info("  Found {$profiles->count()} profiles");

        foreach ($profiles as $profile) {
            $roleMap = [
                'admin' => UserRole::SuperAdmin,
                'moderator' => UserRole::Admin,
                'user' => UserRole::Learner,
                'child' => UserRole::Child,
                'parent' => UserRole::Parent,
            ];

            $role = $roleMap[$profile->role] ?? UserRole::Learner;
            $newUuid = Str::uuid()->toString();

            $data = [
                'uuid' => $newUuid,
                'name' => $profile->full_name ?? 'User',
                'email' => $profile->email,
                'password' => $profile->password_hash ?? null,
                'role' => $role->value,
                'is_active' => ($profile->status ?? 'active') === 'active',
                'locale' => $profile->language ?? 'en',
                'timezone' => 'UTC',
                'avatar_path' => $profile->avatar_url,
                'date_of_birth' => $profile->date_of_birth,
                'school_level' => $profile->school_level,
                'google_id' => null,
                'apple_user_id' => null,
                'email_verified_at' => $profile->created_at, // Assume verified
                'created_at' => $profile->created_at,
                'updated_at' => $profile->updated_at,
            ];

            // Extract Firebase UID for potential Google link
            if ($profile->firebase_uid) {
                $data['google_id'] = $profile->firebase_uid;
            }

            if ($this->dryRun) {
                $this->line("    [DRY] Would insert user: {$profile->email} ({$role->value})");
                continue;
            }

            $newId = DB::table('users')->insertGetId($data);
            $this->userMap[$profile->id] = $newId;
            $this->storeMapping($profile->id, 'profiles', 'users', $newId, $newUuid);
        }

        // 1b. Migrate children table → update existing users or create new
        $children = $old->table('children')->get();
        $this->info("  Found {$children->count()} children records");

        foreach ($children as $child) {
            // children.user_id references profiles.id
            if (isset($this->userMap[$child->user_id])) {
                // This child already exists as a user via profiles, store child mapping
                $this->childMap[$child->id] = $this->userMap[$child->user_id];
                $this->storeMapping($child->id, 'children', 'users', $this->userMap[$child->user_id]);

                // Update the user with child-specific data if needed
                if (!$this->dryRun) {
                    DB::table('users')
                        ->where('id', $this->userMap[$child->user_id])
                        ->update([
                            'name' => $child->name ?: DB::raw('name'),
                            'school_level' => $child->level ?? $child->school_level ?? null,
                            'date_of_birth' => $child->date_of_birth ?? null,
                            'role' => UserRole::Child->value,
                        ]);
                }
            }
        }

        // 1c. Migrate parents table → store parent references
        $parents = $old->table('parents')->get();
        $this->info("  Found {$parents->count()} parent records");

        foreach ($parents as $parent) {
            if (isset($this->userMap[$parent->user_id])) {
                $this->parentMap[$parent->id] = $this->userMap[$parent->user_id];
                $this->storeMapping($parent->id, 'parents', 'users', $this->userMap[$parent->user_id]);

                // Update role to parent
                if (!$this->dryRun) {
                    DB::table('users')
                        ->where('id', $this->userMap[$parent->user_id])
                        ->update(['role' => UserRole::Parent->value]);
                }
            }
        }

        // 1d. Migrate parent_child_links → parent_child
        $links = $old->table('parent_child_links')->get();
        $this->info("  Found {$links->count()} parent-child links");

        foreach ($links as $link) {
            $parentUserId = $this->parentMap[$link->parent_id] ?? null;
            $childUserId = $this->childMap[$link->child_id] ?? null;

            if ($parentUserId && $childUserId && !$this->dryRun) {
                DB::table('parent_child')->insertOrIgnore([
                    'parent_id' => $parentUserId,
                    'child_id' => $childUserId,
                    'relationship' => $link->relation ?? 'parent',
                    'created_at' => $link->created_at,
                ]);
            }
        }

        $this->info("  ✓ Users migrated: " . count($this->userMap));
    }

    // ═══════════════════════════════════════════════════
    // Step 2: Decks (decks → decks, with visibility mapping)
    // ═══════════════════════════════════════════════════

    private function migrateDecks(): void
    {
        $old = DB::connection('old_pgsql');

        // 2a. Migrate categories (if any exist as distinct table)
        $this->migrateCategoriesIfExist($old);

        // 2b. Migrate decks
        $decks = $old->table('decks')->get();
        $this->info("  Found {$decks->count()} decks");

        foreach ($decks as $deck) {
            $authorId = $this->userMap[$deck->author_id] ?? null;
            if (!$authorId) {
                $this->warn("    Skipping deck '{$deck->title}': author not found ({$deck->author_id})");
                continue;
            }

            // Map old visibility
            $visibility = 'private';
            if ($deck->is_public ?? false) {
                $visibility = 'public';
            }
            if (($deck->status ?? '') === 'archived') {
                $visibility = 'private';
            }

            // Map old category string to category_id
            $categoryId = null;
            if (!empty($deck->category)) {
                $categoryId = $this->findOrCreateCategory($deck->category);
            }

            $newUuid = Str::uuid()->toString();

            $data = [
                'uuid' => $newUuid,
                'user_id' => $authorId,
                'title' => $deck->title,
                'description' => $deck->description,
                'category_id' => $categoryId,
                'language' => $this->mapLanguage($deck->language ?? 'Français'),
                'difficulty' => $deck->difficulty ?? 'beginner',
                'visibility' => $visibility,
                'cover_image_path' => $deck->cover_image_url,
                'is_featured' => false,
                'cards_count' => 0, // Will be recalculated
                'is_ai_generated' => false,
                'created_at' => $deck->created_at,
                'updated_at' => $deck->updated_at,
            ];

            if ($this->dryRun) {
                $this->line("    [DRY] Would insert deck: {$deck->title}");
                continue;
            }

            $newId = DB::table('decks')->insertGetId($data);
            $this->deckMap[$deck->id] = $newId;
            $this->storeMapping($deck->id, 'decks', 'decks', $newId, $newUuid);
        }

        // 2c. Migrate library folders → folders
        $this->migrateFolders($old);

        // 2d. Migrate deck_favorites
        $this->migrateDeckFavorites($old);

        $this->info("  ✓ Decks migrated: " . count($this->deckMap));
    }

    private function migrateCategoriesIfExist($old): void
    {
        try {
            // Check if categories table exists in old DB
            $cats = $old->table('information_schema.tables')
                ->where('table_schema', 'public')
                ->where('table_name', 'categories')
                ->exists();

            if ($cats) {
                $categories = $old->table('categories')->get();
                foreach ($categories as $cat) {
                    $newId = DB::table('categories')->insertGetId([
                        'slug' => Str::slug($cat->name ?? $cat->slug ?? 'category'),
                        'parent_id' => null,
                        'icon' => $cat->icon ?? null,
                        'sort_order' => $cat->sort_order ?? 0,
                        'is_active' => true,
                    ]);
                    $this->categoryMap[$cat->id] = $newId;
                    $this->storeMapping($cat->id, 'categories', 'categories', $newId);
                }
                $this->info("    Migrated {$categories->count()} categories");
            }
        } catch (\Exception $e) {
            // No categories table, that's fine
        }
    }

    private function findOrCreateCategory(string $name): int
    {
        $slug = Str::slug($name);

        // Check if we already have this category
        $existing = DB::table('categories')->where('slug', $slug)->first();
        if ($existing) {
            return $existing->id;
        }

        if ($this->dryRun) return 0;

        return DB::table('categories')->insertGetId([
            'slug' => $slug,
            'parent_id' => null,
            'icon' => null,
            'sort_order' => 0,
            'is_active' => true,
        ]);
    }

    private function mapLanguage(string $language): string
    {
        return match (strtolower($language)) {
            'français', 'french', 'fr' => 'fr',
            'english', 'en' => 'en',
            'arabic', 'ar', 'arabe' => 'ar',
            'español', 'spanish', 'es' => 'es',
            default => 'en',
        };
    }

    private function migrateFolders($old): void
    {
        try {
            $folders = $old->table('library_folders')->get();

            foreach ($folders as $folder) {
                $userId = $this->userMap[$folder->user_id] ?? null;
                if (!$userId || $this->dryRun) continue;

                $newId = DB::table('folders')->insertGetId([
                    'user_id' => $userId,
                    'name' => $folder->name,
                    'color' => $folder->color ?? '#6366f1',
                    'icon' => $folder->icon ?? '📚',
                    'parent_id' => null, // Will update after all folders
                    'sort_order' => $folder->sort_order ?? 0,
                    'created_at' => $folder->created_at,
                    'updated_at' => $folder->updated_at,
                ]);
                $this->folderMap[$folder->id] = $newId;
                $this->storeMapping($folder->id, 'library_folders', 'folders', $newId);
            }

            // Update parent_id for nested folders
            foreach ($folders as $folder) {
                if ($folder->parent_id && isset($this->folderMap[$folder->parent_id]) && isset($this->folderMap[$folder->id])) {
                    DB::table('folders')
                        ->where('id', $this->folderMap[$folder->id])
                        ->update(['parent_id' => $this->folderMap[$folder->parent_id]]);
                }
            }

            // Migrate deck-folder assignments
            $assignments = $old->table('deck_folder_assignments')->get();
            foreach ($assignments as $a) {
                $deckId = $this->deckMap[$a->deck_id] ?? null;
                $folderId = $this->folderMap[$a->folder_id ?? ''] ?? null;
                $userId = $this->userMap[$a->user_id] ?? null;

                if ($deckId && $userId) {
                    DB::table('deck_folder')->insertOrIgnore([
                        'deck_id' => $deckId,
                        'folder_id' => $folderId,
                        'user_id' => $userId,
                        'sort_order' => $a->sort_order ?? 0,
                    ]);
                }
            }

            $this->info("    Migrated " . count($this->folderMap) . " folders");
        } catch (\Exception $e) {
            $this->warn("    Folders migration skipped: " . $e->getMessage());
        }
    }

    private function migrateDeckFavorites($old): void
    {
        try {
            $favs = $old->table('deck_favorites')->get();
            $count = 0;

            foreach ($favs as $fav) {
                $userId = $this->userMap[$fav->user_id] ?? null;
                $deckId = $this->deckMap[$fav->deck_id] ?? null;

                if ($userId && $deckId && !$this->dryRun) {
                    DB::table('deck_favorites')->insertOrIgnore([
                        'user_id' => $userId,
                        'deck_id' => $deckId,
                        'created_at' => $fav->favorited_at ?? $fav->created_at ?? now(),
                    ]);
                    $count++;
                }
            }

            $this->info("    Migrated {$count} deck favorites");
        } catch (\Exception $e) {
            $this->warn("    Deck favorites migration skipped: " . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    // Step 3: Cards
    // ═══════════════════════════════════════════════════

    private function migrateCards(): void
    {
        $old = DB::connection('old_pgsql');
        $cards = $old->table('cards')->get();
        $this->info("  Found {$cards->count()} cards");

        foreach ($cards as $card) {
            $deckId = $this->deckMap[$card->deck_id] ?? null;
            if (!$deckId) {
                $this->warn("    Skipping card: deck not found ({$card->deck_id})");
                continue;
            }

            $newUuid = Str::uuid()->toString();

            $data = [
                'uuid' => $newUuid,
                'deck_id' => $deckId,
                'front_text' => $card->front_text,
                'back_text' => $card->back_text,
                'front_image_url' => $card->front_image_url,
                'back_image_url' => $card->back_image_url,
                'front_audio_url' => $card->front_audio_url ?? $card->audio_url,
                'back_audio_url' => $card->back_audio_url,
                'hint' => null,
                'explanation' => null,
                'position' => $card->order_index ?? 0,
                'is_mcq' => false,
                'created_at' => $card->created_at,
                'updated_at' => $card->updated_at,
            ];

            if ($this->dryRun) {
                $this->line("    [DRY] Would insert card: " . Str::limit($card->front_text, 40));
                continue;
            }

            $newId = DB::table('cards')->insertGetId($data);
            $this->cardMap[$card->id] = $newId;
            $this->storeMapping($card->id, 'cards', 'cards', $newId, $newUuid);
        }

        // Update cards_count on decks
        if (!$this->dryRun) {
            DB::statement("
                UPDATE decks SET cards_count = (
                    SELECT COUNT(*) FROM cards WHERE cards.deck_id = decks.id
                )
            ");
        }

        $this->info("  ✓ Cards migrated: " . count($this->cardMap));
    }

    // ═══════════════════════════════════════════════════
    // Step 4: Progress → card_progress
    // ═══════════════════════════════════════════════════

    private function migrateProgress(): void
    {
        $old = DB::connection('old_pgsql');
        $progressRows = $old->table('progress')->get();
        $this->info("  Found {$progressRows->count()} progress records");

        $count = 0;
        foreach ($progressRows as $p) {
            $userId = $this->userMap[$p->user_id] ?? null;
            $cardId = $this->cardMap[$p->card_id] ?? null;

            if (!$userId || !$cardId) continue;

            $data = [
                'user_id' => $userId,
                'card_id' => $cardId,
                'easiness_factor' => max(1.30, $p->ease_factor ?? 2.50),
                'interval_days' => max(0, $p->interval ?? 0),
                'repetitions' => max(0, $p->repetitions ?? 0),
                'next_review_at' => $p->next_review_at,
                'last_reviewed_at' => $p->last_reviewed_at,
                'total_reviews' => $p->repetitions ?? 0,
                'correct_reviews' => 0, // Not tracked in old schema
                'created_at' => $p->created_at,
                'updated_at' => $p->updated_at,
            ];

            if ($this->dryRun) {
                $count++;
                continue;
            }

            DB::table('card_progress')->insertOrIgnore($data);
            $count++;
        }

        $this->info("  ✓ Progress records migrated: {$count}");
    }

    // ═══════════════════════════════════════════════════
    // Step 5: Badges
    // ═══════════════════════════════════════════════════

    private function migrateBadges(): void
    {
        $old = DB::connection('old_pgsql');

        try {
            $badges = $old->table('badges')->get();
            $this->info("  Found {$badges->count()} badges");

            foreach ($badges as $badge) {
                if ($this->dryRun) continue;

                $newId = DB::table('badges')->insertGetId([
                    'slug' => Str::slug($badge->name),
                    'name' => $badge->name,
                    'description' => $badge->description,
                    'icon' => $badge->icon_name ?? '🏆',
                    'color' => $badge->color ?? '#10B981',
                    'criteria' => null,
                ]);
                $this->badgeMap[$badge->id] = $newId;
                $this->storeMapping($badge->id, 'badges', 'badges', $newId);
            }

            // Migrate user_badges
            $userBadges = $old->table('user_badges')->get();
            $ubCount = 0;

            foreach ($userBadges as $ub) {
                $userId = $this->userMap[$ub->user_id] ?? null;
                $badgeId = $this->badgeMap[$ub->badge_id] ?? null;

                if ($userId && $badgeId && !$this->dryRun) {
                    DB::table('user_badges')->insertOrIgnore([
                        'user_id' => $userId,
                        'badge_id' => $badgeId,
                        'awarded_by' => isset($ub->assigned_by) ? ($this->userMap[$ub->assigned_by] ?? null) : null,
                        'awarded_at' => $ub->assigned_at ?? $ub->created_at,
                    ]);
                    $ubCount++;
                }
            }

            $this->info("  ✓ Badges migrated: " . count($this->badgeMap) . " badges, {$ubCount} awards");
        } catch (\Exception $e) {
            $this->warn("  Badges migration skipped: " . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    // Step 6: Messages
    // ═══════════════════════════════════════════════════

    private function migrateMessages(): void
    {
        $old = DB::connection('old_pgsql');

        try {
            $messages = $old->table('messages')->get();
            $this->info("  Found {$messages->count()} messages");

            $count = 0;
            foreach ($messages as $msg) {
                $senderId = $this->userMap[$msg->sender_id] ?? null;
                $receiverId = $this->userMap[$msg->receiver_id] ?? null;

                if (!$senderId || !$receiverId || $this->dryRun) continue;

                DB::table('messages')->insert([
                    'sender_id' => $senderId,
                    'receiver_id' => $receiverId,
                    'content' => $msg->content,
                    'is_read' => $msg->is_read ?? false,
                    'created_at' => $msg->created_at,
                ]);
                $count++;
            }

            $this->info("  ✓ Messages migrated: {$count}");
        } catch (\Exception $e) {
            $this->warn("  Messages migration skipped: " . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    // Step 7: Activity Logs
    // ═══════════════════════════════════════════════════

    private function migrateActivityLogs(): void
    {
        $old = DB::connection('old_pgsql');

        try {
            $logs = $old->table('activity_logs')->get();
            $this->info("  Found {$logs->count()} activity logs");

            $count = 0;
            foreach ($logs as $log) {
                // activity_logs.child_id references children.id → userMap via childMap
                $userId = $this->childMap[$log->child_id] ?? null;
                if (!$userId || $this->dryRun) continue;

                $deckId = isset($log->deck_id) ? ($this->deckMap[$log->deck_id] ?? null) : null;

                DB::table('activity_logs')->insert([
                    'user_id' => $userId,
                    'activity_type' => $log->activity_type ?? 'study',
                    'deck_id' => $deckId,
                    'metadata' => is_string($log->metadata) ? $log->metadata : json_encode($log->metadata),
                    'duration_minutes' => $log->duration_minutes,
                    'cards_reviewed' => $log->cards_reviewed,
                    'success_rate' => $log->success_rate,
                    'created_at' => $log->created_at,
                ]);
                $count++;
            }

            // Also migrate audit_logs
            $this->migrateAuditLogs($old);

            $this->info("  ✓ Activity logs migrated: {$count}");
        } catch (\Exception $e) {
            $this->warn("  Activity logs migration skipped: " . $e->getMessage());
        }
    }

    private function migrateAuditLogs($old): void
    {
        try {
            $audits = $old->table('audit_logs')->get();
            $count = 0;

            foreach ($audits as $audit) {
                $userId = $this->userMap[$audit->user_id] ?? null;
                if (!$userId || $this->dryRun) continue;

                DB::table('audit_logs')->insert([
                    'user_id' => $userId,
                    'action' => $audit->action,
                    'target_type' => $audit->resource_type ?? '',
                    'target_id' => 0, // UUID cannot be stored as BIGINT, use metadata
                    'old_values' => null,
                    'new_values' => is_string($audit->metadata) ? $audit->metadata : json_encode($audit->metadata),
                    'ip_address' => $audit->ip_address,
                    'user_agent' => $audit->user_agent,
                    'created_at' => $audit->created_at,
                ]);
                $count++;
            }

            $this->info("    Audit logs migrated: {$count}");
        } catch (\Exception $e) {
            // Audit logs might not exist
        }
    }

    // ═══════════════════════════════════════════════════
    // Step 8: Subscriptions
    // ═══════════════════════════════════════════════════

    private function migrateSubscriptions(): void
    {
        $old = DB::connection('old_pgsql');

        try {
            // Migrate subscription plans
            $plans = $old->table('subscription_plans')->get();
            $this->info("  Found {$plans->count()} subscription plans");

            $planMap = [];
            foreach ($plans as $plan) {
                if ($this->dryRun) continue;

                // Map old duration to slug
                $slug = match ($plan->duration_type ?? '') {
                    'monthly' => 'monthly',
                    'quarterly' => 'quarterly',
                    'semi_annual' => 'semi-annual',
                    'annual' => 'yearly',
                    default => Str::slug($plan->name),
                };

                $durationDays = match ($plan->duration_type ?? '') {
                    'monthly' => 30,
                    'quarterly' => 90,
                    'semi_annual' => 180,
                    'annual' => 365,
                    default => ($plan->duration_value ?? 1) * 30,
                };

                $newId = DB::table('subscription_plans')->insertGetId([
                    'slug' => $slug,
                    'name' => $plan->name,
                    'price' => $plan->price ?? 0,
                    'currency' => $plan->currency ?? 'USD',
                    'duration_days' => $durationDays,
                    'apple_product_id' => null,
                    'features' => $plan->features,
                    'is_active' => $plan->is_active ?? true,
                    'sort_order' => $plan->sort_order ?? 0,
                ]);
                $planMap[$plan->id] = $newId;
            }

            // Migrate user subscriptions
            $subs = $old->table('user_subscriptions')->get();
            $count = 0;

            foreach ($subs as $sub) {
                $userId = $this->userMap[$sub->user_id] ?? null;
                $planId = $planMap[$sub->plan_id] ?? null;

                if (!$userId || !$planId || $this->dryRun) continue;

                // Map old status
                $statusMap = [
                    'active' => 'active',
                    'canceled' => 'cancelled',
                    'expired' => 'expired',
                    'past_due' => 'expired',
                    'trialing' => 'trial',
                    'incomplete' => 'expired',
                ];

                DB::table('subscriptions')->insert([
                    'user_id' => $userId,
                    'plan_id' => $planId,
                    'status' => $statusMap[$sub->status] ?? 'expired',
                    'apple_transaction_id' => null,
                    'apple_original_transaction_id' => null,
                    'current_period_start' => $sub->current_period_start,
                    'current_period_end' => $sub->current_period_end,
                    'cancelled_at' => $sub->canceled_at,
                    'created_at' => $sub->created_at,
                    'updated_at' => $sub->updated_at,
                ]);
                $count++;
            }

            $this->info("  ✓ Subscriptions migrated: " . count($planMap) . " plans, {$count} subscriptions");
        } catch (\Exception $e) {
            $this->warn("  Subscriptions migration skipped: " . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    // Summary
    // ═══════════════════════════════════════════════════

    private function showSummary(): void
    {
        $this->newLine();
        $this->info('╔══════════════════════════════════════╗');
        $this->info('║        Migration Summary             ║');
        $this->info('╠══════════════════════════════════════╣');

        $counts = [
            'Users' => DB::table('users')->count(),
            'Parent-Child' => DB::table('parent_child')->count(),
            'Decks' => DB::table('decks')->count(),
            'Cards' => DB::table('cards')->count(),
            'Card Progress' => DB::table('card_progress')->count(),
            'Badges' => DB::table('badges')->count(),
            'User Badges' => DB::table('user_badges')->count(),
            'Messages' => DB::table('messages')->count(),
            'Activity Logs' => DB::table('activity_logs')->count(),
            'Subscriptions' => DB::table('subscriptions')->count(),
            'UUID Mappings' => DB::table('uuid_mappings')->count(),
        ];

        foreach ($counts as $label => $count) {
            $this->info(sprintf('║  %-22s %8s    ║', $label, number_format($count)));
        }

        $this->info('╚══════════════════════════════════════╝');
    }
}
