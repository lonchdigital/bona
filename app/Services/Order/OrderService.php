<?php

namespace App\Services\Order;

use App\DataClasses\DeliveryTypesDataClass;
use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\OrderStatusesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\DataClasses\RecipientTypesDataClass;
use App\Mail\AdminNotificationEmail;
use App\Mail\EmailSubscriptionEmail;
use App\Mail\OrderStatusEmail;
use App\Mail\UserCredentialsEmail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Delivery\DeliveryService;
use App\Services\Order\DTO\CheckoutConfirmOrderDTO;
use App\Services\Order\DTO\OrderFilterDTO;
use App\Services\Order\DTO\UpdateOrderDTO;
use Illuminate\Support\Facades\Mail;
use App\Mail\SuccessOrder;

class OrderService extends BaseService
{
    public function __construct(
       private readonly DeliveryService $deliveryService,
    )
    {

    }

    public function getOrdersPaginated(OrderFilterDTO $request)
    {
        $query = Order::query();

        if ($request->statusId) {
            $query->where('status_id', $request->statusId);
        }

        return $query->orderByDesc('id')->paginate(config('domain.items_per_page'));
    }
    public function createOrderByCart(Cart $cart, CheckoutConfirmOrderDTO $request, ?User $user): Order
    {

        return $this->coverWithDBTransactionWithoutResponse(function () use($cart, $request, $user) {
            $newUserCreated = false;
            $newUserPassword = '';

            if(is_null($user)) {

                $isUserExists = User::where('email', $request->email)->exists();
                if (!$isUserExists) {
                    $newUserPassword = \Str::random(16);
                    $user = User::create([
                        'email' => $request->email,
                        'first_name' => $request->firstName,
                        'last_name' => $request->lastName,
                        'phone' => $request->phone,
                        'role_id' => Role::USER_ROLE_ID,
                        'language' => app()->getLocale(),
                        'password' => \Hash::make($newUserPassword),
                    ]);
                    $newUserCreated = true;
                } else {
                    $user = User::where('email', $request->email)->first();

                    $user->setAttribute('first_name', $request->firstName);
                    $user->setAttribute('last_name', $request->lastName);
                    $user->setAttribute('phone', $request->phone);
                    $user->save();
                }

            }

            // TODO:: old version
            /*if (!$user) {
                $newUserPassword = \Str::random(16);
                $user = User::create([
                    'email' => $request->email,
                    'first_name' => $request->firstName,
                    'last_name' => $request->lastName,
                    'phone' => $request->phone,
                    'role_id' => Role::USER_ROLE_ID,
                    'language' => app()->getLocale(),
                    'password' => \Hash::make($newUserPassword),
                ]);
                $newUserCreated = true;
            }*/


            if ($request->paymentTypeId === PaymentTypesDataClass::CARD_PAYMENT) {
                $paymentStatus = OrderPaymentStatusesDataClass::STATUS_IN_PROGRESS;
            } elseif ($request->paymentTypeId === PaymentTypesDataClass::CASH_PAYMENT) {
                $paymentStatus = OrderPaymentStatusesDataClass::STATUS_PAID_AS_RECEIVED;
            } elseif ($request->paymentTypeId === PaymentTypesDataClass::CARD_PAYMENT_PAYPART) {
                $paymentStatus = OrderPaymentStatusesDataClass::STATUS_PAYPART;
            } else {
                $paymentStatus = OrderPaymentStatusesDataClass::STATUS_UNPAID;
            }

            $npCity = null;
            $npDepartment = null;
            if ($request->deliveryTypeId === DeliveryTypesDataClass::NP_DELIVERY) {
                $npCity = $this->deliveryService->getNpCityByRef($request->npCity);
                $npCity = ['uk' => $npCity['Description'] . ' ' . $npCity['AreaDescription'] . ' ' . mb_strtolower(trans('base.region')), 'ru' => $npCity['DescriptionRu'] . ' ' . $npCity['AreaDescriptionRu'] . ' ' . mb_strtolower(trans('base.region'))];

                $npDepartment = $this->deliveryService->getNpDepartmentByRef($request->npCity, $request->npDepartment);
                if( isset($npDepartment['Description']) && isset($npDepartment['DescriptionRu']) ) {
                    $npDepartment = ['uk' => $npDepartment['Description'], 'ru' => $npDepartment['DescriptionRu']];
                } else {
                    $npDepartment = ['uk' => 'Уточнити у покупця', 'ru' => 'Уточнить у покупателя'];
                }
            }

            $satCity = null;
            $satDepartment = null;
            if ($request->deliveryTypeId === DeliveryTypesDataClass::SAT_DELIVERY) {
                $satCity = $this->deliveryService->getSatCityByRef($request->satCity)[0]['text'];
                $satDepartment = $this->deliveryService->getSATDepartmentByRef($request->satDepartment)[0]['text'];

                $satCity = ['uk' => $satCity, 'ru' => $satCity];
                $satDepartment = ['uk' => $satDepartment, 'ru' => $satDepartment];
            }



            // TODO:: remove as FINISH
            /*$meestCity = null;
            $meestDepartment = null;
            if ($request->deliveryTypeId === DeliveryTypesDataClass::MIST_EXPRESS_DELIVERY) {
                $meestCity = $this->deliveryService->getMeestCityByRef($request->meestCity);
                $meestCity = ['uk' => $meestCity['text_uk'], 'ru' => $meestCity['text_ru']];

                $meestDepartment = $this->deliveryService->getMeestDepartmentByRef($request->meestDepartment);
                $meestDepartment = ['uk' => $meestDepartment['text_uk'], 'ru' => $meestDepartment['text_ru']];
            }*/

            $order = Order::create([
                'status_id' => OrderStatusesDataClass::STATUS_NEW,
                'user_id' => $user->id,
                'delivery_type_id' => $request->deliveryTypeId,
                'payment_type_id' => $request->paymentTypeId,
                'promo_code_id' => $cart->promo_code_id,
                'region_id' => $request->regionId,
//                'sat_region_id' => $request->satRegionId,
                'district' => $request->district,
//                'sat_district' => $request->satDistrict,
                'city' => $request->city,
//                'sat_city' => $request->satCity,
                'street' => $request->street,
                'building_number' => $request->buildingNumber,
                'apartment_number' => $request->apartmentNumber,
                'floor_number' => $request->floorNumber,
//                'has_elevator' => $request->hasElevator,
                'delivery_date' => $request->deliveryDate,
                'delivery_time_id' => $request->deliveryTimeId,
                'recipient_type_id' => $request->recipientTypeId,
                'custom_recipient_first_name' => $request->customRecipientFirstName,
                'custom_recipient_last_name' => $request->customRecipientLastName,
                'custom_recipient_phone' => $request->customRecipientPhone,
                'custom_recipient_email' => $request->customRecipientEmail,
                'comment' => $request->comment,
                'np_city' => $npCity,
                'np_department' => $npDepartment,
                'sat_city' => $satCity,
                'sat_department' => $satDepartment,
//                'meest_city' => $meestCity,
//                'meest_department' => $meestDepartment,
                'payment_status_id' => $paymentStatus,
            ]);

            $productsToSync = [];

            // TODO: Remove when finish
            /*foreach ($cart->products as $product) {
                $productsToSync[$product->id] = [
                    'count' => $product->pivot->count,
                    'price' => $product->pivot->price,
                ];
            }*/

            foreach ($cart->products as $product) {
                $productsToSync[] = [
                    'product_id' => $product->id,
                    'count' => $product->pivot->count,
                    'price' => $product->pivot->price,
                    'attributes' => $product->pivot->attributes,
                    'attributes_price' => $product->pivot->attributes_price,
                ];
            }

//            dd($productsToSync);
            $order->products()->sync($productsToSync);

            $cart->products()->sync([]);
            $cart->delete();

            if ($newUserCreated) {
//                Mail::to($request->email)->send(new UserCredentialsEmail($request->email, $newUserPassword));
                Mail::to($request->email)->send(new SuccessOrder($order));
            } else {
                Mail::to($user->email)->send( new SuccessOrder($order) );
            }

            if (config('domain.admin_notification_emails')) {
                foreach (explode(',', config('domain.admin_notification_emails')) as $email) {
                    Mail::to($email)->send(new AdminNotificationEmail(trans('admin.new_order_email_subject'), route('admin.order.edit', ['order' => $order->id]), $order));
                }
            }

            return $order;
        });
    }

