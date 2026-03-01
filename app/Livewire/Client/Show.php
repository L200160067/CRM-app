<?php

namespace App\Livewire\Client;

use App\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Attributes\On;
use Livewire\Component;

class Show extends Component
{
    use AuthorizesRequests;

    public ?Client $client = null;

    #[On('load-client-show')]
    public function loadClient(int $id)
    {
        $this->client = Client::findOrFail($id);
        $this->authorize('view', $this->client);
    }

    public function render()
    {
        return view('livewire.client.show');
    }
}
