<?php

namespace App\Exports;

use App\Models\LicenseKey;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class LicenseKeysExport implements FromCollection, WithMapping, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return LicenseKey::whereNull('deleted_at')->latest('created_at')->get();
    }

    public function columns(): array
    {
        return ["id", "license_key", "product", "variation", "purchased_name","purchased_email","status", "created_at"];
    }

    public function map($licenseKeys): array
    {
        return [
            $licenseKeys->id,
            $licenseKeys->license_key,
            $licenseKeys->product?->name,
            $licenseKeys->variation?->name,
            $licenseKeys->purchased_by?->name,
            $licenseKeys->purchased_by?->email,
            $licenseKeys->status,
            $licenseKeys->created_at,
        ];
    }

    public function headings(): array
    {
        return $this->columns();
    }
}
