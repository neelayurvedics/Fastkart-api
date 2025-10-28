<?php

namespace App\Repositories\Eloquents;

use Exception;
use App\Models\Page;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\DB;
use App\GraphQL\Exceptions\ExceptionHandler;
use Maatwebsite\Excel\Concerns\ToArray;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

class PageRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'title' => 'like',
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
        return Page::class;
    }

    public function show($id)
    {
        try {

            $page = $this->model->with('created_by')->get()
                ->makeVisible(['content', 'meta_description'])->find($id);

            isset($page->created_by)?
                $page->created_by->makeHidden(['permission']): $page;

            return $page->toArray();

        } catch (Exception $e){

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function store($request)
    {
        DB::beginTransaction();
        try {

            $page = $this->model->create([
                'title' => $request->title,
                'content' => $request->content,
                'meta_title' => $request->meta_title,
                'meta_description' => $request->meta_description,
                'page_meta_image_id' => $request->page_meta_image_id,
                'status' => $request->status,
                'slug' => $request->slug
            ]);

            $locales =  Helpers::getAllActiveLocales();
            foreach ($locales as $locale) {
                $page->setTranslation('title', $locale, $request['title'])
                    ->setTranslation('content', $locale, $request['content'])
                    ->setTranslation('meta_title', $locale, $request['meta_title'])
                    ->setTranslation('meta_description', $locale, $request['meta_description'])
                    ->save();
            }

            $page->page_meta_image;
            isset($page->created_by)?
                $page->created_by->makeHidden(['permission']): $page;

            DB::commit();
            return $page;

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $page = $this->model->findOrFail($id);
            $page->update($request);

            isset($page->created_by)?
                $page->created_by->makeHidden(['permission']): $page;

            self::setTranslation($page, $request);

            DB::commit();
            $page = $page->fresh();

            return $page;

        } catch (Exception $e) {

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

            $page = $this->model->findOrFail($id);
            $page->update(['status' => $status]);

            return $page->toArray();

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

    public function getPageBySlug($slug)
    {
        try {

            $page = $this->model
                ->where('slug', $slug)->firstOrFail()
                ->makeVisible(['content', 'meta_description']);

            isset($page->created_by)?
                $page->created_by->makeHidden(['permission']): $page;

            return $page;

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    function setTranslation($page, $request)
    {
        $locale = app()->getLocale();
        return $page->setTranslation('title', $locale, $request['title'])
            ->setTranslation('content', $locale, $request['content'])
            ->setTranslation('meta_title', $locale, $request['meta_title'])
            ->setTranslation('meta_description', $locale, $request['meta_description'])
            ->save();
    }
}
