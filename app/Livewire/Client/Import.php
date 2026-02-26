<?php

namespace App\Livewire\Client;

use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Import extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $csvFile = null;

    /** @var array<int, array<string, mixed>> */
    public array $rows = [];

    public int $importedCount = 0;

    public int $skippedCount = 0;

    public bool $importDone = false;

    public function updatedCsvFile(): void
    {
        $this->rows = [];
        $this->importDone = false;

        if (! $this->csvFile) {
            return;
        }

        $path = $this->csvFile->getRealPath();
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return;
        }

        $header = fgetcsv($handle);

        if (! $header) {
            fclose($handle);

            return;
        }

        // Normalize headers (trim + lowercase)
        $header = array_map(fn ($h) => strtolower(trim($h)), $header);

        $lineNumber = 1;

        while (($raw = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (array_filter($raw) === []) {
                continue; // skip empty rows
            }

            $data = array_combine($header, array_pad($raw, count($header), null)) ?: [];

            $row = [
                'line' => $lineNumber,
                'name' => trim($data['name'] ?? ''),
                'company_name' => trim($data['company_name'] ?? ''),
                'email' => trim($data['email'] ?? ''),
                'phone' => trim($data['phone'] ?? ''),
                'address' => trim($data['address'] ?? ''),
                'city' => trim($data['city'] ?? ''),
                'errors' => [],
                'valid' => true,
            ];

            $validator = Validator::make($row, [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['nullable', 'email', 'max:255', 'unique:clients,email'],
                'company_name' => ['nullable', 'string', 'max:255'],
                'phone' => ['nullable', 'string', 'max:255'],
                'address' => ['nullable', 'string'],
                'city' => ['nullable', 'string', 'max:255'],
            ]);

            if ($validator->fails()) {
                $row['errors'] = $validator->errors()->all();
                $row['valid'] = false;
            }

            $this->rows[] = $row;
        }

        fclose($handle);
    }

    public function processImport(): void
    {
        $this->authorize('create', Client::class);

        if (empty($this->rows)) {
            return;
        }

        $imported = 0;
        $skipped = 0;

        foreach ($this->rows as $row) {
            if (! $row['valid']) {
                $skipped++;

                continue;
            }

            Client::create([
                'name' => $row['name'],
                'company_name' => $row['company_name'] ?: null,
                'email' => $row['email'] ?: null,
                'phone' => $row['phone'] ?: null,
                'address' => $row['address'] ?: null,
                'city' => $row['city'] ?: null,
            ]);

            $imported++;
        }

        $this->importedCount = $imported;
        $this->skippedCount = $skipped;
        $this->importDone = true;
        $this->csvFile = null;
        $this->rows = [];

        $this->dispatch('client-saved');

        \Flux::toast("{$imported} klien berhasil diimport.".($skipped > 0 ? " {$skipped} baris dilewati." : ''));
    }

    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'company_name', 'email', 'phone', 'address', 'city']);
            fputcsv($handle, ['PT Maju Jaya', 'PT Maju Jaya Tbk', 'contact@maju.id', '08123456789', 'Jl. Sudirman No. 1', 'Jakarta']);
            fclose($handle);
        }, 'template-import-klien.csv', ['Content-Type' => 'text/csv']);
    }

    public function resetImport(): void
    {
        $this->csvFile = null;
        $this->rows = [];
        $this->importDone = false;
        $this->importedCount = 0;
        $this->skippedCount = 0;
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.client.import');
    }
}
