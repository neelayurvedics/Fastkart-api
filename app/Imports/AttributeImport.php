<?php

namespace App\Imports;

use App\Models\Attribute;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use App\GraphQL\Exceptions\ExceptionHandler;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class AttributeImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    private $attributes = [];
    private $translateFields = ['name','value'];
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:255', 'unique:attributes,name,NULL,id,deleted_at,NULL'],
            'style' => ['required', 'in:rectangle,circle,color,radio,image,dropdown'],
            'status' => ['required','min:0','max:1'],
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.unique' => __('validation.name_already_taken'),
            'name.required' => __('validation.name_field_required'),
            'status.required' => __('validation.status_field_required'),
            'style.required' => __('validation.style_field_required'),
        ];
    }

    /**
     * @param \Throwable $e
     */
    public function onError(\Throwable $e)
    {
        throw new ExceptionHandler($e->getMessage() , 422);
    }

    public function getImportedAttributes()
    {
        return $this->attributes;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $attributeValues = [];
        $row = $this->filterRow($row);
        $attribute = new Attribute([
            'name' => $row['name'],
            'style' => $row['style'],
            'status' => $row['status'],
        ]);
        $this->setTranslations($attribute, $row);
        $attribute->save();
        $attribute = $attribute->fresh();
        if (isset($row['values'])) {
            $attributeValues = $this->createAttributeValues($attribute, json_decode($row['values']));
        }

        $this->attributes[] = [
            'id' => $attribute->id,
            'name' =>  $attribute->name,
            'style' => $attribute->style,
            'status' => $attribute->status,
            'attribute_values' => $attributeValues
        ];

        return $attribute;
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

    function createAttributeValues ($attribute, $attributeValues)
    {
        foreach ($attributeValues as $key => $value) {
            foreach ($value as $k => $v) {
                $separate = explode('_',$k);
                if(in_array(head($separate),$this->translateFields)) {
                    $formattedField[head($separate)][last($separate)] = $v;
                }else{
                    $formattedField[$k] = $v;
                }
            }
            $formattedFields[] = $formattedField;
        }
        return $attribute->attribute_values()->createMany($formattedFields);
    }
}
