<?php

namespace App\Repositories\Eloquents;

use Exception;
use App\Models\Publication;
use Illuminate\Support\Facades\DB;
use App\GraphQL\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

class PublicationRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'publisher_name' => 'like',
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
        return Publication::class;
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

            $publication = $this->model->create([
                'publisher_name' => $request->publisher_name,
                'description' => $request->description,
                'publisher_logo_id' => $request->publisher_logo_id,
                'publisher_cover_image_id' => $request->publisher_cover_image_id,
                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
                'city' => $request->city,
                'facebook' => $request->facebook,
                'twitter' => $request->twitter,
                'instagram' => $request->instagram,
                'youtube' => $request->youtube,
                'pinterest' => $request->pinterest,
                'status' => $request->status,
            ]);

            $publication->publisher_logo;
            $publication->publisher_cover_image;

            DB::commit();
            return $publication;

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $publication = $this->model->findOrFail($id);
            $publication->update($request);

            if (isset($request['publisher_logo_id'])) {
                $publication->publisher_logo()->associate($request['publisher_logo_id']);
            }

            if (isset($request['publisher_cover_image_id'])) {
                $publication->publisher_cover_image()->associate($request['publisher_cover_image_id']);
            }

            $publication->publisher_logo;
            $publication->publisher_cover_image;

            DB::commit();
            return $publication;

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

            $publication = $this->model->findOrFail($id);
            $publication->update(['status' => $status]);

            return $publication;

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

    public function getPublicationBySlug($slug)
    {
        try {

            return $this->model->where('slug', $slug)->firstOrFail();

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}






