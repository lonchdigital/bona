<?php

namespace App\Http\Actions\Admin\Contacts\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Application\ApplicationConfigService;
use App\Services\Contacts\ContactsPageService;

class ShowContactsEditPageAction extends BaseAction
{

    public function __invoke(
        ApplicationConfigService $applicationService,
        ContactsPageService $contactsService,
    )
    {
        return view('pages.admin.contacts.edit', [
            'availableLanguages' => $applicationService->getAvailableLanguages(),
            'contactsConfig' => $contactsService->getContactsConfig(),
        ]);
    }

}
