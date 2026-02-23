@props([
    'sidebar' => false,
])

@if($sidebar)
    <flux:sidebar.brand name="M-One Solution CRM" {{ $attributes }}>
        <x-slot name="logo" class="flex size-8 items-center justify-center rounded-md text-accent-foreground">
            <x-app-logo-icon class="max-w-full max-h-full" />
        </x-slot>
    </flux:sidebar.brand>
@else
    <flux:brand name="M-One Solution CRM" {{ $attributes }}>
        <x-slot name="logo" class="flex size-8 items-center justify-center rounded-md text-accent-foreground">
            <x-app-logo-icon class="max-w-full max-h-full" />
        </x-slot>
    </flux:brand>
@endif
