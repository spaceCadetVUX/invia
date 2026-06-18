<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateStorageToR2 extends Command
{
    protected $signature   = 'storage:migrate-to-r2 {--dry-run : Liệt kê files mà không upload}';
    protected $description = 'Copy tất cả files từ local disk lên Cloudflare R2';

    public function handle(): void
    {
        $local   = Storage::disk('local');
        $r2      = Storage::disk('r2');
        $dryRun  = $this->option('dry-run');

        $files = $local->allFiles();

        if (empty($files)) {
            $this->info('Không có files nào trong local storage.');
            return;
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '') . count($files) . ' files sẽ được migrate.');

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        $errors = [];

        foreach ($files as $file) {
            if (!$dryRun) {
                try {
                    $stream = $local->readStream($file);
                    $r2->writeStream($file, $stream);
                    if (is_resource($stream)) {
                        fclose($stream);
                    }
                } catch (\Throwable $e) {
                    $errors[] = "{$file}: {$e->getMessage()}";
                }
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($errors) {
            $this->error(count($errors) . ' files thất bại:');
            foreach ($errors as $err) {
                $this->line("  - {$err}");
            }
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '') . count($files) . ' files processed.');

        if (!$dryRun) {
            $this->line('');
            $this->line('Bước tiếp theo:');
            $this->line('  1. Đổi FILESYSTEM_DISK=r2 trong .env');
            $this->line('  2. Test upload + download');
            $this->line('  3. Xóa local files sau khi verified OK');
        }
    }
}
