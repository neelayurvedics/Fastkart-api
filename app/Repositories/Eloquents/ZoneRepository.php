<?php

namespace App\Repositories\Eloquents;

use Exception;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use App\GraphQL\Exceptions\ExceptionHandler;
use MatanYadaev\EloquentSpatial\Objects\Point;
use Prettus\Repository\Eloquent\BaseRepository;
use Prettus\Repository\Criteria\RequestCriteria;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Objects\LineString;

class ZoneRepository extends BaseRepository
{

    protected $fieldSearchable = [
        'name' => 'like'
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
        return Zone::class;
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

            $coordinates = $request->place_points;
            $points = array_map(function ($coordinate) {
                return new Point($coordinate['lat'], $coordinate['lng']);
            }, $coordinates);

            if (head($points) != $points[count($points) - 1]) {
                $points[] = head($points);
            }

            $lineString = new LineString($points);
            $place_points = new Polygon([$lineString]);

            $zone = $this->model->create([
                'name' => $request->name,
                'place_points' => $place_points,
                'locations' => $coordinates,
                'status' => $request->status,
            ]);

            DB::commit();
            return $zone;

        } catch (Exception $e) {

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function update($request, $id)
    {
        DB::beginTransaction();
        try {

            $zone = $this->model->findOrFail($id);
            if (isset($request['place_points']) && ! empty($request['place_points'])) {
                $coordinates = $request['place_points'];
                $points = array_map(function ($coordinate) {
                    return new Point($coordinate['lat'], $coordinate['lng']);
                }, $coordinates);

                if (head($points) != $points[count($points) - 1]) {
                    $points[] = head($points);
                }

                $lineString = new LineString($points);
                $place_points = new Polygon([$lineString]);
                unset($request['place_points']);

                $zone->place_points = $place_points;
                $zone->locations = $coordinates;
                $zone->save();
            }

            $zone->update($request);

            DB::commit();
            return $zone;

        } catch (Exception $e){

            DB::rollback();
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getZoneIds($request)
    {
        try {

            if (isset($request->lat) && isset($request->lng)) {

                $lat = (float)$request->lat;
                $lng = (float)$request->lng;

                if ($lat && $lng) {
                    $points = new Point($lat, $lng);
                    $zones = Zone::whereContains('place_points', $points)->get(['id', 'name']);
                    if (!$zones) {
                        throw new Exception(__('errors.zone_not_found'), 400);
                    }

                    return $zones;

                }

                throw new Exception(__('errors.invalid_coordinates'), 400);
            }

            throw new Exception(__('errors.coordinates_required'));

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

            $zone = $this->model->findOrFail($id);
            $zone->update(['status' => $status]);

            return $zone;

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function deleteAll($ids)
    {
        try {

            return $this->model->whereIn('id', $ids)->delete();

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
