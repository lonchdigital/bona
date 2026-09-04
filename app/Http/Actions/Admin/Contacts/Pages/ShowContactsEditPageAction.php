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
    ) {
        $availableLanguages = $applicationService->getAvailableLanguages();

        return view('pages.admin.contacts.edit', [
            'availableLanguages' => $availableLanguages,
            'contactsConfig' => $contactsService->getContactsConfig(),
            'defaultWorkingHours' => collect($availableLanguages)
                ->mapWithKeys(fn (string $locale) => [$locale => trans('base.working_hours', [], $locale)])
                ->all(),
        ]);
    }
}
