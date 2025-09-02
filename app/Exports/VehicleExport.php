<?php

namespace App\Exports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VehicleExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // エクスポートするデータを指定
        return Vehicle::select('id', 'model_name', 'vehicle_type', 'category', 'asset_number', 'manufacturing_year')->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'model_name',
            'vehicle_type',
            'category',
            'asset_number',
            'manufacturing_year',
        ];
    }
}
