<?php

namespace App\Http\Actions\Store\Cart;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Resources\Store\Cart\CartResource;
use App\Models\Cart;
use App\Services\Cart\CartService;
use App\Services\Cart\DTO\ChangeProductCountInCartDTO;
use App\Services\Product\DoorConfiguratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddConfiguratorSelectionAction extends BaseAction
{
    use NeedCart;

    public function __invoke(Request $request, CartService $cartService, DoorConfiguratorService $configurator)
    {
        $input = $request->validate([
            'product' => ['required', 'string', 'max:60'],
            'color' => ['required', 'string', 'max:60'],
            'handle' => ['nullable', 'string', 'max:60'],
            'request_id' => ['required', 'uuid'],
            'expected_total' => ['required', 'numeric', 'min:0'],
        ]);
        $cart = $this->getCart($cartService);
        $hash = hash('sha256', json_encode([$input['product'], $input['color'], $input['handle'] ?? null]));

        DB::transaction(function () use ($cart, $cartService, $configurator, $input, $hash) {
            Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();
            $previous = DB::table('configurator_cart_requests')->where('cart_id', $cart->id)->where('request_id', $input['request_id'])->first();
            if ($previous) {
                abort_unless(hash_equals($previous->selection_hash, $hash), 409, trans('configurator.request_conflict'));

                return;
            }
            $selection = $configurator->selection($input);
            abort_if(abs($selection['total'] - (float) $input['expected_total']) > 0.009, 409, trans('configurator.price_changed'));
            // Independent catalog lines, not an assertion of a technically compatible bundle.
            foreach ($selection['lines'] as $line) {
                $cartService->addProductToCart($cart, $line['product'], new ChangeProductCountInCartDTO(1, $line['attributes']));
            }
            DB::table('configurator_cart_requests')->insert([
                'cart_id' => $cart->id,
                'request_id' => $input['request_id'],
                'selection_hash' => $hash,
                'created_at' => now(),
            ]);
        }, 3);

        return CartResource::make($cartService->getProductsInCartWithSummary($cart->fresh(), null));
    }
}