    /**
     * Takes an order for a single product from someone who only left a name
     * and a number.
     *
     * The orders table wants an account behind every order, and the admin
     * screens read the customer straight off it, so one is found or made the
     * same way checkout does for a guest — matched on the digits of the phone
     * rather than the whole string, since the field carries the input mask's
     * brackets and dashes. There is no address, delivery or payment to record:
     * the shop rings back and settles all of that.
     */
    public function createOneClickOrder(Product $product, string $name, string $phone, ?User $user = null): Order
    {
        return $this->coverWithDBTransactionWithoutResponse(function () use ($product, $name, $phone, $user) {
            $user = $user ?? $this->resolveOneClickCustomer($name, $phone);

            $order = Order::create([
                'status_id' => OrderStatusesDataClass::STATUS_ONE_CLICK,
                'user_id' => $user->id,
                'recipient_type_id' => RecipientTypesDataClass::RECIPIENT_USER,
                'custom_recipient_phone' => $phone,
                // Nothing has been paid yet — that is settled on the call back.
                'payment_status_id' => OrderPaymentStatusesDataClass::STATUS_UNPAID,
            ]);

            $order->products()->sync([[
                'product_id' => $product->id,
                'count' => 1,
                'price' => $product->price,
                'attributes' => null,
                'attributes_price' => null,
            ]]);

            if (config('domain.admin_notification_emails')) {
                foreach (explode(',', config('domain.admin_notification_emails')) as $email) {
                    Mail::to($email)->send(new AdminNotificationEmail(
                        trans('admin.new_order_email_subject'),
                        route('admin.order.details.page', ['order' => $order->id])
                    ));
                }
            }

            return $order;
        });
    }

