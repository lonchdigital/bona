<?php

namespace App\Services\Application;

use App\Models\ApplicationConfig;
use App\Services\Application\DTO\EditRobotsTxtDto;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use Illuminate\Support\Facades\Cache;

class ApplicationConfigService extends BaseService
{
    /**
     * always return default value
     */
    const AVAILABLE_LANGUAGES_CONFIG = 'AVAILABLE_LANGUAGES';
    const ROBOTS_TXT_CONFIG = 'ROBOTS_TXT';

    public function getAvailableLanguages(): array
    {
        return Cache::get('application_available_languages', function () {
            try {
                $config = ApplicationConfig::where('config_name', self::AVAILABLE_LANGUAGES_CONFIG)->first();

                if ($config) {
                    return json_decode($config->data, true);
                }
            } catch (\Exception $exception) {

            }

            return ['uk', 'ru'];
        });
    }

    public function getRobotsTxtContent(): string
    {
        $config = ApplicationConfig::where('config_name', self::ROBOTS_TXT_CONFIG)->first();

        if ($config) {
            return  $config['data'];
        }

        return '';
    }

    public function setRobotsTxtContent(EditRobotsTxtDto $request): ServiceActionResult
    {
        return $this->coverWithTryCatch(function () use ($request){
             ApplicationConfig::updateOrCreate([
                 'config_name' => self::ROBOTS_TXT_CONFIG
             ], [
                 'data' => $request->content,
             ]);

             return ServiceActionResult::make(true, trans('admin.robots_txt_edit_success'));
        });
    }
}
