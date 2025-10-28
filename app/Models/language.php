<?php

namespace App\Models;

use File;
use App\Helpers\Helpers;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class language extends Model
{
    use HasFactory, SoftDeletes, HasTranslations;

    public $translatable = [
        'name',
    ];

    protected $table = 'languages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'flag',
        'locale',
        'is_rtl',
        'system_reserve',
        'status'
    ];

    protected $casts = [
        'status' => 'integer',
        'is_rtl' => 'integer',
    ];

    public static function boot()
    {
        parent::boot();
        Static::created(function ($language){
            self::createLangFolder($language);
        });

        Static::deleting(function ($language){
            self::deleteLangFolder($language);
        });
    }

    public function toArray()
    {
        $attributes = parent::toArray();
        $translated = Helpers::handleModelTranslations($this, $attributes, $this->translatable);
        return $translated;
    }

    /**
     * @return Int
     */
    public function getId($request)
    {
        return ($request->id) ? $request->id : $request->route('language')->id;
    }

    public static function createLangFolder($language)
    {
        $langDir = resource_path().'/lang/';
        $locale = Helpers::getDefaultLanguageLocale();
        $enDir = $langDir.$locale;
        $currentLang = $langDir . $language->locale;
        if(!File::exists($currentLang)){
            File::makeDirectory($currentLang);
            File::copyDirectory($enDir,$currentLang);
        }
    }

    public static function deleteLangFolder($language)
    {
        $folderURL = resource_path().'/lang/'.$language->locale;
        if(File::exists($folderURL)){
            File::deleteDirectory($folderURL);
        }
    }
}
