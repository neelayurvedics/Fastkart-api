<?php

namespace App\Repositories\Eloquents;

use Exception;
use App\Models\Tag;
use App\Helpers\Helpers;
use App\Imports\TagImport;
use App\Exports\TagsExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\GraphQL\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

class TagRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name' => 'like',
    ];

    public function boot()
    {
        try {

            $this->pushCriteria(app(RequestCriteria::class));

        } catch (ExceptionHandler $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    function model()
    {
        return Tag::class;
    }

    public function show($id)
    {
        try {

            return $this->model->findOrFail($id)?->toArray();

        } catch (Exception $e){

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {

            $tag =  $this->model->create([
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'status' => $request->status,
                'slug' => $request->slug
            ]);

            $locales =  Helpers::getAllActiveLocales();
            foreach ($locales as $locale) {
                $tag->setTranslation('name', $locale, $request['name'])
                    ->setTranslation('description', $locale, $request['description'])
                    ->save();
            }

            DB::commit();
            return $tag;

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $tag = $this->model->findOrFail($id);
            $tag->update($request);
            $this->setTranslation($tag, $request);
            DB::commit();
            return $tag;

        } catch (Exception $e){

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function destroy($id)
    {
        try {

            return $this->model->findOrFail($id)->destroy($id);

        } catch (Exception $e){

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function status($id, $status)
    {
        try {

            $tag = $this->model->findOrFail($id);
            $tag->update(['status' => $status]);

            return $tag->toArray();

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function deleteAll($ids)
    {
        try {

            return $this->model->whereIn('id', $ids)->delete();

        } catch (Exception $e){

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function import()
    {
        DB::beginTransaction();
        try {

            $tagImport = new TagImport();
            Excel::import($tagImport, request()->file('tags'));
            DB::commit();

            return $tagImport->getImportedTags();

        } catch (Exception $e){

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getTagsExportUrl()
    {
        try {

            return route('admin.tags.export');

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function export()
    {
        try {

            return Excel::download(new TagsExport, 'tags.csv');

        } catch (Exception $e){

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    function setTranslation($tag, $request)
    {
        $locale = app()->getLocale();
        return $tag->setTranslation('name', $locale, $request['name'])
            ->setTranslation('description', $locale, $request['description'])
            ->save();
    }
}






