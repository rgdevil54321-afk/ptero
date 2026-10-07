<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Pterodactyl\Models\Nest;
use Pterodactyl\Services\Nests\NestCreationService;
use Pterodactyl\Contracts\Repository\NestRepositoryInterface;
use Pterodactyl\Services\Eggs\Sharing\EggImporterService;
use Pterodactyl\Services\Eggs\Sharing\EggUpdateImporterService;
use Illuminate\Http\UploadedFile;
use Pterodactyl\Models\Egg;

class HyperEggSeeder extends Seeder
{
    private const NESTS_TO_SEED = ['Utilities'];

    private NestCreationService $nestCreationService;
    private NestRepositoryInterface $nestRepository;
    private EggImporterService $importerService;
    private EggUpdateImporterService $updateImporterService;

    public function __construct(
        NestCreationService $nestCreationService,
        NestRepositoryInterface $nestRepository,
        EggImporterService $importerService,
        EggUpdateImporterService $updateImporterService
    ) {
        $this->nestCreationService = $nestCreationService;
        $this->nestRepository = $nestRepository;
        $this->importerService = $importerService;
        $this->updateImporterService = $updateImporterService;
    }

    public function run(): void
    {
        $this->command->info('Seeding Hyper nests and eggs...');

        $items = $this->nestRepository->findWhere([
            'author' => 'support@pterodactyl.io',
        ])->keyBy('name')->toArray();

        foreach (self::NESTS_TO_SEED as $nestName) {
            if (!isset($items[$nestName])) {
                $this->command->info("Creating nest: {$nestName}");
                $this->createNest($nestName);
            }
        }

        foreach (self::NESTS_TO_SEED as $nestName) {
            $nest = Nest::query()
                ->where('author', 'support@pterodactyl.io')
                ->where('name', $nestName)
                ->first();

            if (!$nest) {
                $this->command->warn("Nest '{$nestName}' not found, skipping egg import.");
                continue;
            }

            $this->importEggsForNest($nest);
        }

        $this->command->info('Hyper egg seeding complete.');
    }

    private function createNest(string $name): void
    {
        $descriptions = [
            'Utilities' => 'Shared utilities and tools for game server management.',
        ];

        try {
            $this->nestCreationService->handle([
                'name' => $name,
                'description' => $descriptions[$name] ?? "{$name} nest.",
            ], 'support@pterodactyl.io');
        } catch (\Throwable $e) {
            $this->command->warn("Could not create nest '{$name}': {$e->getMessage()}");
        }
    }

    private function importEggsForNest(Nest $nest): void
    {
        $directory = database_path("Seeders/eggs/{$nest->name}");
        if (!is_dir($directory)) {
            $this->command->warn("No egg directory found: {$directory}");
            return;
        }

        $files = new \DirectoryIterator($directory);
        foreach ($files as $file) {
            if (!$file->isFile() || !$file->isReadable() || $file->getExtension() !== 'json') {
                continue;
            }

            $decoded = json_decode(file_get_contents($file->getRealPath()));
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->command->warn("Skipping invalid JSON: {$file->getFilename()}");
                continue;
            }

            $uploadedFile = new UploadedFile($file->getRealPath(), $file->getFilename(), 'application/json');

            $existing = $nest->eggs()
                ->where('author', $decoded->author ?? 'unknown')
                ->where('name', $decoded->name ?? 'unknown')
                ->first();

            if ($existing) {
                $this->command->info("Updating egg: {$decoded->name}");
                try {
                    $this->updateImporterService->handle($existing, $uploadedFile);
                } catch (\Throwable $e) {
                    $this->command->warn("Failed to update egg {$decoded->name}: {$e->getMessage()}");
                }
            } else {
                $this->command->info("Importing egg: {$decoded->name}");
                try {
                    $this->importerService->handle($uploadedFile, $nest->id);
                } catch (\Throwable $e) {
                    $this->command->warn("Failed to import egg {$decoded->name}: {$e->getMessage()}");
                }
            }
        }
    }
}
