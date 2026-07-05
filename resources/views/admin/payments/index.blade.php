@extends('layouts.admin')

@section('title', 'Payments')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Payments</h1>
            <p class="text-sm text-gray-600">View and filter payment transactions.</p>
        </div>
    </div>

    <x-ui.card class="mb-6">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 lg:items-end">
            <fieldset class="fieldset">
                <legend class="fieldset-legend">Status</legend>
                <select name="status" class="select w-full" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach (['initiated', 'paid', 'failed', 'cancelled'] as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">Gateway</legend>
                <select name="gateway" class="select w-full" onchange="this.form.submit()">
                    <option value="">All gateways</option>
                    @foreach (['sslcommerz'] as $option)
                        <option value="{{ $option }}" @selected($gateway === $option)>{{ ucfirst($option) }}</option>
                    @endforeach
                </select>
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">From</legend>
                <input type="date" name="from" value="{{ $from }}" class="input w-full">
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">To</legend>
                <input type="date" name="to" value="{{ $to }}" class="input w-full">
            </fieldset>

            <div class="flex gap-2">
                <x-ui.button type="submit" variant="primary">Filter</x-ui.button>
                @if ($status || $gateway || $from || $to)
                    <x-ui.button as="a" href="{{ route('admin.payments.index') }}" variant="ghost">Reset</x-ui.button>
                @endif
            </div>
        </form>
    </x-ui.card>

    <x-ui.card class="p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Gateway</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        @php
                            $badgeVariant = match ($payment->status) {
                                'paid' => 'green',
                                'initiated' => 'yellow',
                                'failed', 'cancelled' => 'red',
                                default => 'neutral',
                            };
                        @endphp
                        <tr>
                            <td class="font-medium text-gray-900">{{ $payment->transaction_id }}</td>
                            <td class="text-gray-500">
                                @if ($payment->order)
                                    <a href="{{ route('admin.orders.show', $payment->order) }}" class="text-brand-700 hover:underline">{{ $payment->order->order_number }}</a>
                                @else
                                    &mdash;
                                @endif
                            </td>
                            <td class="text-gray-500">{{ $payment->order?->user?->name ?? 'Guest' }}</td>
                            <td class="text-gray-500">{{ ucfirst($payment->gateway) }}</td>
                            <td class="text-gray-500">{{ $payment->created_at->format('M j, Y') }}</td>
                            <td class="text-gray-900"><x-ui.money :value="$payment->amount" /></td>
                            <td><x-ui.badge variant="{{ $badgeVariant }}">{{ ucfirst($payment->status) }}</x-ui.badge></td>
                            <td class="text-right whitespace-nowrap">
                                <x-ui.button as="a" href="{{ route('admin.payments.show', $payment) }}" variant="secondary" size="sm">
                                    View
                                </x-ui.button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-gray-500 py-8">No payments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>

    <div class="mt-4">
        <x-ui.pagination-links :paginator="$payments" />
    </div>
@endsection
