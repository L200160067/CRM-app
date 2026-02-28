<?php

namespace App\Livewire\ClientService;

use App\Livewire\Forms\ClientServiceForm;
use App\Models\Client;
use App\Models\ClientService;
use App\Models\Product;
use Flux;
use Livewire\Attributes\On;
use Livewire\Component;

class Form extends Component
{
    public ClientServiceForm $form;

    public string $mode = 'create';

    #[On('load-client-service-form')]
    public function loadClientService(?int $id = null): void
    {
        $this->form->reset();
        $this->form->resetValidation();

        if ($id) {
            $this->mode = 'edit';
            $clientService = ClientService::findOrFail($id);
            $this->form->setClientService($clientService);
        } else {
            $this->mode = 'create';
            $this->form->started_at = today()->format('Y-m-d');
            $this->form->expires_at = today()->addYear()->format('Y-m-d');
        }
    }

    public function save(): void
    {
        if ($this->mode === 'create') {
            $this->form->store();
        } else {
            $this->form->update();
        }

        Flux::modal('client-service-form-modal')->close();

        $this->dispatch('client-service-saved');

        Flux::toast($this->mode === 'create'
            ? 'Layanan berhasil ditambahkan.'
            : 'Layanan berhasil diperbarui.'
        );
    }

    public function render()
    {
        return view('livewire.client-service.form', [
            'clients' => Client::orderBy('name')->get(),
            'products' => Product::orderBy('name')->get(),
        ]);
    }
}