    /**
     * The customer behind a one click order: their account if the phone is
     * already known, otherwise a new one. The address is only there because
     * the column demands something unique — nobody signs in with it, and a
     * repeat customer on the same number lands on the same record.
     */
    private function resolveOneClickCustomer(string $name, string $phone): User
    {
        $digits = preg_replace('/\D/', '', $phone);

        $existing = User::whereRaw("REGEXP_REPLACE(phone, '[^0-9]', '') = ?", [$digits])->first();

        if ($existing) {
            return $existing;
        }

        return User::create([
            'email' => 'one-click-' . $digits . '@bona-doors.com.ua',
            'first_name' => $name,
            'last_name' => '',
            'phone' => $phone,
            'role_id' => Role::USER_ROLE_ID,
            'language' => app()->getLocale(),
            'password' => \Hash::make(\Str::random(32)),
        ]);
    }

    public function updateOrderPaymentStatusId(Order $order, int $newStatusId): ServiceActionResult
    {
        return $this->coverWithDBTransactionWithoutResponse(function () use($order, $newStatusId) {
            $order->update([
               'payment_status_id' => $newStatusId,
            ]);

            if (config('domain.admin_notification_emails')) {
                foreach (explode(',', config('domain.admin_notification_emails')) as $email) {
                    Mail::to($email)->send(new OrderStatusEmail(
                        trans('admin.order_status_email'),
                        route('admin.order.edit', ['order' => $order->id]),
                        $order,
                        OrderPaymentStatusesDataClass::get($newStatusId)['name']
                    ));
                }
            }

            return ServiceActionResult::make(true, 'Success');
        });
    }

    public function updateOrderPaymentStatusIdWithoutEmail(Order $order, int $newStatusId): ServiceActionResult
    {
        return $this->coverWithDBTransactionWithoutResponse(function () use($order, $newStatusId) {
            $order->update([
                'payment_status_id' => $newStatusId,
            ]);

            return ServiceActionResult::make(false, 'Fail');
        });
    }

    public function getOrderSummary(Order $order): array
    {
        $totalPrice = 0;
        foreach ($order->products as $product) {
            $totalPrice += $product->pivot->price * $product->pivot->count;
        }

        $deliveryPrice = config('domain.delivery_price');
        $deliveryPriceOld = $deliveryPrice;

        if ($totalPrice >= config('domain.free_delivery_from_price')) {
            $deliveryPrice = 0;
        }

//        $total = round($totalPrice + $deliveryPrice, 2);
        $total = round($totalPrice, 2);
        $discount = 0;

        if ($order->promoCode) {
            $discount = $total / 100 * $order->promoCode->discount;
            $total = $total - $discount;
        }

        return [
            'products' =>  round($totalPrice, 2),
            'delivery' => round($deliveryPrice, 2),
            'delivery_old' => round($deliveryPriceOld, 2),
            'total' => round($total, 2),
            'discount' => round($discount, 2),
        ];
    }

    public function updateOrder(Order $order, UpdateOrderDTO $request): ServiceActionResult
    {
        return $this->coverWithTryCatch(function () use($order, $request) {
            $order->update([
                'status_id' => $request->statusId,
                'payment_status_id' => $request->orderPaymentStatusId,
            ]);


            return ServiceActionResult::make(true, trans('admin.order_update_success'));
        });
    }

    public function deleteOrder(Order $order): ServiceActionResult
    {
        $order->products()->sync([]);
        $order->delete();

        return ServiceActionResult::make(true, trans('admin.order_delete_success'));
    }
}
