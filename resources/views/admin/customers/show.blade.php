@extends('layouts.admin')

@section('title', $customer->name)

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div class="flex items-center gap-4">
            <div class="avatar">
                <div class="w-16 rounded-full">
                    <img src="{{ $customer->avatarUrl() }}" alt="{{ $customer->name }}">
                </div>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $customer->name }}</h1>
                <p class="text-sm text-gray-600">{{ $customer->email }} &middot; {{ $customer->phone ?? 'No phone on file' }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <x-ui.badge :variant="$customer->is_blocked ? 'red' : 'green'">
                {{ $customer->is_blocked ? 'Blocked' : 'Active' }}
            </x-ui.badge>

            @unless ($customer->is_blocked)
                <form method="POST" action="{{ route('admin.customers.impersonate', $customer) }}">
                    @csrf
                    <x-ui.button type="submit" variant="secondary" size="sm">
                        Log in as this user
                    </x-ui.button>
                </form>
            @endunless

            <form method="POST" action="{{ route('admin.customers.toggle-block', $customer) }}">
                @csrf
                <x-ui.button type="submit" variant="{{ $customer->is_blocked ? 'primary' : 'danger' }}" size="sm">
                    {{ $customer->is_blocked ? 'Unblock customer' : 'Block customer' }}
                </x-ui.button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card>
                <h2 class="font-semibold text-gray-900 mb-4">Orders</h2>

                @if ($orders->isEmpty())
                    <p class="text-sm text-gray-500">This customer hasn't placed any orders yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    @php
                                        $badgeVariant = match ($order->status) {
                                            'delivered' => 'green',
                                            'shipped', 'processing' => 'blue',
                                            'cancelled' => 'red',
                                            default => 'neutral',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="font-medium text-gray-900">{{ $order->order_number }}</td>
                                        <td class="text-gray-500">{{ $order->created_at->format('M j, Y') }}</td>
                                        <td class="text-gray-500">{{ $order->items_count }}</td>
                                        <td class="text-gray-900"><x-ui.money :value="$order->total" /></td>
                                        <td><x-ui.badge variant="{{ $badgeVariant }}">{{ ucfirst($order->status) }}</x-ui.badge></td>
                                        <td class="text-right whitespace-nowrap">
                                            <x-ui.button as="a" href="{{ route('admin.orders.show', $order) }}" variant="secondary" size="sm">
                                                View
                                            </x-ui.button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        <x-ui.pagination-links :paginator="$orders" />
                    </div>
                @endif
            </x-ui.card>
        </div>

        <div class="lg:col-span-1">
            <x-ui.card class="mb-6">
                <h2 class="font-semibold text-gray-900 mb-4">Profile</h2>

                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-500">Bio</dt>
                        <dd class="text-gray-900">{{ $customer->profile?->bio ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <div>
                            <dt class="text-gray-500">Date of birth</dt>
                            <dd class="text-gray-900">{{ $customer->profile?->date_of_birth?->format('M j, Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Gender</dt>
                            <dd class="text-gray-900">{{ $customer->profile?->gender ? ucfirst($customer->profile->gender) : '—' }}</dd>
                        </div>
                    </div>
                    <div>
                        <dt class="text-gray-500">Joined</dt>
                        <dd class="text-gray-900">{{ $customer->created_at->format('M j, Y') }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card>
                <h2 class="font-semibold text-gray-900 mb-4">Addresses</h2>

                @if ($customer->addresses->isEmpty())
                    <p class="text-sm text-gray-500">No addresses on file.</p>
                @else
                    <div class="space-y-4">
                        @foreach ($customer->addresses as $address)
                            <div class="text-sm text-gray-600 border-b border-base-200 last:border-0 pb-4 last:pb-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <p class="font-medium text-gray-900">{{ $address->label }}</p>
                                    @if ($address->is_default)
                                        <x-ui.badge variant="blue">Default</x-ui.badge>
                                    @endif
                                </div>
                                <address class="not-italic">
                                    {{ $address->line1 }}<br>
                                    @if ($address->line2)
                                        {{ $address->line2 }}<br>
                                    @endif
                                    {{ $address->city }}, {{ $address->state }} {{ $address->postal_code }}<br>
                                    {{ $address->country }}
                                </address>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.card>
        </div>
    </div>
@endsection
