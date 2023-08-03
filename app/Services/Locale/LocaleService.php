<?php

namespace App\Services\Locale;

use App\Models\User;
use App\Services\Base\BaseService;

class LocaleService extends BaseService
{
    public function setLocale(User $user, string $newLocale): void
    {
        $user->update([
            'language' => $newLocale
        ]);
    }
}
