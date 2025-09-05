<?php

namespace App\Exports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings; // ★★★★★ 文字化け対策のために追加 ★★★★★

class VehicleExport implements FromCollection, WithHeadings, WithMapping, WithCustomCsvSettings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Vehicle::all();
    }

    public function headings(): array
    {
        // ★★★★★ ヘッダーの先頭に「ID」を追加 ★★★★★
        return [
            'ID',
            '名称',
            'メーカー',
            '種別',
            'カテゴリ',
            '管理番号',
            '年式',
        ];
    }

    public function map($vehicle): array
    {
        // ★★★★★ データの先頭に「ID」を追加 ★★★★★
        return [
            $vehicle->id,
            $vehicle->model_name,
            $vehicle->maker,
            $vehicle->vehicle_type,
            $vehicle->category,
            $vehicle->asset_number,
            $vehicle->manufacturing_year,
        ];
    }

    // ★★★★★ このメソッドをまるごと追加 ★★★★★
    public function getCsvSettings(): array
    {
        // Excelで開いた際の文字化けを防ぐため、BOM（Byte Order Mark）を付ける設定
        return [
            'use_bom' => true,
        ];
    }
}
