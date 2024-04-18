<?php

namespace App\Http\Actions\Store\Contacts\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\ContactConfig;
use App\Services\Contacts\ContactsPageService;
use Abordage\LastModified\Facades\LastModified;


class ShowContactsPageAction extends BaseAction
{
    public function __invoke(ContactsPageService $contactsService)
    {
        LastModified::set(ContactConfig::first()->updated_at);

        return view('pages.store.contacts-page', [
            'contactsConfig' => $contactsService->getContactsConfig(),
        ]);
    }
}
