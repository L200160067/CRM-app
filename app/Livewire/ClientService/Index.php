<?php

namespace App\Livewire\ClientService;

use App\Models\ClientService;
use Flux;
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

    public ?int $clientServiceIdToDelete = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $this->clientServiceIdToDelete = $id;
    }

    public function delete(): void
    {
        if ($this->clientServiceIdToDelete) {
            $service = ClientService::findOrFail($this->clientServiceIdToDelete);
            $this->authorize('delete', $service);
            $service->delete();

            Flux::modal('client-service-delete-modal')->close();
            Flux::toast('Layanan berhasil dihapus.');

            $this->clientServiceIdToDelete = null;
            unset($this->services);
        }
    }

    #[On('client-service-saved')]
    public function refreshTable(): void
    {
        unset($this->services);
    }

    #[Computed]
    public function services()
    {
        return ClientService::with(['client', 'product'])
            ->when($this->search, function ($query) {
                $query->where('domain_name', 'like', '%'.$this->search.'%')
                    ->orWhereHas('client', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('company_name', 'like', '%'.$this->search.'%');
                    })
                    ->orWhereHas('product', function ($q) {
                        $q->where('name', 'like', '%'.$this->search.'%');
                    });
            })
            ->latest()
            ->paginate(10);
    }

    public function render()
    {
        return view('livewire.client-service.index');
    }
}
