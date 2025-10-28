<?php
namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Enums\SortByEnum;
use App\GraphQL\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Http\Requests\CreateBlogRequest;
use App\Http\Requests\UpdateBlogRequest;
use App\Models\Blog;
use App\Repositories\Eloquents\BlogRepository;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public $repository;

    public function __construct(BlogRepository $repository)
    {
        $this->authorizeResource(Blog::class, 'blog', [
            'except' => ['index', 'show'],
        ]);

        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {

            $blog = $this->filter($this->repository, $request);
            return $blog->latest('created_at')->paginate($request->paginate ?? $blog->count());

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
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
    public function store(CreateBlogRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Blog $blog)
    {
        return $this->repository->show($blog->id);
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
    public function update(UpdateBlogRequest $request, Blog $blog)
    {
        return $this->repository->update($request->all(), $blog->getId($request));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Blog $blog)
    {
        return $this->repository->destroy($blog->getId($request));
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

    public function deleteAll(Request $request)
    {
        return $this->repository->deleteAll($request->ids);
    }

    public function getBlogBySlug($slug)
    {
        return $this->repository->getBlogBySlug($slug);
    }

    public function filter($blog, $request)
    {
        if (! Helpers::isUserLogin() || (Helpers::getCurrentRoleName() == RoleEnum::CONSUMER)) {
            $blog = $blog->orderBy('is_sticky', SortByEnum::DESC);
        }

        if ($request->ids) {
            $ids = explode(',', $request->ids);
            $blog->whereIn('id', $ids);
        }

        if ($request->field && $request->sort) {
            $blog = $blog->orderBy($request->field, $request->sort);
        }

        if (isset($request->status)) {
            $blog = $blog->where('status', $request->status);
        }

        if ($request->category) {
            $slugs = explode(',', $request->category);
            $blog = $blog->where(function ($query) use ($slugs) {
                $query->where('is_all_categories', true)
                    ->orWhere(function ($q) use ($slugs) {
                        $q->where('is_all_categories', false)
                            ->where('status', true)
                            ->whereHas('categories', function (Builder $categories) use ($slugs) {
                                $categories->whereIn('categories.slug',  $slugs);
                            });
                    });
            });
        }

        if ($request->tag) {
            $slugs = explode(',', $request->tag);
            $blog = $blog->where(function ($query) use ($slugs) {
                $query->where('is_all_tags', true)
                    ->orWhere(function ($q) use ($slugs) {
                        $q->where('is_all_tags', false)
                            ->where('status', true)
                            ->whereHas('tags', function (Builder $tags) use ($slugs) {
                                $tags->whereIn('tags.slug',  $slugs);
                            });
                    });
            });
        }

        if ($request->category_ids) {
            $categoryIds = is_array($request->category_ids) ? $request->category_ids : [];
            $blog = $blog->where(function ($query) use ($categoryIds) {
                $query->where('is_all_categories', true)
                    ->orWhere(function ($q) use ($categoryIds) {
                        $q->where('is_all_categories', false)
                            ->where('status', true)
                            ->whereHas('categories', function (Builder $categories) use ($categoryIds) {
                                $categories->whereIn('categories.id', $categoryIds);
                            });
                    });
            });
        }

        if ($request->is_all_tags) {
            $tagIds = is_array($request->tag_ids) ? $request->tag_ids : [];
            $blog = $blog->where(function ($query) use ($tagIds) {
                $query->where('is_all_tags', true)
                    ->orWhere(function ($q) use ($tagIds) {
                        $q->where('is_all_tags', false)
                            ->where('status', true)
                            ->whereHas('tags', function (Builder $tags) use ($tagIds) {
                                $tags->whereIn('tags.id', $tagIds);
                            });
                    });
            });
        }

        return $blog;
    }
}
