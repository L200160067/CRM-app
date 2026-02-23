<?php

namespace App\Livewire\Client;

use App\Models\Client;
use Flux;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    public ?Client $client = null;

    #[On('load-client-show')]
    public function loadClient(int $id)
    {
        $this->client = Client::findOrFail($id);
    }

    public function render()
    {
        return view('livewire.client.show');
    }
}
