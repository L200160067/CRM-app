<?php

namespace App\Livewire;

use App\Enums\InvoiceStatus;
use App\Models\Client;
use App\Models\ClientService;
use App\Models\Invoice;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    #[Computed]
    public function totalRevenue(): float
    {
        return (float) Invoice::query()
            ->where('status', InvoiceStatus::Paid)
            ->sum('grand_total');
    }

    #[Computed]
    public function outstanding(): float
    {
        return (float) Invoice::query()
            ->whereIn('status', [InvoiceStatus::Sent, InvoiceStatus::Overdue])
            ->sum('grand_total');
    }

    #[Computed]
    public function overdueCount(): int
    {
        return Invoice::query()
            ->where('status', InvoiceStatus::Overdue)
            ->count();
    }

    #[Computed]
    public function totalClients(): int
    {
        return Client::query()->count();
    }

    #[Computed]
    public function totalProducts(): int
    {
        return Product::query()->count();
    }

    #[Computed]
    public function totalServices(): int
    {
        return ClientService::query()->count();
    }

    /** @return array<string, int> */
    #[Computed]
    public function invoicesByStatus(): array
    {
        return Invoice::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();
    }

    #[Computed]
    public function recentInvoices()
    {
        return Invoice::query()
            ->with('client')
            ->latest()
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function recentClients()
    {
        return Client::query()
            ->latest()
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function expiringSoonServices()
    {
        return ClientService::query()
            ->with(['client', 'product'])
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now(), now()->addDays(30)])
            ->orderBy('expires_at')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function draftInvoicesCount(): int
    {
        return Invoice::query()
            ->where('status', InvoiceStatus::Draft)
            ->count();
    }

    #[Computed]
    public function overdueClientsCount(): int
    {
        return Invoice::query()
            ->where('status', InvoiceStatus::Overdue)
            ->distinct('client_id')
            ->count('client_id');
    }

    #[Computed]
    public function sentInvoicesCount(): int
    {
        return Invoice::query()
            ->where('status', InvoiceStatus::Sent)
            ->count();
    }

    #[Computed]
    public function newClientsThisMonthCount(): int
    {
        return Client::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    public function render(): \Illuminate\View\View
    {
        return view('livewire.dashboard');
    }
}
