<?php

namespace App\Models;

use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Theme extends Model
{
    use HasFactory, HasTranslations;

    public $translatable = [
        'name',
    ];

    /**
     * The themes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];

    /**
     * @return Int
     */
    public function getId($request)
    {
        return ($request->id) ? $request->id : $request->route('theme')->id;
    }

    public function toArray()
    {
        $attributes = parent::toArray();
        $translated = Helpers::handleModelTranslations($this, $attributes, $this->translatable);
        return $translated;
    }

}
