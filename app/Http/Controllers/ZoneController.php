<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\Request;
use App\Http\Requests\CreateZoneRequest;
use App\Http\Requests\UpdateZoneRequest;
use App\Repositories\Eloquents\ZoneRepository;

class ZoneController extends Controller
{
    public $repository;

    public function __construct(ZoneRepository $repository)
    {
        $this->authorizeResource(Zone::class, 'zone', [
            'except' => [ 'index', 'show' ],
        ]);

        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        try{

            $zones = $this->filter($this->repository, $request);
            return $zones->latest('created_at')->paginate($request->paginate);

        } catch(Exception $e) {

            throw new ExceptionHandler($e->getMessage(),$e->getCode());
        }
    }

    public function create()
    {
        //
    }

    public function store(CreateZoneRequest $request)
    {
        return $this->repository->store($request);
    }

    public function show(Zone $zone)
    {
        return $this->repository->show($zone->id);
    }

    public function edit(Zone $zone)
    {
        //
    }

    public function update(UpdateZoneRequest $request, Zone $zone)
    {
        return $this->repository->update($request->all(), $zone?->getId($request));
    }

    public function destroy(Request $request, Zone $zone)
    {
        return $this->repository->destroy($zone->getId($request));
    }

    public function status(Request $request)
    {
        return $this->repository->status($request->id, $request->status);
    }

    public function deleteAll(Request $request)
    {
        return $this->repository->deleteAll($request->ids);
    }

    public function getZoneIds(Request $request)
    {
        return $this->repository->getZoneIds($request);
    }

    public function filter($zones, $request)
    {
        if (isset($request->status)) {
            $zones = $zones->where('status', $request->status);
        }

        if ($request->category_ids) {
            $category_ids = explode(',', $request->category_ids);
            $zones = $zones->whereRelation('categories', function ($categories) use ($category_ids) {
                $categories->WhereIn('category_id', $category_ids);
            });
        }

        return $zones;
    }
}
