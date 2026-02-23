<?php

namespace App\Livewire\Forms;

use App\Models\Client;
use Illuminate\Validation\Rule;
use Livewire\Form;

class ClientForm extends Form
{
    public ?Client $client = null;

    public string $name = '';

    public ?string $company_name = null;

    public ?string $email = null;

    public ?string $phone = null;

    public ?string $address = null;

    public ?string $city = null;

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('clients', 'email')->ignore($this->client?->id),
            ],
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'city' => 'nullable|string|max:255',
        ];
    }

    public function setClient(Client $client)
    {
        $this->client = $client;
        $this->name = $client->name;
        $this->company_name = $client->company_name;
        $this->email = $client->email;
        $this->phone = $client->phone;
        $this->address = $client->address;
        $this->city = $client->city;
    }

    public function store()
    {
        $this->validate();
        Client::create($this->except('client'));
        $this->reset();
    }

    public function update()
    {
        $this->validate();
        $this->client->update($this->except('client'));
        $this->reset();
    }
}
