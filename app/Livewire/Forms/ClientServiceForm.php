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

    public string $status = \App\Enums\ServiceStatus::Active->value;

    public string $billing_cycle = \App\Enums\ServiceBillingCycle::Yearly->value;

    public ?float $recurring_price = null;

    public string $started_at = '';

    public string $expires_at = '';

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'product_id' => ['required', 'exists:products,id'],
            'domain_name' => ['nullable', 'string', 'max:255'],
            'status' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\ServiceStatus::class)],
            'billing_cycle' => ['required', \Illuminate\Validation\Rule::enum(\App\Enums\ServiceBillingCycle::class)],
            'recurring_price' => ['nullable', 'numeric', 'min:0'],
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
        $this->status = $clientService->status->value;
        $this->billing_cycle = $clientService->billing_cycle->value;
        $this->recurring_price = $clientService->recurring_price;
        $this->started_at = $clientService->started_at ? $clientService->started_at->format('Y-m-d') : '';
        $this->expires_at = $clientService->expires_at ? $clientService->expires_at->format('Y-m-d') : '';
    }

    public function calculateExpiresAt(): void
    {
        if (empty($this->started_at)) {
            return;
        }

        try {
            $start = \Carbon\Carbon::parse($this->started_at);
            if ($this->billing_cycle === \App\Enums\ServiceBillingCycle::Monthly->value) {
                $this->expires_at = $start->addMonth()->format('Y-m-d');
            } elseif ($this->billing_cycle === \App\Enums\ServiceBillingCycle::Yearly->value) {
                $this->expires_at = $start->addYear()->format('Y-m-d');
            }
        } catch (\Exception $e) {
            // Invalid date format, ignore
        }
    }

    public function autoFillPrice(): void
    {
        if ($this->product_id) {
            $product = \App\Models\Product::find($this->product_id);
            if ($product) {
                $this->recurring_price = (float) $product->default_price;
            }
        }
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
