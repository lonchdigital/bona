<?php

namespace App\Services\Order;

use App\DataClasses\DeliveryTypesDataClass;
use App\DataClasses\OrderPaymentStatusesDataClass;
use App\DataClasses\OrderStatusesDataClass;
use App\DataClasses\PaymentTypesDataClass;
use App\Mail\AdminNotificationEmail;
use App\Mail\EmailSubscriptionEmail;
use App\Mail\UserCredentialsEmail;
use App\Models\Cart;
use App\Models\Order;
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
                $paymentStatus = OrderPaymentStatusesDataClass::STATUS_UNPAID;
            } else {
                $paymentStatus = OrderPaymentStatusesDataClass::STATUS_PAID;
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
                    Mail::to($email)->send(new AdminNotificationEmail(trans('admin.new_order_email_subject'), route('admin.order.edit', ['order' => $order->id])));
                }
            }

            return $order;
        });
    }

    public function updateOrderPaymentStatusId(Order $order, int $newStatusId): ServiceActionResult
    {
        return $this->coverWithDBTransactionWithoutResponse(function () use($order, $newStatusId) {
            $order->update([
               'payment_status_id' => $newStatusId,
            ]);

            return ServiceActionResult::make(true, 'Success');
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

        $total = round($totalPrice + $deliveryPrice, 2);
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
            ]);


            return ServiceActionResult::make(true, trans('admin.order_update_success'));
        });
    }
}
