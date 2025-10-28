<?php

namespace App\Imports;

use App\Models\Zone;
use App\Models\Category;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\ToModel;
use App\GraphQL\Exceptions\ExceptionHandler;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CategoryImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    private $categories = [];
    private $translateFields = ['name','description', 'meta_title', 'meta_description'];
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:255', Rule::unique('categories')->where('type', 'product')->whereNull('deleted_at')],
            'parent_id' => ['nullable','exists:categories,id,deleted_at,NULL'],
            'status' => ['required','min:0','max:1'],
            'commission_rate' => ['nullable', 'regex:/^([0-9]{1,2}){1}(\.[0-9]{1,2})?$/'],
            'type' => ['required','in:post,product']
        ];
    }

    public function customValidationMessages()
    {
        return [
            'commission_rate.regex' => __('validation.commission_rate_between'),
            'type.in' => __('validation.category_type_post_or_product'),
            'name.unique' => __('validation.name_already_taken'),
            'parent_id.exists' => __('validation.category_id_required'),
            'status.required' => __('status_required'),
        ];
    }

    /**
     * @param \Throwable $e
     */
    public function onError(\Throwable $e)
    {
        throw new ExceptionHandler($e->getMessage() , 422);
    }

    public function getImportedCategories()
    {
        return $this->categories;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $row = $this->filterRow($row);
        $category = new Category([
            'name' =>  $row['name'],
            'description' => $row['description'],
            'type' => $row['type'],
            'status' => $row['status'],
            'commission_rate' => $row['commission_rate'],
            'parent_id' => $row['parent_id'],
            'meta_title' => $row['meta_title'],
            'meta_description' => $row['meta_description'],
            'is_allow_all_zone' => $row['is_allow_all_zone'],
        ]);

        $this->setTranslations($category, $row);

        if (isset($row['category_image_url'])) {
            $media = $category->addMediaFromUrl($row['category_image_url'])->toMediaCollection('attachment');
            $media->save();
            $category->category_image_id = $media->id;
            $category->save();
        }

        if (isset($row['category_icon_url'])) {
            $media = $category->addMediaFromUrl($row['category_icon_url'])->toMediaCollection('attachment');
            $media->save();
            $category->category_icon_id = $media->id;
            $category->save();
        }

        if ($row['is_allow_all_zone']) {
            $category->zones()->attach(Zone::whereNull('deleted_at')->get());
            $category->zones;
        }

        if (isset($row['zone_ids'])) {
            $category->zones()->attach($row['zone_ids'] ?? []);
            $category->zones;
        }

        if (isset($row['exclude_zone_ids'])){
            $category->exclude_zones()->attach($row['exclude_zone_ids'] ?? []);
            $category->exclude_zones;
        }

        $category = $category->fresh();
        $this->categories[] = [
            'id' => $category->id,
            'name' =>  $category->name,
            'description' => $category->description,
            'type' => $category->type,
            'status' => $category->status,
            'commission_rate' => $category->commission_rate,
            'parent_id' => $category->parent_id,
            'category_image' => $category->category_image,
            'category_icon' => $category->category_icon
        ];

        return $category;
    }

    function filterRow($row)
    {
        foreach ($row as $key => $value) {

            $lastUnderscorePos = strrpos($key, "_");
            $separatedKeys = [
                1 => substr($key, 0, $lastUnderscorePos),
                2 => substr($key, $lastUnderscorePos + 1),
            ];
            if(in_array(head($separatedKeys),$this->translateFields)) {
                $rows[head($separatedKeys)][last($separatedKeys)] = $value;
            }else{
                $rows[$key] = $value;
            }
        }
        return $rows;
    }

    function setTranslations($data, $row)
    {
        $locale = app()->getLocale();
        foreach ($row as $key => $value) {
            if ($data->isTranslatableAttribute($key)) {
                $translations = is_array($value) ? $value : [$locale => $value];
                $data->setTranslations($key, $translations);
            }
        }
        return $data->save();
    }
}
