@extends('layouts.admin')

@section('title', 'Customers')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Customers</h1>
            <p class="text-sm text-gray-600">Search customers and manage their access.</p>
        </div>
    </div>

    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('admin.customers.index') }}" class="flex flex-col sm:flex-row gap-4 sm:items-end">
            <fieldset class="fieldset flex-1">
                <legend class="fieldset-legend">Search</legend>
                <input
                    type="text"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Name or email"
                    class="input w-full"
                />
            </fieldset>

            <div class="flex gap-2">
                <x-ui.button type="submit" variant="primary">Search</x-ui.button>
                @if ($search)
                    <x-ui.button as="a" href="{{ route('admin.customers.index') }}" variant="ghost">Reset</x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    <x-ui.card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Orders</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($customers as $customer)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="avatar">
                                        <div class="w-9 rounded-full">
                                            <img src="{{ $customer->avatarUrl() }}" alt="{{ $customer->name }}">
                                        </div>
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $customer->name }}</span>
                                </div>
                            </td>
                            <td class="text-gray-500">{{ $customer->email }}</td>
                            <td class="text-gray-500">{{ $customer->phone ?? '—' }}</td>
                            <td class="text-gray-500">{{ $customer->orders_count }}</td>
                            <td>
                                <x-ui.badge :variant="$customer->is_blocked ? 'red' : 'green'">
                                    {{ $customer->is_blocked ? 'Blocked' : 'Active' }}
                                </x-ui.badge>
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <x-ui.button as="a" href="{{ route('admin.customers.show', $customer) }}" variant="secondary" size="sm">
                                    View
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-gray-500 py-8">No customers found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-4">
        <x-ui.pagination-links :paginator="$customers" />
    </div>
@endsection
