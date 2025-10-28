<?php

namespace App\Http\Controllers;

use App\Models\Publication;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use App\Http\Requests\CreatePublicationRequest;
use App\Http\Requests\UpdatePublicationRequest;
use App\Repositories\Eloquents\PublicationRepository;

class PublicationController extends Controller
{
    public $repository;

    public function __construct(PublicationRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $publications = $this->filter($this->repository, $request);
        return $publications->latest('created_at')->paginate($request->paginate ?? $publications->count());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreatePublicationRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Publication $publication)
    {
        return $this->repository->show($publication->id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePublicationRequest $request, Publication $publication)
    {
        return $this->repository->update($request->all(), $publication->getId($request));
    }

    /**
     * Update Status the specified resource from storage.
     *
     * @param  int  $id
     * @param int $status
     * @return \Illuminate\Http\Response
     */
    public function status(Request $request)
    {
        return $this->repository->status($request->id, $request->status);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Publication $publication)
    {
        return $this->repository->destroy($publication->getId($request));
    }

    public function deleteAll(Request $request)
    {
        return $this->repository->deleteAll($request->ids);
    }

    public function getPublicationBySlug($slug)
    {
        return $this->repository->getPublicationBySlug($slug);
    }

    public function filter($publications, $request)
    {
        if ($request->field && $request->sort) {
            $publications = $publications->orderBy($request->field, $request->sort);
        }

        if (isset($request->status)) {
            $publications = $publications->whereStatus($request->status);
        }

        return $publications;
    }
}
