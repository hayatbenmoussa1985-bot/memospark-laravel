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
 * Old schema (PostgreSQL on o2switch):
 *   - users: id, name, email, password
 *   - profiles: id (UUID), user_id → users.id, role, first_name, last_name, etc.
 *   - decks: id (UUID), profile_id → profiles.id, title, description, etc.
 *   - cards: id (UUID), deck_id → decks.id, question, answer, etc.
 *   - card_reviews: id, card_id, profile_id, quality, easiness, interval, etc.
 *   - badges, profile_badges, messages, notifications, activity_logs, etc.
 *
 * New schema (MySQL):
 *   - users: id (BIGINT AI), uuid, name, email, role, etc.
 *   - decks: id (BIGINT AI), uuid, user_id, title, etc.
 *   - cards: id (BIGINT AI), uuid, deck_id, front_text, back_text, etc.
 *   - card_progress: user_id, card_id, easiness_factor, interval_days, etc.
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
     * ID mapping caches (populated during migration).
     * Key = old ID (UUID string or integer), Value = new BIGINT ID.
     */
    private array $profileToUserMap = [];  // old profiles.id (UUID) => new users.id
    private array $oldUserToNewMap = [];   // old users.id (int) => new users.id
    private array $deckMap = [];           // old decks.id (UUID) => new decks.id
    private array $cardMap = [];           // old cards.id (UUID) => new cards.id
    private array $badgeMap = [];          // old badges.id => new badges.id
    private array $categoryMap = [];       // category slug => new categories.id
    private array $folderMap = [];         // old folders.id => new folders.id

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
                old_uuid VARCHAR(255) NOT NULL,
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
            'categories',
        ];

        foreach ($tables as $table) {
            try {
                DB::table($table)->truncate();
            } catch (\Exception $e) {
                // Table might not exist yet
            }
        }

        // Delete all users except the seeded super admin
        DB::table('users')->where('email', '!=', 'admin@memospark.net')->delete();

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->info('  Done.');
        $this->newLine();
    }

    private function rebuildMapsFromMappingTable(): void
    {
        $mappings = DB::table('uuid_mappings')->get();

        foreach ($mappings as $m) {
            match ($m->old_table) {
                'profiles' => $this->profileToUserMap[$m->old_uuid] = $m->new_id,
                'decks' => $this->deckMap[$m->old_uuid] = $m->new_id,
                'cards' => $this->cardMap[$m->old_uuid] = $m->new_id,
                'badges' => $this->badgeMap[$m->old_uuid] = $m->new_id,
                'folders' => $this->folderMap[$m->old_uuid] = $m->new_id,
                default => null,
            };
        }

        $this->info("Rebuilt maps: " . count($this->profileToUserMap) . " users, "
            . count($this->deckMap) . " decks, "
            . count($this->cardMap) . " cards");
    }

    private function runStep(string $name, string $method): void
    {
        $this->newLine();
        $this->info("━━━ Step: {$name} ━━━");
        $start = microtime(true);

        try {
            $this->$method();
        } catch (\Exception $e) {
            $this->error("  ✗ Error: " . $e->getMessage());
            $this->error("  File: " . $e->getFile() . ":" . $e->getLine());
        }

        $duration = round(microtime(true) - $start, 2);
        $this->info("  Completed in {$duration}s");
    }

    private function storeMapping(string $oldId, string $oldTable, string $newTable, int $newId, ?string $newUuid = null): void
    {
        if ($this->dryRun) return;

        DB::table('uuid_mappings')->insertOrIgnore([
            'old_uuid' => $oldId,
            'old_table' => $oldTable,
            'new_table' => $newTable,
            'new_id' => $newId,
            'new_uuid' => $newUuid,
        ]);
    }

    // ═══════════════════════════════════════════════════
    // Step 1: Users (profiles JOIN users → new users)
    // ═══════════════════════════════════════════════════

    private function migrateUsers(): void
    {
        $old = DB::connection('old_pgsql');

        // Join profiles with users to get email + password
        $profiles = $old->table('profiles')
            ->join('users', 'profiles.user_id', '=', 'users.id')
            ->select(
                'profiles.id as profile_id',
                'profiles.user_id',
                'profiles.role',
                'profiles.first_name',
                'profiles.last_name',
                'profiles.display_name',
                'profiles.date_of_birth',
                'profiles.school_level',
                'profiles.avatar_url',
                'profiles.status',
                'profiles.xp',
                'profiles.level',
                'profiles.streak_days',
                'profiles.total_cards_reviewed',
                'profiles.total_decks_created',
                'profiles.profile_type',
                'profiles.created_at as profile_created_at',
                'profiles.updated_at as profile_updated_at',
                'users.id as old_user_id',
                'users.name as user_name',
                'users.email',
                'users.password',
                'users.email_verified_at',
                'users.created_at as user_created_at',
            )
            ->get();

        $this->info("  Found {$profiles->count()} profiles (joined with users)");

        $skipped = 0;
        foreach ($profiles as $p) {
            // Skip if email already exists in new DB (e.g., the seeded super admin)
            $existingUser = DB::table('users')->where('email', $p->email)->first();
            if ($existingUser) {
                $this->profileToUserMap[$p->profile_id] = $existingUser->id;
                $this->oldUserToNewMap[$p->old_user_id] = $existingUser->id;
                $this->storeMapping($p->profile_id, 'profiles', 'users', $existingUser->id, $existingUser->uuid ?? null);
                $skipped++;
                continue;
            }

            // Map role
            $roleMap = [
                'admin' => UserRole::SuperAdmin,
                'moderator' => UserRole::Admin,
                'user' => UserRole::Learner,
                'learner' => UserRole::Learner,
                'child' => UserRole::Child,
                'parent' => UserRole::Parent,
                'adult' => UserRole::Learner,
            ];
            $role = $roleMap[$p->role] ?? UserRole::Learner;

            // Build full name
            $name = trim(($p->first_name ?? '') . ' ' . ($p->last_name ?? ''));
            if (empty($name)) {
                $name = $p->display_name ?? $p->user_name ?? 'User';
            }

            $newUuid = Str::uuid()->toString();

            $data = [
                'uuid' => $newUuid,
                'name' => $name,
                'email' => $p->email,
                'password' => $p->password, // Already hashed from old users table
                'role' => $role->value,
                'is_active' => ($p->status ?? 'active') === 'active',
                'locale' => 'en',
                'timezone' => 'UTC',
                'avatar_path' => $p->avatar_url,
                'date_of_birth' => $p->date_of_birth,
                'school_level' => $p->school_level,
                'google_id' => null,
                'apple_user_id' => null,
                'email_verified_at' => $p->email_verified_at ?? $p->user_created_at,
                'created_at' => $p->profile_created_at ?? $p->user_created_at,
                'updated_at' => $p->profile_updated_at ?? $p->user_created_at,
            ];

            if ($this->dryRun) {
                $this->line("    [DRY] Would insert user: {$p->email} ({$role->value})");
                $this->profileToUserMap[$p->profile_id] = 0;
                $this->oldUserToNewMap[$p->old_user_id] = 0;
                continue;
            }

            $newId = DB::table('users')->insertGetId($data);
            $this->profileToUserMap[$p->profile_id] = $newId;
            $this->oldUserToNewMap[$p->old_user_id] = $newId;
            $this->storeMapping($p->profile_id, 'profiles', 'users', $newId, $newUuid);
        }

        $migrated = count($this->profileToUserMap);
        $this->info("  ✓ Users migrated: {$migrated} (skipped {$skipped} existing)");
    }

    // ═══════════════════════════════════════════════════
    // Step 2: Decks
    // ═══════════════════════════════════════════════════

    private function migrateDecks(): void
    {
        $old = DB::connection('old_pgsql');

        // First migrate library_categories → categories
        $this->migrateLibraryCategories($old);

        // Migrate user decks
        $decks = $old->table('decks')->get();
        $this->info("  Found {$decks->count()} user decks");

        foreach ($decks as $deck) {
            // deck.profile_id references profiles.id
            $userId = $this->profileToUserMap[$deck->profile_id] ?? null;
            if (!$userId) {
                $this->warn("    Skipping deck '{$deck->title}': profile not found ({$deck->profile_id})");
                continue;
            }

            // Map visibility
            $visibility = 'private';
            if (!empty($deck->visibility)) {
                $visibility = match ($deck->visibility) {
                    'public' => 'public',
                    'library' => 'library',
                    default => 'private',
                };
            } elseif ($deck->is_public ?? false) {
                $visibility = 'public';
            }

            // Map category string → category_id
            $categoryId = null;
            if (!empty($deck->category)) {
                $categoryId = $this->findOrCreateCategory($deck->category);
            }

            $newUuid = Str::uuid()->toString();

            $data = [
                'uuid' => $newUuid,
                'user_id' => $userId,
                'title' => $deck->title,
                'description' => $deck->description,
                'category_id' => $categoryId,
                'language' => $this->mapLanguage($deck->language ?? 'en'),
                'difficulty' => $deck->difficulty ?? 'beginner',
                'visibility' => $visibility,
                'cover_image_path' => $deck->cover_image_url,
                'is_featured' => $deck->is_featured ?? false,
                'cards_count' => 0, // Recalculated after cards migration
                'average_rating' => $deck->average_rating ?? 0,
                'ratings_count' => $deck->ratings_count ?? 0,
                'is_ai_generated' => $deck->is_ai_generated ?? false,
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

        // Migrate library_decks → decks with visibility='library'
        $this->migrateLibraryDecks($old);

        // Migrate folders and deck assignments
        $this->migrateFolders($old);

        // Migrate deck favorites
        $this->migrateDeckFavorites($old);

        $this->info("  ✓ Decks migrated: " . count($this->deckMap));
    }

    private function migrateLibraryCategories($old): void
    {
        try {
            $cats = $old->table('library_categories')->get();
            $this->info("  Found {$cats->count()} library categories");

            foreach ($cats as $cat) {
                if ($this->dryRun) continue;

                $slug = Str::slug($cat->name ?? 'category-' . $cat->id);

                // Avoid duplicate slugs
                $existing = DB::table('categories')->where('slug', $slug)->first();
                if ($existing) {
                    $this->categoryMap[$slug] = $existing->id;
                    continue;
                }

                $newId = DB::table('categories')->insertGetId([
                    'slug' => $slug,
                    'parent_id' => null,
                    'icon' => $cat->icon ?? null,
                    'sort_order' => $cat->sort_order ?? 0,
                    'is_active' => true,
                ]);
                $this->categoryMap[$slug] = $newId;
            }

            $this->info("    Categories migrated: " . count($this->categoryMap));
        } catch (\Exception $e) {
            $this->warn("    Categories migration skipped: " . $e->getMessage());
        }
    }

    private function migrateLibraryDecks($old): void
    {
        try {
            $libraryDecks = $old->table('library_decks')->get();
            $this->info("  Found {$libraryDecks->count()} library decks");

            // We need a user to own these — use the super admin
            $superAdmin = DB::table('users')->where('role', 'super_admin')->first();
            $ownerId = $superAdmin ? $superAdmin->id : 1;

            foreach ($libraryDecks as $deck) {
                if ($this->dryRun) continue;

                $newUuid = Str::uuid()->toString();

                // Find category if available
                $categoryId = null;
                if (isset($deck->category_id)) {
                    $slug = Str::slug($deck->category_id);
                    $categoryId = $this->categoryMap[$slug] ?? null;
                }

                $newId = DB::table('decks')->insertGetId([
                    'uuid' => $newUuid,
                    'user_id' => $ownerId,
                    'title' => $deck->title,
                    'description' => $deck->description ?? null,
                    'category_id' => $categoryId,
                    'language' => $this->mapLanguage($deck->language ?? 'en'),
                    'difficulty' => $deck->difficulty ?? 'beginner',
                    'visibility' => 'library',
                    'cover_image_path' => $deck->cover_image_url ?? null,
                    'is_featured' => $deck->is_featured ?? false,
                    'cards_count' => 0,
                    'is_ai_generated' => false,
                    'created_at' => $deck->created_at,
                    'updated_at' => $deck->updated_at,
                ]);
                $this->deckMap['lib_' . $deck->id] = $newId;
                $this->storeMapping('lib_' . $deck->id, 'library_decks', 'decks', $newId, $newUuid);
            }
        } catch (\Exception $e) {
            $this->warn("    Library decks migration skipped: " . $e->getMessage());
        }
    }

    private function findOrCreateCategory(string $name): ?int
    {
        $slug = Str::slug($name);
        if (empty($slug)) return null;

        if (isset($this->categoryMap[$slug])) {
            return $this->categoryMap[$slug];
        }

        $existing = DB::table('categories')->where('slug', $slug)->first();
        if ($existing) {
            $this->categoryMap[$slug] = $existing->id;
            return $existing->id;
        }

        if ($this->dryRun) return null;

        $newId = DB::table('categories')->insertGetId([
            'slug' => $slug,
            'parent_id' => null,
            'icon' => null,
            'sort_order' => 0,
            'is_active' => true,
        ]);
        $this->categoryMap[$slug] = $newId;
        return $newId;
    }

    private function mapLanguage(string $language): string
    {
        return match (strtolower(trim($language))) {
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
            $folders = $old->table('folders')->get();
            $this->info("  Found {$folders->count()} folders");

            foreach ($folders as $folder) {
                // folders might use profile_id or user_id
                $userId = null;
                if (isset($folder->profile_id)) {
                    $userId = $this->profileToUserMap[$folder->profile_id] ?? null;
                } elseif (isset($folder->user_id)) {
                    $userId = $this->oldUserToNewMap[$folder->user_id] ?? null;
                }
                if (!$userId || $this->dryRun) continue;

                $newId = DB::table('folders')->insertGetId([
                    'user_id' => $userId,
                    'name' => $folder->name,
                    'color' => $folder->color ?? '#6366f1',
                    'icon' => $folder->icon ?? '📚',
                    'parent_id' => null,
                    'sort_order' => $folder->sort_order ?? 0,
                    'created_at' => $folder->created_at,
                    'updated_at' => $folder->updated_at,
                ]);
                $this->folderMap[$folder->id] = $newId;
            }

            // Migrate deck_folder_assignments
            $assignments = $old->table('deck_folder_assignments')->get();
            $assignCount = 0;
            foreach ($assignments as $a) {
                $deckId = $this->deckMap[$a->deck_id] ?? null;
                $folderId = $this->folderMap[$a->folder_id] ?? null;

                // Try to find user from the deck
                $userId = null;
                if ($deckId) {
                    $deck = DB::table('decks')->where('id', $deckId)->first();
                    $userId = $deck?->user_id;
                }

                if ($deckId && $folderId && $userId && !$this->dryRun) {
                    DB::table('deck_folder')->insertOrIgnore([
                        'deck_id' => $deckId,
                        'folder_id' => $folderId,
                        'user_id' => $userId,
                        'sort_order' => $a->sort_order ?? 0,
                    ]);
                    $assignCount++;
                }
            }

            $this->info("    Folders: " . count($this->folderMap) . ", assignments: {$assignCount}");
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
                // deck_favorites might use profile_id or user_id
                $userId = null;
                if (isset($fav->profile_id)) {
                    $userId = $this->profileToUserMap[$fav->profile_id] ?? null;
                } elseif (isset($fav->user_id)) {
                    $userId = $this->oldUserToNewMap[$fav->user_id] ?? null;
                }
                $deckId = $this->deckMap[$fav->deck_id] ?? null;

                if ($userId && $deckId && !$this->dryRun) {
                    DB::table('deck_favorites')->insertOrIgnore([
                        'user_id' => $userId,
                        'deck_id' => $deckId,
                        'created_at' => $fav->created_at ?? now(),
                    ]);
                    $count++;
                }
            }

            $this->info("    Deck favorites: {$count}");
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

        // Migrate user deck cards
        $cards = $old->table('cards')->get();
        $this->info("  Found {$cards->count()} cards");

        foreach ($cards as $card) {
            $deckId = $this->deckMap[$card->deck_id] ?? null;
            if (!$deckId) {
                $this->warn("    Skipping card: deck not found ({$card->deck_id})");
                continue;
            }

            $newUuid = Str::uuid()->toString();

            // Detect MCQ from mcq_options column
            $isMcq = false;
            $mcqQuestion = null;
            if (!empty($card->mcq_options)) {
                $isMcq = true;
                $mcqQuestion = $card->question; // The question is the MCQ question
            }

            $data = [
                'uuid' => $newUuid,
                'deck_id' => $deckId,
                'front_text' => $card->question ?? '',
                'back_text' => $card->answer ?? '',
                'front_image_url' => $card->question_image_url,
                'back_image_url' => $card->answer_image_url,
                'front_audio_url' => $card->question_audio_url,
                'back_audio_url' => $card->answer_audio_url,
                'hint' => $card->hint,
                'explanation' => $card->explanation,
                'position' => $card->position ?? 0,
                'is_mcq' => $isMcq,
                'mcq_question' => $mcqQuestion,
                'created_at' => $card->created_at,
                'updated_at' => $card->updated_at,
            ];

            if ($this->dryRun) {
                $this->line("    [DRY] Would insert card: " . Str::limit($card->question ?? '', 40));
                continue;
            }

            $newId = DB::table('cards')->insertGetId($data);
            $this->cardMap[$card->id] = $newId;
            $this->storeMapping($card->id, 'cards', 'cards', $newId, $newUuid);

            // Migrate MCQ options if present
            if ($isMcq && !empty($card->mcq_options)) {
                $this->migrateMcqOptions($card, $newId);
            }
        }

        // Migrate library_cards
        $this->migrateLibraryCards($old);

        // Recalculate cards_count on all decks
        if (!$this->dryRun) {
            DB::statement("
                UPDATE decks SET cards_count = (
                    SELECT COUNT(*) FROM cards WHERE cards.deck_id = decks.id
                )
            ");
        }

        $this->info("  ✓ Cards migrated: " . count($this->cardMap));
    }

    private function migrateMcqOptions($card, int $newCardId): void
    {
        $options = is_string($card->mcq_options) ? json_decode($card->mcq_options, true) : $card->mcq_options;
        if (!is_array($options)) return;

        foreach ($options as $index => $option) {
            $optionText = is_string($option) ? $option : ($option['text'] ?? $option['option'] ?? '');

            DB::table('mcq_options')->insert([
                'card_id' => $newCardId,
                'option_text' => $optionText,
                'option_image_url' => null,
                'is_correct' => $index === ($card->mcq_correct_index ?? 0),
                'position' => $index,
            ]);
        }
    }

    private function migrateLibraryCards($old): void
    {
        try {
            $cards = $old->table('library_cards')->get();
            $this->info("  Found {$cards->count()} library cards");

            foreach ($cards as $card) {
                $deckId = $this->deckMap['lib_' . $card->deck_id] ?? null;
                if (!$deckId || $this->dryRun) continue;

                $newUuid = Str::uuid()->toString();

                // library_cards may have different column names
                $frontText = $card->question ?? $card->front_text ?? '';
                $backText = $card->answer ?? $card->back_text ?? '';

                $newId = DB::table('cards')->insertGetId([
                    'uuid' => $newUuid,
                    'deck_id' => $deckId,
                    'front_text' => $frontText,
                    'back_text' => $backText,
                    'front_image_url' => $card->question_image_url ?? $card->image_url ?? null,
                    'back_image_url' => $card->answer_image_url ?? null,
                    'front_audio_url' => $card->audio_url ?? null,
                    'back_audio_url' => null,
                    'hint' => $card->hint ?? null,
                    'explanation' => $card->explanation ?? null,
                    'position' => $card->position ?? $card->order_index ?? 0,
                    'is_mcq' => false,
                    'created_at' => $card->created_at,
                    'updated_at' => $card->updated_at,
                ]);
                $this->cardMap['lib_' . $card->id] = $newId;
            }
        } catch (\Exception $e) {
            $this->warn("    Library cards migration skipped: " . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    // Step 4: Progress (card_reviews → card_progress)
    // ═══════════════════════════════════════════════════

    private function migrateProgress(): void
    {
        $old = DB::connection('old_pgsql');

        $reviews = $old->table('card_reviews')->get();
        $this->info("  Found {$reviews->count()} card review records");

        $count = 0;
        foreach ($reviews as $r) {
            // card_reviews.profile_id → profileToUserMap
            $userId = $this->profileToUserMap[$r->profile_id] ?? null;
            $cardId = $this->cardMap[$r->card_id] ?? null;

            if (!$userId || !$cardId) continue;

            $data = [
                'user_id' => $userId,
                'card_id' => $cardId,
                'easiness_factor' => max(1.30, $r->easiness ?? 2.50),
                'interval_days' => max(0, $r->interval ?? 0),
                'repetitions' => max(0, $r->repetitions ?? 0),
                'next_review_at' => $r->next_review_at,
                'last_reviewed_at' => $r->reviewed_at ?? $r->created_at,
                'total_reviews' => $r->total_reviews ?? 0,
                'correct_reviews' => $r->correct_reviews ?? 0,
                'created_at' => $r->created_at,
                'updated_at' => $r->updated_at,
            ];

            if ($this->dryRun) {
                $count++;
                continue;
            }

            DB::table('card_progress')->insertOrIgnore($data);
            $count++;
        }

        // Also migrate library_user_card_progress if it exists
        $this->migrateLibraryProgress($old);

        $this->info("  ✓ Progress records migrated: {$count}");
    }

    private function migrateLibraryProgress($old): void
    {
        try {
            $progress = $old->table('library_user_card_progress')->get();
            $this->info("  Found {$progress->count()} library progress records");

            $count = 0;
            foreach ($progress as $p) {
                $userId = null;
                if (isset($p->profile_id)) {
                    $userId = $this->profileToUserMap[$p->profile_id] ?? null;
                } elseif (isset($p->user_id)) {
                    $userId = $this->oldUserToNewMap[$p->user_id] ?? null;
                }
                $cardId = $this->cardMap['lib_' . ($p->card_id ?? '')] ?? null;

                if (!$userId || !$cardId || $this->dryRun) continue;

                DB::table('card_progress')->insertOrIgnore([
                    'user_id' => $userId,
                    'card_id' => $cardId,
                    'easiness_factor' => max(1.30, $p->easiness_factor ?? $p->easiness ?? 2.50),
                    'interval_days' => max(0, $p->interval ?? 0),
                    'repetitions' => max(0, $p->repetitions ?? 0),
                    'next_review_at' => $p->next_review_at ?? now(),
                    'last_reviewed_at' => $p->last_reviewed_at ?? $p->updated_at,
                    'total_reviews' => $p->total_reviews ?? 0,
                    'correct_reviews' => $p->correct_reviews ?? 0,
                    'created_at' => $p->created_at,
                    'updated_at' => $p->updated_at,
                ]);
                $count++;
            }

            $this->info("    Library progress: {$count}");
        } catch (\Exception $e) {
            $this->warn("    Library progress migration skipped: " . $e->getMessage());
        }
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

                $slug = Str::slug($badge->name ?? 'badge-' . $badge->id);

                // Avoid duplicates
                $existing = DB::table('badges')->where('slug', $slug)->first();
                if ($existing) {
                    $this->badgeMap[$badge->id] = $existing->id;
                    continue;
                }

                $newId = DB::table('badges')->insertGetId([
                    'slug' => $slug,
                    'name' => $badge->name ?? 'Badge',
                    'description' => $badge->description ?? null,
                    'icon' => $badge->icon ?? $badge->icon_name ?? '🏆',
                    'color' => $badge->color ?? '#10B981',
                    'criteria' => isset($badge->criteria) ? (is_string($badge->criteria) ? $badge->criteria : json_encode($badge->criteria)) : null,
                ]);
                $this->badgeMap[$badge->id] = $newId;
                $this->storeMapping((string)$badge->id, 'badges', 'badges', $newId);
            }

            // Migrate profile_badges → user_badges
            $profileBadges = $old->table('profile_badges')->get();
            $ubCount = 0;

            foreach ($profileBadges as $pb) {
                $userId = $this->profileToUserMap[$pb->profile_id] ?? null;
                $badgeId = $this->badgeMap[$pb->badge_id] ?? null;

                if ($userId && $badgeId && !$this->dryRun) {
                    DB::table('user_badges')->insertOrIgnore([
                        'user_id' => $userId,
                        'badge_id' => $badgeId,
                        'awarded_by' => null,
                        'awarded_at' => $pb->awarded_at ?? $pb->created_at ?? now(),
                    ]);
                    $ubCount++;
                }
            }

            $this->info("  ✓ Badges migrated: " . count($this->badgeMap) . " badges, {$ubCount} awards");
        } catch (\Exception $e) {
            $this->warn("  Badges migration error: " . $e->getMessage());
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
                // Messages use sender_profile_id / receiver_profile_id
                $senderKey = $msg->sender_profile_id ?? $msg->sender_id ?? null;
                $receiverKey = $msg->receiver_profile_id ?? $msg->receiver_id ?? null;
                $senderId = $senderKey ? ($this->profileToUserMap[$senderKey] ?? null) : null;
                $receiverId = $receiverKey ? ($this->profileToUserMap[$receiverKey] ?? null) : null;

                if (!$senderId || !$receiverId || $this->dryRun) continue;

                DB::table('messages')->insert([
                    'sender_id' => $senderId,
                    'receiver_id' => $receiverId,
                    'content' => $msg->content ?? $msg->message ?? '',
                    'is_read' => $msg->is_read ?? false,
                    'created_at' => $msg->created_at,
                ]);
                $count++;
            }

            // Also migrate notifications
            $this->migrateNotifications($old);

            $this->info("  ✓ Messages migrated: {$count}");
        } catch (\Exception $e) {
            $this->warn("  Messages migration error: " . $e->getMessage());
        }
    }

    private function migrateNotifications($old): void
    {
        try {
            $notifs = $old->table('notifications')->get();
            $count = 0;

            foreach ($notifs as $n) {
                $profileId = $n->profile_id ?? null;
                $userId = $profileId ? ($this->profileToUserMap[$profileId] ?? null) : null;
                if (!$userId || $this->dryRun) continue;

                DB::table('notifications')->insert([
                    'user_id' => $userId,
                    'title' => $n->title ?? 'Notification',
                    'message' => $n->message ?? $n->body ?? '',
                    'type' => $n->type ?? 'system',
                    'data' => isset($n->data) ? (is_string($n->data) ? $n->data : json_encode($n->data)) : null,
                    'read_at' => $n->read_at,
                    'created_at' => $n->created_at,
                ]);
                $count++;
            }

            $this->info("    Notifications: {$count}");
        } catch (\Exception $e) {
            $this->warn("    Notifications migration skipped: " . $e->getMessage());
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
                // activity_logs likely uses profile_id
                $userId = null;
                if (isset($log->profile_id)) {
                    $userId = $this->profileToUserMap[$log->profile_id] ?? null;
                } elseif (isset($log->user_id)) {
                    $userId = $this->oldUserToNewMap[$log->user_id] ?? null;
                } elseif (isset($log->child_id)) {
                    $userId = $this->profileToUserMap[$log->child_id] ?? null;
                }

                if (!$userId || $this->dryRun) continue;

                $deckId = null;
                if (isset($log->deck_id)) {
                    $deckId = $this->deckMap[$log->deck_id] ?? null;
                }

                // Convert duration_seconds to duration_minutes
                $durationMinutes = null;
                if (isset($log->duration_seconds) && $log->duration_seconds) {
                    $durationMinutes = (int) ceil($log->duration_seconds / 60);
                } elseif (isset($log->duration_minutes)) {
                    $durationMinutes = $log->duration_minutes;
                }

                DB::table('activity_logs')->insert([
                    'user_id' => $userId,
                    'activity_type' => $log->activity_type ?? $log->type ?? 'study',
                    'deck_id' => $deckId,
                    'metadata' => isset($log->metadata) ? (is_string($log->metadata) ? $log->metadata : json_encode($log->metadata)) : null,
                    'duration_minutes' => $durationMinutes,
                    'cards_reviewed' => $log->cards_reviewed ?? null,
                    'success_rate' => $log->success_rate ?? null,
                    'created_at' => $log->created_at,
                ]);
                $count++;
            }

            $this->info("  ✓ Activity logs migrated: {$count}");
        } catch (\Exception $e) {
            $this->warn("  Activity logs migration error: " . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    // Step 8: Subscriptions
    // ═══════════════════════════════════════════════════

    private function migrateSubscriptions(): void
    {
        $old = DB::connection('old_pgsql');

        try {
            // Check if user_subscriptions or subscriptions table exists
            $tableName = null;
            foreach (['user_subscriptions', 'subscriptions'] as $t) {
                $exists = $old->select("SELECT EXISTS(SELECT 1 FROM pg_tables WHERE schemaname = 'public' AND tablename = '{$t}') as e");
                if ($exists[0]->e) {
                    $tableName = $t;
                    break;
                }
            }

            if (!$tableName) {
                $this->info("  No subscriptions table found, skipping.");
                return;
            }

            // We already have subscription plans from the seeder. Map old plan references to new.
            $freePlan = DB::table('subscription_plans')->where('slug', 'free')->first();

            $subs = $old->table($tableName)->get();
            $this->info("  Found {$subs->count()} subscriptions");

            $count = 0;
            foreach ($subs as $sub) {
                $userId = null;
                if (isset($sub->profile_id)) {
                    $userId = $this->profileToUserMap[$sub->profile_id] ?? null;
                } elseif (isset($sub->user_id)) {
                    $userId = $this->oldUserToNewMap[$sub->user_id] ?? null;
                }

                if (!$userId || $this->dryRun) continue;

                // Default to free plan
                $planId = $freePlan?->id ?? 1;

                DB::table('subscriptions')->insert([
                    'user_id' => $userId,
                    'plan_id' => $planId,
                    'status' => $sub->status ?? 'active',
                    'apple_transaction_id' => null,
                    'apple_original_transaction_id' => null,
                    'current_period_start' => $sub->current_period_start ?? $sub->created_at,
                    'current_period_end' => $sub->current_period_end ?? $sub->expires_at ?? now()->addYear(),
                    'cancelled_at' => $sub->cancelled_at ?? $sub->canceled_at ?? null,
                    'created_at' => $sub->created_at,
                    'updated_at' => $sub->updated_at ?? $sub->created_at,
                ]);
                $count++;
            }

            $this->info("  ✓ Subscriptions migrated: {$count}");
        } catch (\Exception $e) {
            $this->warn("  Subscriptions migration error: " . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════
    // Summary
    // ═══════════════════════════════════════════════════

    private function showSummary(): void
    {
        if ($this->dryRun) {
            $this->newLine();
            $this->info('DRY RUN complete — no data was written.');
            return;
        }

        $this->newLine();
        $this->info('╔══════════════════════════════════════╗');
        $this->info('║        Migration Summary             ║');
        $this->info('╠══════════════════════════════════════╣');

        $counts = [
            'Users' => DB::table('users')->count(),
            'Categories' => DB::table('categories')->count(),
            'Decks' => DB::table('decks')->count(),
            'Cards' => DB::table('cards')->count(),
            'Card Progress' => DB::table('card_progress')->count(),
            'Badges' => DB::table('badges')->count(),
            'User Badges' => DB::table('user_badges')->count(),
            'Messages' => DB::table('messages')->count(),
            'Notifications' => DB::table('notifications')->count(),
            'Activity Logs' => DB::table('activity_logs')->count(),
            'Folders' => DB::table('folders')->count(),
            'UUID Mappings' => DB::table('uuid_mappings')->count(),
        ];

        foreach ($counts as $label => $count) {
            $this->info(sprintf('║  %-22s %8s    ║', $label, number_format($count)));
        }

        $this->info('╚══════════════════════════════════════╝');
    }
}
