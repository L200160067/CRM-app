<?php

namespace App\Livewire\Forms;

use App\Models\ClientService;
use Livewire\Form;

class ClientServiceForm extends Form
{
    public ?ClientService $clientService = null;

    public ?int $client_id = null;

    public ?int $product_id = null;

    public ?string $domain_name = null;

    public string $status = 'Active';

    public string $billing_cycle = 'Yearly';

    public string $started_at = '';

    public string $expires_at = '';

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'product_id' => ['required', 'exists:products,id'],
            'domain_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:Active,Suspended,Expired,Pending'],
            'billing_cycle' => ['required', 'in:Monthly,Yearly'],
            'started_at' => ['required', 'date'],
            'expires_at' => ['required', 'date', 'after_or_equal:started_at'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Klien harus dipilih.',
            'product_id.required' => 'Produk/layanan harus dipilih.',
            'started_at.required' => 'Tanggal mulai harus diisi.',
            'expires_at.required' => 'Tanggal berakhir harus diisi.',
            'expires_at.after_or_equal' => 'Tanggal berakhir harus setelah atau sama dengan tanggal mulai.',
        ];
    }

    public function setClientService(ClientService $clientService): void
    {
        $this->clientService = $clientService;
        $this->client_id = $clientService->client_id;
        $this->product_id = $clientService->product_id;
        $this->domain_name = $clientService->domain_name;
        $this->status = $clientService->status;
        $this->billing_cycle = $clientService->billing_cycle;
        $this->started_at = $clientService->started_at ? $clientService->started_at->format('Y-m-d') : '';
        $this->expires_at = $clientService->expires_at ? $clientService->expires_at->format('Y-m-d') : '';
    }

    public function store(): void
    {
        $this->validate();

        ClientService::create($this->except('clientService'));

        $this->reset();
    }

    public function update(): void
    {
        $this->validate();

        $this->clientService->update($this->except('clientService'));

        $this->reset();
    }
}
