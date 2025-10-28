<?php

namespace App\Repositories\Eloquents;

use Exception;
use App\Models\Faq;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\DB;
use App\GraphQL\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

class FaqRepository extends BaseRepository
{

    protected $fieldSearchable = [
        'title' => 'like',
        'description' => 'like',
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
       return Faq::class;
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

            $faq = $this->model->create([
                'title' => $request->title,
                'description' => $request->description,
                'status' => $request->status
            ]);

            $locales =  Helpers::getAllActiveLocales();
            foreach ($locales as $locale) {
                $faq->setTranslation('title', $locale, $request['title'])
                    ->setTranslation('description', $locale, $request['description'])
                    ->save();
            }

            DB::commit();
            return $faq;

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $faq = $this->model->findOrFail($id);
            $faq->update($request);

            $this->setTranslation($faq, $request);

            DB::commit();
            return $faq;

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

            $faq = $this->model->findOrFail($id);
            $faq->update(['status' => $status]);

            return $faq->toArray();

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

    function setTranslation($faq, $request)
    {
        $locale = app()->getLocale();
        return $faq->setTranslation('title', $locale, $request['title'])
            ->setTranslation('description', $locale, $request['description'])
            ->save();
    }
}
