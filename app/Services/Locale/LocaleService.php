<?php

namespace App\Services\Locale;

use App\Models\User;
use App\Services\Base\BaseService;

class LocaleService extends BaseService
{
    public function setLocale(?User $user, string $newLocale): void
    {
        if (!$user) {
            return;
        }

        $user->update([
            'language' => $newLocale
        ]);
    }
}
