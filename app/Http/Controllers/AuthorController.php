<?php

namespace App\Http\Controllers;

use App\Models\Author;
use App\Enums\RoleEnum;
use App\Helpers\Helpers;
use Illuminate\Http\Request;
use App\Http\Requests\CreateAuthorRequest;
use App\Http\Requests\UpdateAuthorRequest;
use App\Repositories\Eloquents\AuthorRepository;

class AuthorController extends Controller
{
    public $repository;

    public function __construct(AuthorRepository $repository)
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
        $authors = $this->filter($this->repository, $request);
        return $authors->latest('created_at')->paginate($request->paginate ?? $authors->count());
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
    public function store(CreateAuthorRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Author $author)
    {
        return $this->repository->show($author->id);
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
    public function update(UpdateAuthorRequest $request, Author $author)
    {
        return $this->repository->update($request->all(), $author->getId($request));
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
    public function destroy(Request $request, Author $author)
    {
        return $this->repository->destroy($author->getId($request));
    }

    public function deleteAll(Request $request)
    {
        return $this->repository->deleteAll($request->ids);
    }

    public function getAuthorBySlug($slug)
    {
        return $this->repository->getAuthorBySlug($slug);
    }

    public function filter($authors, $request)
    {
        if ($request->field && $request->sort) {
            $authors = $authors->orderBy($request->field, $request->sort);
        }

        if (isset($request->status)) {
            $authors = $authors->whereStatus($request->status);
        }

        return $authors;
    }
}
