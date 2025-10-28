<?php

namespace App\Repositories\Eloquents;

use Exception;
use App\Models\Author;
use App\Helpers\Helpers;
use Illuminate\Support\Facades\DB;
use App\GraphQL\Exceptions\ExceptionHandler;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;

class AuthorRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'author_name' => 'like',
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
        return Author::class;
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

            $author = $this->model->create([
                'author_name' => $request->author_name,
                'bio' => $request->bio,
                'author_image_id' => $request->author_image_id,
                'author_cover_image_id' => $request->author_cover_image_id,
                'country_id' => $request->country_id,
                'state_id' => $request->state_id,
                'city' => $request->city,
                'birth_date' => $request->birth_date,
                'death_date' => $request->death_date,
                'languages' => $request->languages,
                'facebook' => $request->facebook,
                'twitter' => $request->twitter,
                'instagram' => $request->instagram,
                'youtube' => $request->youtube,
                'pinterest' => $request->pinterest,
                'status' => $request->status,
            ]);

            $author->author_image;
            $author->author_cover_image;

            DB::commit();
            return $author;

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $author = $this->model->findOrFail($id);
            $author->update($request);

            if (isset($request['author_image_id'])) {
                $author->author_image()->associate($request['author_image_id']);
            }

            if (isset($request['author_cover_image_id'])) {
                $author->author_cover_image()->associate($request['author_cover_image_id']);
            }

            $author->author_image;
            $author->author_cover_image;

            DB::commit();
            return $author;

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

            $author = $this->model->findOrFail($id);
            $author->update(['status' => $status]);

            return $author;

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

    public function getAuthorBySlug($slug)
    {
        try {

            return $this->model->where('slug', $slug)->firstOrFail();

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}






