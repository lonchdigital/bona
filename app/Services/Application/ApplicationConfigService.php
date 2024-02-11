<?php

namespace App\Services\Application;

use App\Models\ApplicationConfig;
use App\Services\Application\DTO\ApplicationConfigEditDTO;
use App\Services\Application\DTO\EditRobotsTxtDto;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ApplicationConfigService extends BaseService
{
    /**
     * always return default value
     */
    const AVAILABLE_LANGUAGES_CONFIG = 'AVAILABLE_LANGUAGES';
    const ROBOTS_TXT_CONFIG = 'ROBOTS_TXT';
    const APPLICATION_IMAGES_FOLDER = 'application-images';

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

    public function editApplicationConfig(ApplicationConfigEditDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($request) {

            $dataToUpdate = [];
            foreach ($request as $key => $value) {
                $dataToUpdate[$key] = $value;
            }

            $existinglogoLight = ApplicationConfig::where('config_name', 'logoLight')->first();
            $existinglogoDark = ApplicationConfig::where('config_name', 'logoDark')->first();
            $dataToUpdate['logoLight'] = (!is_null($existinglogoLight)) ? $existinglogoLight->config_data : null;
            $dataToUpdate['logoDark'] = (!is_null($existinglogoDark)) ? $existinglogoDark->config_data : null;


            // logo Light
            if( !is_null($request->logoLight) ) {
                $logoLightImagePath = self::APPLICATION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';
                $this->storeImage($logoLightImagePath, $request->logoLight, 'png');
                $dataToUpdate['logoLight'] = $logoLightImagePath;

                if(!is_null($existinglogoLight) && !is_null($existinglogoLight->config_data)) {
                    $this->deleteImage($existinglogoLight->config_data);
                }
            }
            if( $dataToUpdate['logoLightDeleted'] ) {
                $this->deleteImage($existinglogoLight->config_data);
                $dataToUpdate['logoLight'] = null;
            }

            // logo Dark
            if( !is_null($request->logoDark) ) {
                $logoDarkImagePath = self::APPLICATION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.jpg';
                $this->storeImage($logoDarkImagePath, $request->logoDark, 'png');
                $dataToUpdate['logoDark'] = $logoDarkImagePath;

                if(!is_null($existinglogoDark) && !is_null($existinglogoDark->config_data)) {
                    $this->deleteImage($existinglogoDark->config_data);
                }
            }
            if( $dataToUpdate['logoDarkDeleted'] ) {
                $this->deleteImage($existinglogoDark->config_data);
                $dataToUpdate['logoDark'] = null;
            }

            unset($dataToUpdate['logoLightDeleted']);
            unset($dataToUpdate['logoDarkDeleted']);

            foreach ($dataToUpdate as $key => $value) {
                ApplicationConfig::updateOrCreate(['config_name' => $key], ['config_data' => $value]);
            }

            return ServiceActionResult::make(true, trans('admin.settings_updated_success'));
        });
    }

    public function getAllApplicationConfigOptions(): array
    {
        $data = ApplicationConfig::all();

//        dd($data->getTranslations['footerText']);
        $dataArray = [];

        foreach ($data as $item) {
            $dataArray[$item->config_name] = $item->config_data;
        }

        return $dataArray;
    }


}
