<?php

namespace App\Services\Application;

use App\Models\ApplicationConfig;
use App\Services\Application\DTO\ApplicationConfigEditDTO;
use App\Services\Application\DTO\EditRobotsTxtDto;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
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
        // TODO:: we do not have AVAILABLE_LANGUAGES in DB
        /*return Cache::get('application_available_languages', function () {
            try {
                $config = ApplicationConfig::where('config_name', self::AVAILABLE_LANGUAGES_CONFIG)->first();

                if ($config) {
                    return json_decode($config->data, true);
                }
            } catch (\Exception $exception) {

            }

            return ['uk', 'ru'];
        });*/
        return ['uk', 'ru'];
    }

    public function setLanguage($lang, Request $request)
    {
        if (in_array($lang, ['uk', 'ru'])) {
            session()->put('locale', $lang);
            app()->setLocale($lang);
        }

        session()->put('language_popup_shown', true);

        return redirect()->back();
    }

    public function getRobotsTxtContent(): string
    {
        $config = ApplicationConfig::where('config_name', self::ROBOTS_TXT_CONFIG)->first();

        if ($config) {
            return  $config['config_data'];
        }

        return '';
    }

    public function setRobotsTxtContent(EditRobotsTxtDto $request): ServiceActionResult
    {
        return $this->coverWithTryCatch(function () use ($request){
            // put data to robots.txt
            $filePath = public_path('robots.txt');
            file_put_contents($filePath, $request->content);

            // update data in DB
            ApplicationConfig::updateOrCreate([
                'config_name' => self::ROBOTS_TXT_CONFIG
            ], [
                'config_data' => $request->content,
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
            $existingFormImage = ApplicationConfig::where('config_name', 'formImage')->first();
            $existingAuthorAvatar = ApplicationConfig::where('config_name', 'authorAvatar')->first();
            $dataToUpdate['logoLight'] = (!is_null($existinglogoLight)) ? $existinglogoLight->config_data : null;
            $dataToUpdate['logoDark'] = (!is_null($existinglogoDark)) ? $existinglogoDark->config_data : null;
            $dataToUpdate['formImage'] = (!is_null($existingFormImage)) ? $existingFormImage->config_data : null;
            $dataToUpdate['authorAvatar'] = (!is_null($existingAuthorAvatar)) ? $existingAuthorAvatar->config_data : null;

            // logo Light
            if( !is_null($request->logoLight) ) {
                $logoLightImagePath = self::APPLICATION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10);

                $this->storeImage($logoLightImagePath, $request->logoLight, 'webp', 100);
                $this->storeImage($logoLightImagePath, $request->logoLight, 'png', 100);

                $dataToUpdate['logoLight'] = $logoLightImagePath . '.webp';

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
                $logoDarkImagePath = self::APPLICATION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10);

                $this->storeImage($logoDarkImagePath, $request->logoDark, 'webp', 100);
                $this->storeImage($logoDarkImagePath, $request->logoDark, 'png', 100);

                $dataToUpdate['logoDark'] = $logoDarkImagePath . '.webp';

                if(!is_null($existinglogoDark) && !is_null($existinglogoDark->config_data)) {
                    $this->deleteImage($existinglogoDark->config_data);
                }
            }
            if( $dataToUpdate['logoDarkDeleted'] ) {
                $this->deleteImage($existinglogoDark->config_data);
                $dataToUpdate['logoDark'] = null;
            }

            // form Image
            if( !is_null($request->formImage) ) {
                $formImagePath = self::APPLICATION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10);

                $this->storeImage($formImagePath, $request->formImage, 'webp');
                $this->storeImage($formImagePath, $request->formImage, 'jpg');

                $dataToUpdate['formImage'] = $formImagePath . '.webp';

                if(!is_null($existingFormImage) && !is_null($existingFormImage->config_data)) {
                    $this->deleteImage($existingFormImage->config_data);
                }
            }
            if( $dataToUpdate['formImageDeleted'] ) {
                $this->deleteImage($existingFormImage->config_data);
                $dataToUpdate['formImage'] = null;
            }


            // author avatar
            if( !is_null($request->authorAvatar) ) {
                $authorAvatarPath = self::APPLICATION_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10);

                $this->storeAuthorAvatar($authorAvatarPath, $request->authorAvatar, 'webp');
                $this->storeAuthorAvatar($authorAvatarPath, $request->authorAvatar, 'jpg');

                $dataToUpdate['authorAvatar'] = $authorAvatarPath . '.webp';

                if(!is_null($existingAuthorAvatar) && !is_null($existingAuthorAvatar->config_data)) {
                    $this->deleteImage($existingAuthorAvatar->config_data);
                }
            }
            if( $dataToUpdate['authorAvatarDeleted'] ) {
                $this->deleteImage($existingAuthorAvatar->config_data);
                $dataToUpdate['authorAvatar'] = null;
            }

            unset($dataToUpdate['logoLightDeleted']);
            unset($dataToUpdate['logoDarkDeleted']);
            unset($dataToUpdate['formImageDeleted']);
            unset($dataToUpdate['authorAvatarDeleted']);

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
