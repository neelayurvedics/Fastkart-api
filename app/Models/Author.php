<?php

namespace App\Models;

use App\Helpers\Helpers;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Author extends Model implements HasMedia
{
    use Sluggable, HasFactory, SoftDeletes, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'author_name',
        'bio',
        'slug',
        'author_image_id',
        'author_cover_image_id',
        'country_id',
        'state_id',
        'city',
        'birth_date',
        'death_date',
        'languages',
        'facebook',
        'twitter',
        'instagram',
        'youtube',
        'pinterest',
        'created_by_id',
        'status',
    ];

    protected $with = [
        'author_image',
        'author_cover_image',
        'country:id,name',
        'state:id,name'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'birth_date' => 'date',
        'death_date' => 'date',
        'status' => 'integer'
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'author_name',
                'onUpdate' => true,
            ]
        ];
    }

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->created_by_id = Helpers::getCurrentUserId();
            if (request()['slug']) {
                $model->slug = request()['slug'];
            }
        });
    }

    /**
     * @return Int
     */
    public function getId($request)
    {
        return ($request->id) ? $request->id : $request->route('author')->id;
    }

    /**
     * @return BelongsTo
     */
    public function author_image(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'author_image_id');
    }

    /**
     * @return BelongsTo
     */
    public function author_cover_image(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'author_cover_image_id');
    }

    /**
     * @return BelongsTo
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * @return BelongsTo
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'author_product');
    }
}
