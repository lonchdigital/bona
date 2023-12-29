<?php

namespace App\Http\Actions\Locale;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Locale\LocaleService;

class ChangeLocaleAction extends BaseAction
{
    public function __invoke(string $newLocale, LocaleService $localeService)
    {
        $localeService->setLocale($newLocale, $this->getAuthUser());

        return redirect()->back();
    }
}
