<?php

namespace App\Http\Controllers;

use App\Models\language;
use Illuminate\Http\Request;
use App\Http\Requests\CreatelanguageRequest;
use App\Http\Requests\UpdatelanguageRequest;
use App\Repositories\Eloquents\LanguageRepository;

class LanguageController extends Controller
{

    protected $repository;

    public function __construct(LanguageRepository $repository)
    {
        $this->authorizeResource(language::class, 'language', [
            'except' => [ 'index', 'show' ],
        ]);
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $language = $this->filter($this->repository, $request);
        return $language->latest('created_at')->paginate($request->paginate ?? $language->count());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatelanguageRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(language $language)
    {
        return $this->repository->show($language->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(language $language)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatelanguageRequest $request, language $language)
    {
        return $this->repository->update($request->all(), $language->getId($request));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request,language $language)
    {
        return $this->repository->destroy($language->getId($request));
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
     * Update rtl the specified resource from storage.
     *
     * @param  int  $id
     * @param int $rtl
     * @return \Illuminate\Http\Response
     */
    public function rtl($id, $rtl)
    {
        return $this->repository->rtl($id, $rtl);
    }

    public function getFilesInFolder(Request $request)
    {
        return $this->repository->getFilesInFolder($request);
    }

    public function getFileContent(Request $request)
    {
        return $this->repository->getFileContent($request);
    }

    public function saveFileContent(Request $request)
    {
        return $this->repository->saveFileContent($request);
    }

    public function filter($language, $request)
    {
        if (isset($request->status)) {
            $language = $language->where('status',$request->status);
        }

        return $language;
    }
}
