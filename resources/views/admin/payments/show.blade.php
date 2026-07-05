@extends('layouts.admin')

@section('title', 'Payment '.$payment->transaction_id)

@section('content')
    @php
        $badgeVariant = match ($payment->status) {
            'paid' => 'green',
            'initiated' => 'yellow',
            'failed', 'cancelled' => 'red',
            default => 'neutral',
        };
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $payment->transaction_id }}</h1>
            <p class="text-sm text-gray-600">
                Recorded on {{ $payment->created_at->format('M j, Y \a\t g:ia') }}
            </p>
        </div>

        <x-ui.badge variant="{{ $badgeVariant }}">{{ ucfirst($payment->status) }}</x-ui.badge>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card>
                <h2 class="font-semibold text-gray-900 mb-4">Raw Gateway Response</h2>

                @if ($payment->gateway_response)
                    <pre class="bg-gray-900 text-gray-100 text-xs rounded-lg p-4 overflow-x-auto">{{ json_encode($payment->gateway_response, JSON_PRETTY_PRINT) }}</pre>
                @else
                    <p class="text-sm text-gray-500">No gateway response recorded yet.</p>
                @endif
            </x-ui.card>
        </div>

        <div class="lg:col-span-1 space-y-6">
            <x-ui.card>
                <h2 class="font-semibold text-gray-900 mb-4">Transaction Details</h2>

                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Gateway</dt>
                        <dd class="text-gray-900 font-medium">{{ ucfirst($payment->gateway) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Amount</dt>
                        <dd class="text-gray-900 font-medium"><x-ui.money :value="$payment->amount" /></dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Validation ID</dt>
                        <dd class="text-gray-900 font-medium">{{ $payment->val_id ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Paid at</dt>
                        <dd class="text-gray-900 font-medium">{{ $payment->paid_at?->format('M j, Y g:ia') ?? '—' }}</dd>
                    </div>
                </dl>
            </x-ui.card>

            <x-ui.card>
                <h2 class="font-semibold text-gray-900 mb-4">Order</h2>

                @if ($payment->order)
                    <dl class="space-y-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Order #</dt>
                            <dd class="text-gray-900 font-medium">
                                <a href="{{ route('admin.orders.show', $payment->order) }}" class="text-brand-700 hover:underline">{{ $payment->order->order_number }}</a>
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Customer</dt>
                            <dd class="text-gray-900 font-medium">
                                @if ($payment->order->user)
                                    <a href="{{ route('admin.customers.show', $payment->order->user) }}" class="text-brand-700 hover:underline">{{ $payment->order->user->name }}</a>
                                @else
                                    Guest
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Order total</dt>
                            <dd class="text-gray-900 font-medium"><x-ui.money :value="$payment->order->total" /></dd>
                        </div>
                    </dl>
                @else
                    <p class="text-sm text-gray-500">No associated order.</p>
                @endif
            </x-ui.card>
        </div>
    </div>
@endsection
