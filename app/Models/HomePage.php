<?php

namespace App\Models;

use App\Helpers\Helpers;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HomePage extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, HasTranslations;

    public $translatable = [
        'content'
    ];

    protected $casts = [
        'content' => 'json',
    ];

    /**
     * The Options that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content',
        'slug',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    /**
     * @return Int
     */
    public function getId($request)
    {
        return ($request->id) ? $request->id : $request->route('home');
    }

    public function toArray()
    {
        $attributes = parent::toArray();
        $translated = Helpers::handleModelTranslations($this, $attributes, $this->translatable);
        return $translated;
    }
}
