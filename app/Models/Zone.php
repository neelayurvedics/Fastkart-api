<?php

namespace App\Models;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Zone extends Model
{
    use HasFactory, HasSpatial, SoftDeletes;

    protected $fillable = [
        'name',
        'place_points',
        'locations',
        'status',
        'created_by_id',
    ];

    protected $spatialFields = [
        'place_points',
        'zones:id,name'
    ];

    protected $casts = [
        'place_points' => Polygon::class,
        'locations' => 'json',
        'status' => 'integer',
    ];

    public static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->created_by_id = Helpers::getCurrentUserId() ?? Helpers::getAdmin()?->id;
        });
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name',
                'onUpdate' => true,
            ]
        ];
    }

    /**
     * @return Int
     */
    public function getId($request)
    {
        return ($request->id) ? $request->id : $request->route('zone')->id;
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_zones');
    }
}
