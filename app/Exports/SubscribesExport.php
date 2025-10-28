<?php

namespace App\Exports;

use App\Models\Subscribe;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class SubscribesExport implements FromCollection, WithMapping, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Subscribe::whereNull('deleted_at')->latest('created_at')->get();
    }

    public function columns(): array
    {
        return ["id","email","created_at"];
    }

    public function map($tag): array
    {
        return [
            $tag->id,
            $tag->email,
            $tag->created_at,
        ];
    }

    public function headings(): array
    {
        return $this->columns();
    }
}
