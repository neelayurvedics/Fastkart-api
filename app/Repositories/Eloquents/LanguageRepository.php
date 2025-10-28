<?php

namespace App\Repositories\Eloquents;

use Exception;
use App\Models\language;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Artisan;
use App\GraphQL\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Prettus\Repository\Criteria\RequestCriteria;


class LanguageRepository extends BaseRepository
{

    protected $appLocale;

    public function boot()
    {
        try {

            $this->pushCriteria(app(RequestCriteria::class));

        } catch (ExceptionHandler $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function model()
    {
        $this->appLocale = app()->getLocale();
        return language::class;
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {

            $language = $this->model->create([
                'name' => $request->name,
                'locale' => $request->locale,
                'status' => $request->status,
                'is_rtl' => $request->is_rtl
            ]);

            $locales =  Helpers::getAllActiveLocales();
            foreach ($locales as $locale) {
                $language->setTranslation('name', $locale, $request->name)->save();
            }

            DB::commit();

            return $language;

        } catch (Exception $e) {

            DB::rollback();

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function show($id)
    {
        try {

            return $this->model->findOrFail($id)?->toArray();

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $language = $this->model->findOrFail($id);
            $language->update($request);

            $language->setTranslation('name', app()->getLocale(), $request['name'])->save();
            DB::commit();

            return $language;

        } catch (ExceptionHandler $e) {

            DB::rollback();

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {

            $language = $this->model->findOrFail($id);
            if ($language->system_reserve) {
                throw new Exception(__('errors.system_reserved_language_deletion'), 403);
            }
            return $language->destroy($id);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function status($id, $status)
    {
        try {

            $language = $this->model->findOrFail($id);
            $language->update(['status' => $status]);
            return $language;

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function rtl($id, $rtl)
    {
        try {

            $language = $this->model->findOrFail($id);
            $language->update(['is_rtl' => $rtl]);
            return $language;

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getFilesInFolder($request)
    {
        $locale = $request->header("Accept-Lang") ?? $this->appLocale;
        $folderPath = resource_path("lang/{$locale}");
        $filesInFolder = File::allFiles($folderPath);

        $fileList = [];
        foreach ($filesInFolder as $file) {
            $fileInfo = pathinfo($file);
            $fileList[] = [
                'name' => ucfirst($fileInfo['filename']),
                'slug' => $fileInfo['filename']
            ];
        }

        return $fileList;
    }

    public function createPaginate($translations, $request)
    {
        $perPage = $request->paginate ?? config('app.paginate');
        $currentPage = $request->input('page', 1);
        $items = array_slice($translations, ($currentPage - 1) * $perPage, $perPage);
        $translations = new LengthAwarePaginator($items, count($translations), $perPage, $currentPage, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return $translations;
    }

    public function getFileContent($request)
    {
        try {
            $locale = $request->header("Accept-Lang") ?? $this->appLocale;
            $settings = Helpers::getSettings();
            $file = $request->filename;
            $searchTerm = $request->search;
            $dir = resource_path("lang/{$locale}");
            if (File::isDirectory($dir)) {
                foreach (File::allFiles($dir) as $dirFile) {
                    $filename = pathinfo($dirFile, PATHINFO_FILENAME);
                    $allFiles[] = $filename;
                }

                if (! $file) {
                    $file = head($allFiles);
                }

                $languageFilePath = "{$dir}/{$file}.php";
                if (file_exists($languageFilePath)) {
                    $translations = include $languageFilePath;
                    if ($searchTerm) {
                        $translations = array_filter($translations, function ($value, $key) use ($searchTerm) {
                            if (is_string($value)) {
                                return stripos($key, $searchTerm) !== false || stripos($value, $searchTerm) !== false;
                            }
                            return false; // Skip non-string values
                        }, ARRAY_FILTER_USE_BOTH);
                    }

                    if ($request->pagination) {
                        $translations = $this->createPaginate($translations, $request);
                    }
                    return $translations;
                }
            }

            throw new Exception(__('errors.file_not_found'), 422);
        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }

    }

    public function saveFileContent($request)
    {

        try {
            $locale = $request->header("Accept-Lang");
            $file = $request->filename;
            $dir = resource_path("lang/{$locale}");

            if (File::isDirectory($dir)) {
                $languageFilePath = "{$dir}/{$file}.php";
                if (file_exists($languageFilePath)) {
                    $fileContent = include $languageFilePath;
                    $modifiedContent = array_merge($fileContent,$request->all());
                    $fp = fopen($languageFilePath, 'w');
                    fwrite($fp, var_export($modifiedContent, true));
                    File::prepend($languageFilePath, '<?php return  ');
                    File::append($languageFilePath, ';');
                    Artisan::call('cache:clear');

                    $translations = $this->createPaginate($modifiedContent, $request);

                    return $translations;
                }
            }

            throw new Exception(__('errors.file_not_found'), 422);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
