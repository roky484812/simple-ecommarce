<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SslCommerzService
{
    private string $storeId;

    private string $storePassword;

    private string $baseUrl;

    public function __construct()
    {
        $this->storeId = (string) config('services.sslcommerz.store_id');
        $this->storePassword = (string) config('services.sslcommerz.store_password');
        $this->baseUrl = config('services.sslcommerz.sandbox')
            ? 'https://sandbox.sslcommerz.com'
            : 'https://securepay.sslcommerz.com';
    }

    /**
     * Initiate a payment session with SSLCommerz and return the gateway redirect URL.
     *
     * @return array{success: bool, gateway_url: ?string, transaction_id: string}
     */
    public function initiate(Order $order): array
    {
        $transactionId = 'TXN-'.Str::upper(Str::random(12));

        $shippingAddress = $order->shipping_address;

        $payload = [
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'total_amount' => $order->total,
            'currency' => 'BDT',
            'tran_id' => $transactionId,
            'success_url' => route('payment.success'),
            'fail_url' => route('payment.fail'),
            'cancel_url' => route('payment.cancel'),
            'ipn_url' => route('payment.ipn'),
            'cus_name' => $order->user->name,
            'cus_email' => $order->user->email,
            'cus_phone' => $order->user->phone ?? '01700000000',
            'cus_add1' => $shippingAddress['line1'] ?? '',
            'cus_city' => $shippingAddress['city'] ?? '',
            'cus_postcode' => $shippingAddress['postal_code'] ?? '',
            'cus_country' => $shippingAddress['country'] ?? 'Bangladesh',
            'shipping_method' => 'Courier',
            'ship_name' => $order->user->name,
            'ship_add1' => $shippingAddress['line1'] ?? '',
            'ship_city' => $shippingAddress['city'] ?? '',
            'ship_postcode' => $shippingAddress['postal_code'] ?? '',
            'ship_country' => $shippingAddress['country'] ?? 'Bangladesh',
            'product_name' => 'Order #'.$order->order_number,
            'product_category' => 'E-Commerce',
            'product_profile' => 'general',
            'value_a' => $order->id,
            'value_b' => $order->order_number,
        ];

        $response = Http::asForm()->post("{$this->baseUrl}/gwprocess/v4/api.php", $payload);

        $data = $response->json();

        if (isset($data['GatewayPageURL']) && $data['status'] === 'SUCCESS') {
            return [
                'success' => true,
                'gateway_url' => $data['GatewayPageURL'],
                'transaction_id' => $transactionId,
            ];
        }

        return [
            'success' => false,
            'gateway_url' => null,
            'transaction_id' => $transactionId,
        ];
    }

    /**
     * Validate a transaction with SSLCommerz's Order Validation API.
     *
     * @return array{valid: bool, data: array<string, mixed>}
     */
    public function validateTransaction(string $valId): array
    {
        $response = Http::get("{$this->baseUrl}/validator/api/validationserverAPI.php", [
            'val_id' => $valId,
            'store_id' => $this->storeId,
            'store_passwd' => $this->storePassword,
            'format' => 'json',
        ]);

        $data = $response->json() ?? [];

        $isValid = ($data['status'] ?? '') === 'VALID' || ($data['status'] ?? '') === 'VALIDATED';

        return [
            'valid' => $isValid,
            'data' => $data,
        ];
    }
}
