<?php

namespace App\Http\Actions\Admin\Contacts;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Contacts\ContactsEditRequest;
use App\Services\Contacts\ContactsPageService;

class ContactsEditAction extends BaseAction
{
    public function __invoke(
        ContactsEditRequest $request,
        ContactsPageService $contactsService,
    )
    {
        $result = $contactsService->editContactsPage($request->toDTO());

        return $this->handleActionResult(route('admin.pages.list.page'), $request, $result);
    }
}

