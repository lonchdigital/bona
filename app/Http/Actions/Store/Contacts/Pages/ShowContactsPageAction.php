<?php

namespace App\Http\Actions\Store\Contacts\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Contacts\ContactsPageService;
use Abordage\LastModified\Facades\LastModified;


class ShowContactsPageAction extends BaseAction
{
    public function __invoke(ContactsPageService $contactsService)
    {
        $config = $contactsService->getContactsConfig();
        $config->meta_tags = $this->handleFollowTag($config->meta_tags);

        LastModified::set($config->updated_at);

        return view('pages.store.contacts-page', [
            'contactsConfig' => $config,
        ]);
    }
}
