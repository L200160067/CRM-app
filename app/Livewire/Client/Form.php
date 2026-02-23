<?php

namespace App\Livewire\Client;

use App\Livewire\Forms\ClientForm;
use App\Models\Client;
use Flux;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Component;

class Form extends Component
{
    use AuthorizesRequests;

    public ClientForm $form;

    public string $mode = 'create';

    // Dengarkan event dari komponen manapun untuk memuat data
    #[On('load-client-form')]
    public function loadClient(?int $id = null)
    {
        $this->form->reset();
        $this->form->resetValidation();

        if ($id) {
            $this->mode = 'edit';
            $client = Client::findOrFail($id);
            $this->authorize('update', $client);
            $this->form->setClient($client);
        } else {
            $this->mode = 'create';
            $this->authorize('create', Client::class);
        }
    }

    public function save()
    {
        if ($this->mode === 'create') {
            $this->authorize('create', Client::class);
            $this->form->store();
        } else {
            $this->authorize('update', $this->form->client);
            $this->form->update();
        }

        // 1. Tutup modal menggunakan facade Flux PHP
        Flux::modal('client-form-modal')->close();

        // 2. Beritahu tabel untuk refresh data secara global
        $this->dispatch('client-saved');

        // 3. Opsional: Kirim Toast Notification
        Flux::toast('Client berhasil disimpan.');
    }

    public function render()
    {
        return view('livewire.client.form');
    }
}
