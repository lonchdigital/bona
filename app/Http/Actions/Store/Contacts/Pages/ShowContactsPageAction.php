<?php

namespace App\Http\Actions\Store\Contacts\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Contacts\ContactsPageService;


class ShowContactsPageAction extends BaseAction
{
    public function __invoke(ContactsPageService $contactsService)
    {
        return view('pages.store.contacts-page', [
            'contactsConfig' => $contactsService->getContactsConfig(),
        ]);
    }
}
