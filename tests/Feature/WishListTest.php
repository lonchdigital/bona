<?php

namespace Tests\Feature;

use App\Models\WishList;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\MakesShopData;
use Tests\TestCase;

/**
 * The wish list, kept by a visitor who has not signed in.
 *
 * The merge on sign in is the part that was never verified end to end: it
 * needed an account, and checking it by hand on the live site was not
 * something worth doing.
 */
class WishListTest extends TestCase
{
    use RefreshDatabase;
    use MakesShopData;

    private function save(string $slug)
    {
        return $this->keepCookies(
            $this->postJson(route('store.wishlist.private.add-product', ['productSlug' => $slug]))
        );
    }

    private function signIn(string $email)
    {
        return $this->keepCookies($this->post(route('auth.sign-in'), [
            'email' => $email,
            'password' => 'password',
        ]));
    }

    public function test_a_guest_can_save_a_product(): void
    {
        $product = $this->makeProduct();

        $this->save($product->slug)->assertOk();

        $list = WishList::first();

        $this->assertNotNull($list, 'Список не створився.');
        $this->assertNull($list->owner_id, 'У гостя списку не має бути власника.');
        $this->assertCount(1, $list->products);
    }

    public function test_the_list_survives_the_next_request(): void
    {
        $product = $this->makeProduct();

        $this->save($product->slug)->assertOk();
        $this->save($this->makeProduct()->slug)->assertOk();

        $this->assertSame(1, WishList::count(), 'Той самий відвідувач має мати один список.');
        $this->assertCount(2, WishList::first()->products);
    }

    public function test_two_visitors_do_not_see_each_others_lists(): void
    {
        $mine = $this->makeProduct();
        $theirs = $this->makeProduct();

        $this->save($mine->slug)->assertOk();
        $this->asNewVisitor()->save($theirs->slug)->assertOk();

        $this->assertSame(2, WishList::count());

        $lists = WishList::with('products')->get();

        $this->assertNotSame(
            $lists[0]->products->pluck('id')->all(),
            $lists[1]->products->pluck('id')->all(),
            'Списки різних відвідувачів не мають збігатись.'
        );
    }

    public function test_a_list_saved_before_signing_in_is_handed_to_the_account(): void
    {
        $product = $this->makeProduct();
        $user = $this->author();

        $this->save($product->slug)->assertOk();
        $this->signIn($user->email);

        $this->assertSame(
            1,
            WishList::where('owner_id', $user->id)->count(),
            'Після входу список гостя має належати акаунту.'
        );
        $this->assertSame(
            0,
            WishList::whereNull('owner_id')->count(),
            'Гостьового списку більше не має лишитись.'
        );
        $this->assertCount(1, WishList::first()->products);
    }

    public function test_what_the_account_already_had_is_kept_when_a_guest_list_joins_it(): void
    {
        $alreadySaved = $this->makeProduct();
        $savedAsGuest = $this->makeProduct();
        $user = $this->author();

        // What the account already had, put there directly so the test is
        // about the merge rather than about signing in and out.
        $accountList = WishList::create([
            'owner_id' => $user->id,
            'access_token' => 'account-list-token',
        ]);
        $accountList->products()->attach($alreadySaved->id);

        // A guest saves something else, then signs in.
        $this->save($savedAsGuest->slug)->assertOk();
        $this->signIn($user->email);

        $list = WishList::where('owner_id', $user->id)->first();

        $this->assertNotNull($list);
        $this->assertCount(2, $list->products, 'Мали лишитись обидва товари.');
        $this->assertSame(1, WishList::count(), 'Гостьовий список мав зникнути після злиття.');
    }

    public function test_a_saved_product_comes_back_marked_as_saved(): void
    {
        $product = $this->makeProduct();

        $this->save($product->slug)->assertOk();

        $ids = app(\App\Services\WishList\WishListService::class)
            ->getWishListProductsId(WishList::first());

        $this->assertSame(
            [$product->id],
            $ids->all(),
            'Список збережених ідентифікаторів має містити сам товар, а не порожнечі.'
        );
    }
}
