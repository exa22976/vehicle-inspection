<?php

namespace App\Imports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Validation\Rule;

class VehicleImport implements ToModel, WithHeadingRow, WithValidation
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // ★IDがあれば更新、なければ新規作成する
        return Vehicle::updateOrCreate(
            ['id' => $row['id'] ?? null], // IDで検索
            [
                // 更新または作成するデータ
                'model_name'         => $row['model_name'],
                'vehicle_type'       => $row['vehicle_type'],
                'category'           => $row['category'],
                'asset_number'       => $row['asset_number'],
                'manufacturing_year' => $row['manufacturing_year'],
            ]
        );
    }

    /**
     * ★各行のバリデーションルールを定義
     */
    public function rules(): array
    {
        return [
            'id' => 'nullable|integer|exists:vehicles,id',
            'model_name' => 'required|string|max:255',
            'vehicle_type' => 'required|string|max:255',
            'category' => ['required', Rule::in(['車両', '重機'])],
            'asset_number' => 'nullable|integer',
            'manufacturing_year' => 'nullable|integer|min:1900',

            // 各行に対するルール
            '*.id' => 'nullable|integer|exists:vehicles,id',
            '*.model_name' => 'required|string|max:255',
            '*.vehicle_type' => 'required|string|max:255',
            '*.category' => ['required', Rule::in(['車両', '重機'])],
            '*.asset_number' => 'nullable|integer',
            '*.manufacturing_year' => 'nullable|integer|min:1900',
        ];
    }
}
