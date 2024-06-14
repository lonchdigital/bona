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
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
                'meta_tags' => $request->metaTags,

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

    public function getContactsFooter()
    {
        return ContactConfig::select('city_one', 'address_one', 'phone_one', 'email_one', 'city_two', 'address_two', 'phone_two', 'email_two', 'city_three', 'address_three', 'phone_three', 'email_three')->first();
    }
}
