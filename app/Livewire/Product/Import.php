<?php

namespace App\Livewire\Product;

use App\Models\Product;
use Flux;
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

        $header = array_map(fn ($h) => strtolower(trim($h)), $header);

        $lineNumber = 1;

        while (($raw = fgetcsv($handle)) !== false) {
            $lineNumber++;

            if (array_filter($raw) === []) {
                continue;
            }

            $data = array_combine($header, array_pad($raw, count($header), null)) ?: [];

            $row = [
                'line' => $lineNumber,
                'name' => trim($data['name'] ?? ''),
                'description' => trim($data['description'] ?? ''),
                'default_price' => trim($data['default_price'] ?? ''),
                'errors' => [],
                'valid' => true,
            ];

            $validator = Validator::make($row, [
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'default_price' => ['required', 'numeric', 'min:0'],
            ], [
                'default_price.required' => 'Harga wajib diisi.',
                'default_price.numeric' => 'Harga harus berupa angka.',
                'default_price.min' => 'Harga tidak boleh negatif.',
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
        $this->authorize('create', Product::class);

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

            Product::create([
                'name' => $row['name'],
                'description' => $row['description'] ?: null,
                'default_price' => (float) $row['default_price'],
            ]);

            $imported++;
        }

        $this->importedCount = $imported;
        $this->skippedCount = $skipped;
        $this->importDone = true;
        $this->csvFile = null;
        $this->rows = [];

        $this->dispatch('product-saved');

        Flux::toast("{$imported} produk berhasil diimport.".($skipped > 0 ? " {$skipped} baris dilewati." : ''));
    }

    public function downloadTemplate(): StreamedResponse
    {
        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['name', 'description', 'default_price']);
            fputcsv($handle, ['Jasa Desain Web', 'Pembuatan website company profile', '2500000']);
            fputcsv($handle, ['Jasa Maintenance', 'Pemeliharaan bulanan aplikasi', '750000']);
            fclose($handle);
        }, 'template-import-produk.csv', ['Content-Type' => 'text/csv']);
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
        return view('livewire.product.import');
    }
}
