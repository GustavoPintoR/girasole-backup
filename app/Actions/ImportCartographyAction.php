<?php

namespace App\Actions;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;
use ZipArchive;

class ImportCartographyAction
{
    use AsAction;
    use AsCommand;

    public string $commandSignature = 'cartography:download';

    public string $commandDescription = 'Download, unzip, and store the cartography file in private storage';

    // https://www.agenziaentrate.gov.it/portale/accedi-al-servizio-cartografici
    public function handle(?Command $command = null)
    {
        $url = 'https://wfs.cartografia.agenziaentrate.gov.it/inspire/wfs/GetDataset.php?dataset=ITALIA.zip'; // Replace with actual URL
        $localPath = storage_path('app/private/Cartography.zip');
        $extractPath = storage_path('app/private/cartography');

        // Stream download directly to file to avoid timeouts and memory issues
        $downloadedBytes = 0;
        $downloadTotal = 0;
        $progressBar = null;
        $options = [
            'sink' => $localPath,
            // No timeout for very large files
            'progress' => function ($total, $downloaded, $uploadTotal, $uploaded) use (&$progressBar, &$downloadTotal, &$downloadedBytes, $command) {
                if ($progressBar === null && $command && $total > 0) {
                    $downloadTotal = $total;
                    $progressBar = $command->getOutput()->createProgressBar((int) ceil($downloadTotal / 1048576)); // MB
                    $progressBar->setFormat(' %current%/%max% MB [%bar%] %percent:3s%%');
                    $progressBar->start();
                }
                if ($progressBar && $total > 0) {
                    $progressBar->setProgress((int) ceil($downloaded / 1048576));
                }
            },
        ];
        $response = Http::withOptions($options)->get($url);
        if ($progressBar) {
            $progressBar->finish();
            $command?->newLine();
        }
        // Guzzle does not return a Laravel response object when using 'sink', so check file existence
        if (! file_exists($localPath) || filesize($localPath) === 0) {
            return 1;
        }

        $zipPath = Storage::disk('local')->path('Cartography.zip');
        $zip = new ZipArchive;
        if ($zip->open($zipPath) === true) {
            $zip->extractTo(Storage::disk('local')->path('cartography'));
            $zip->close();
        }
        // Delete Cartography.zip after extraction
        Storage::disk('local')->delete('Cartography.zip');

        return 0;
    }

    public function asCommand(Command $command): void
    {
        $command->info('Downloading cartography file (this may take a while)...');
        // Show a simple spinner while downloading
        $spinner = ['|', '/', '-', '\\'];
        $i = 0;
        $done = false;
        // Start download with progress bar
        $result = $this->handle($command);
        if ($result === 0) {
            $command->info('Cartography file downloaded and extracted successfully.');
        } elseif ($result === 1) {
            $command->error('Failed to download cartography file.');
        } elseif ($result === 2) {
            $command->error('Failed to extract cartography file.');
        }

    }
}
