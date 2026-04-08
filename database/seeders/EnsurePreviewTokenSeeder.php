<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Ensures PREVIEW_TOKEN is set in the project's .env file.
 *
 * Behaviour:
 *  - If PREVIEW_TOKEN is already present AND non-empty → leave it alone
 *  - If PREVIEW_TOKEN is present but empty (e.g. copied from .env.example) → fill it in
 *  - If PREVIEW_TOKEN line is missing entirely → append it with a comment
 *
 * Re-running the seeder is always safe — it's fully idempotent.
 */
class EnsurePreviewTokenSeeder extends Seeder
{
    public function run(): void
    {
        $envPath = base_path('.env');

        if (!file_exists($envPath)) {
            $this->command?->warn('EnsurePreviewTokenSeeder: .env file not found — skipping. Create .env from .env.example first.');
            return;
        }

        $envContents = file_get_contents($envPath);
        $token = bin2hex(random_bytes(32));

        if (preg_match('/^PREVIEW_TOKEN=(.*)$/m', $envContents, $matches)) {
            $currentValue = trim($matches[1]);

            if ($currentValue !== '') {
                // Already set to a real value, never overwrite
                $this->command?->info('EnsurePreviewTokenSeeder: PREVIEW_TOKEN already set, skipping.');
                return;
            }

            // Empty — fill it in
            $newEnv = preg_replace(
                '/^PREVIEW_TOKEN=.*$/m',
                "PREVIEW_TOKEN={$token}",
                $envContents
            );
        } else {
            // Line missing entirely — append with a comment
            if (!str_ends_with($envContents, "\n")) {
                $envContents .= "\n";
            }
            $newEnv = $envContents
                . "\n# Shared secret for the /api/public/preview endpoint (dark preview)\n"
                . "PREVIEW_TOKEN={$token}\n";
        }

        // Write with error handling — .env may be read-only in some deploys
        if (@file_put_contents($envPath, $newEnv) === false) {
            $this->command?->error('EnsurePreviewTokenSeeder: could not write to .env (permission denied?).');
            $this->command?->warn('Add this line manually to .env:');
            $this->command?->line("PREVIEW_TOKEN={$token}");
            return;
        }

        // Make the new token available to any subsequent seeders / artisan calls
        // in this same process. A running `php artisan serve` will still hold
        // the OLD cached config, so we warn the user to restart it.
        config(['app.preview_token' => $token]);

        $this->command?->info('EnsurePreviewTokenSeeder: generated new PREVIEW_TOKEN and saved to .env.');
        $this->command?->line('  Value: ' . substr($token, 0, 16) . '... (64 chars total)');
        $this->command?->warn('  If `php artisan serve` is running, restart it to pick up the new token.');
    }
}
