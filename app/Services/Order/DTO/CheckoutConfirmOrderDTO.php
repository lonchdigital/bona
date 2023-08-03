<?php

namespace App\Services\Order\DTO;

use App\Services\Base\DTO\BaseDTO;

class CheckoutConfirmOrderDTO implements BaseDTO
{
    public function __construct(
        public readonly ?string $firstName,
        public readonly ?string $lastName,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly int $deliveryTypeId,
        public readonly int $paymentTypeId,
        public readonly ?int $regionId,
        public readonly ?string $district,
        public readonly ?string $city,
        public readonly ?string $street,
        public readonly ?string $buildingNumber,
        public readonly ?string $apartmentNumber,
        public readonly ?string $floorNumber,
        public readonly bool $hasElevator,
        public readonly bool $saveDeliveryAddress,
        public readonly ?string $deliveryDate,
        public readonly ?int $deliveryTimeId,
        public readonly int $recipientTypeId,
        public readonly ?string $customRecipientFirstName,
        public readonly ?string $customRecipientLastName,
        public readonly ?string $customRecipientPhone,
        public readonly ?string $customRecipientEmail,
        public readonly ?string $comment,
        public readonly ?string $npCity,
        public readonly ?string $npDepartment,
        public readonly ?string $meestCity,
        public readonly ?string $meestDepartment,
    ) { }
}
