<?php

namespace App\Listeners;

use App\Services\CartService;
use Illuminate\Auth\Events\Login;

class MergeGuestCartOnLogin
{
    /**
     * Create the event listener.
     */
    public function __construct(private CartService $cartService) {}

    /**
     * Handle the event: merge the guest's session-bound Redis cart into
     * their authenticated DB cart, summing quantities for shared products.
     */
    public function handle(Login $event): void
    {
        $sessionId = session()->getId();

        $this->cartService->mergeGuestCartIntoUser($event->user, $sessionId);
    }
}
