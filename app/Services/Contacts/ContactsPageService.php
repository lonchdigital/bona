<?php

namespace App\Services\Contacts;

use App\Models\ContactConfig;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Contacts\DTO\ContactsPageEditDTO;

class ContactsPageService extends BaseService
{

    public function editContactsPage(ContactsPageEditDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($request) {


            $existingConfig = ContactConfig::first();

            $dataToUpdate = [
                'city_one' => $request->cityOne,
                'address_one' => $request->addressOne,
                'phone_one' => $request->phoneOne,
                'email_one' => $request->emailOne,
                'iframe_address_one' => $request->iframeAddressOne,

                'city_two' => $request->cityTwo,
                'address_two' => $request->addressTwo,
                'phone_two' => $request->phoneTwo,
                'email_two' => $request->emailTwo,
                'iframe_address_two' => $request->iframeAddressTwo,

                'city_three' => $request->cityThree,
                'address_three' => $request->addressThree,
                'phone_three' => $request->phoneThree,
                'email_three' => $request->emailThree,
                'iframe_address_three' => $request->iframeAddressThree,
            ];




            if( !is_null($existingConfig)){
                $existingConfig->update($dataToUpdate);
            } else {
                ContactConfig::create($dataToUpdate);
            }

             return ServiceActionResult::make(true, trans('admin.contacts_edit_success'));
        });
    }


    public function getContactsConfig(): ?ContactConfig
    {
        return ContactConfig::first();
    }
}
