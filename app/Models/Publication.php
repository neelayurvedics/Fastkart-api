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

class Publication extends Model implements HasMedia
{
    use Sluggable, HasFactory, SoftDeletes, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'publisher_name',
        'description',
        'slug',
        'publisher_logo_id',
        'publisher_cover_image_id',
        'country_id',
        'state_id',
        'city',
        'facebook',
        'twitter',
        'instagram',
        'youtube',
        'pinterest',
        'created_by_id',
        'status',
    ];

    protected $with = [
        'publisher_logo',
        'publisher_cover_image',
        'country:id,name',
        'state:id,name'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'status' => 'integer'
    ];

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'publisher_name',
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
        return ($request->id) ? $request->id : $request->route('publication')->id;
    }

    /**
     * @return BelongsTo
     */
    public function publisher_logo(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'publisher_logo_id');
    }

    /**
     * @return BelongsTo
     */
    public function publisher_cover_image(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'publisher_cover_image_id');
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
}
