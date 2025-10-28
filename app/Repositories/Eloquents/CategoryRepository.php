<?php

namespace App\Repositories\Eloquents;

use Exception;
use App\Models\Zone;
use App\Models\Category;
use App\Helpers\Helpers;
use App\Imports\CategoryImport;
use App\Exports\CategoriesExport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\GraphQL\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;

class CategoryRepository extends BaseRepository
{
    function model()
    {
       return Category::class;
    }

    public function show($id)
    {
        try {

            return $this->model->with('subcategories.parent')->findOrFail($id)?->toArray();

        } catch (Exception $e){

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {

            $category =  $this->model->create([
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'status' => $request->status,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'category_meta_image_id' => $request->category_meta_image_id,
                'category_image_id' => $request->category_image_id,
                'category_icon_id'   => $request->category_icon_id,
                'commission_rate' => $request->commission_rate,
                'parent_id' => $request->parent_id,
                'is_allow_all_zone' => $request->is_allow_all_zone,
                'slug' => $request->slug
            ]);

            if ($request->is_allow_all_zone) {
                $category->zones()->attach(Zone::whereNull('deleted_at')->get());
            }

            if (isset($request->zone_ids)) {
                $category->zones()->attach($request->zone_ids ?? []);
                $category->zones;
            }

            if (isset($request->exclude_zone_ids)){
                $category->exclude_zones()->attach($request->exclude_zone_ids ?? []);
                $category->exclude_zones;
            }

            $category->category_image;
            $category->category_icon;

            $locales =  Helpers::getAllActiveLocales();
            foreach ($locales as $locale) {
                $category->setTranslation('name', $locale, $request['name'])
                    ->setTranslation('description', $locale, $request['description'])
                    ->setTranslation('meta_title', $locale, $request['meta_title'])
                    ->setTranslation('meta_description', $locale, $request['meta_description'])
                    ->save();
            }

            DB::commit();
            return $category;

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $category = $this->model->findOrFail($id);
            $category->update($request);

            if (!$request['is_allow_all_zone']) {
                $category->zones()->sync($request['zone_ids'] ?? []);
                if (isset($request['exclude_zone_ids'])){
                    $category->exclude_zones()->sync([]);
                    $category->exclude_zones()->sync($request['exclude_zone_ids']);
                }
            }

            if ($request['is_allow_all_zone']) {
                $category->exclude_zones()->sync([]);
                $category->zones()->sync([]);
                $zoneIds = Zone::whereNull('deleted_at')?->get()?->pluck('id')?->toArray();
                $category->zones()->sync($zoneIds);
            }

            if (isset($request['category_image_id'])) {
                $category->category_image()->associate($request['category_image_id']);
            }

            if (isset($request['category_icon_id'])) {
                $category->category_icon()->associate($request['category_icon_id']);
            }

            if (isset($request['zones'])) {
                $category->zones()->sync($request['zones']);
            }

            $category->category_image;
            $category->category_icon;

            $this->setTranslation($category, $request);

            DB::commit();

            return $category;

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function status($id, $status)
    {
        try {

            $category = $this->model->findOrFail($id);
            $category->update(['status' => $status]);

            return $category;

        } catch (Exception $e) {

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

    public function import()
    {
        DB::beginTransaction();
        try {

            $categoryImport = new CategoryImport();
            Excel::import($categoryImport, request()->file('categories'));
            DB::commit();

            return $categoryImport->getImportedCategories();

        } catch (Exception $e){

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getCategoriesExportUrl()
    {
        try {

            return route('admin.categories.export');

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function export()
    {
        try {

            return Excel::download(new CategoriesExport, 'categories.csv');

        } catch (Exception $e){

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getCategoryBySlug($slug)
    {
        try {

            return $this->model->where('slug', $slug)->firstOrFail();

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    function setTranslation($category, $request)
    {
        $locale = app()->getLocale();
        return $category->setTranslation('name', $locale, $request['name'])
            ->setTranslation('description', $locale, $request['description'])
            ->setTranslation('meta_title', $locale, $request['meta_title'])
            ->setTranslation('meta_description', $locale, $request['meta_description'])
            ->save();
    }
}
