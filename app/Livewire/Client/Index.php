<?php

namespace App\Livewire\Client;

use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    // Filter status table
    public string $status = 'active';

    public ?int $clientIdToDelete = null;

    public ?int $clientIdToForceDelete = null;

    public function confirmDelete(int $id)
    {
        $this->clientIdToDelete = $id;
    }

    public function delete()
    {
        if ($this->clientIdToDelete) {
            $client = Client::findOrFail($this->clientIdToDelete);
            $this->authorize('delete', $client);
            $client->delete(); // This triggers soft delete

            \Flux::modal('client-delete-modal')->close();
            \Flux::toast('Klien berhasil dihapus (Dipindahkan ke Trash).');

            $this->clientIdToDelete = null;
            unset($this->clients);
        }
    }

    public function restore(int $id)
    {
        $client = Client::onlyTrashed()->findOrFail($id);
        $this->authorize('restore', $client);
        $client->restore();
        \Flux::toast('Klien berhasil dipulihkan dari Trash.');
        unset($this->clients);
    }

    public function confirmForceDelete(int $id)
    {
        $this->clientIdToForceDelete = $id;
    }

    public function forceDelete()
    {
        if ($this->clientIdToForceDelete) {
            $client = Client::onlyTrashed()->findOrFail($this->clientIdToForceDelete);
            $this->authorize('forceDelete', $client);
            $client->forceDelete();

            \Flux::modal('client-force-delete-modal')->close();
            \Flux::toast('Klien berhasil dihapus secara permanen.');

            $this->clientIdToForceDelete = null;
            unset($this->clients);
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    #[Computed]
    public function clients()
    {
        $query = Client::query();

        if ($this->status === 'trashed') {
            $query->onlyTrashed();
        }

        return $query
            ->when($this->search, function ($q) {
                $q->where(function ($subQuery) {
                    $subQuery->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('company_name', 'like', '%'.$this->search.'%');
                });
            })
            ->latest()
            ->paginate(10);
    }

    // Hanya mereset halaman dan refresh tabel tanpa logic berbelit
    #[On('client-saved')]
    public function refreshTable()
    {
        // Livewire secara otomatis akan me-render ulang computed property
    }

    public function render()
    {
        return view('livewire.client.index');
    }
}
