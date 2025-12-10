<?php

namespace App\Providers;

use App\Services\CartService;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(CartService $cartService): void
    {
        // Header / layout e cart share
        View::composer(
            [
                'frontend.layout',
                'components.frontend.navbar',
            ],
            function ($view) use ($cartService) {
                $request = request();

                $cart  = $cartService->getCurrentCart($request);
                $items = $cart->items;

                $view->with([
                    'cart'  => $cart,
                    'items' => $items,
                ]);
            }
        );
    }
}
