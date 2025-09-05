<?php

namespace App\Imports;

use App\Models\Vehicle;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithStartRow;

// ★★★★★ 文字コード関連の機能をすべて削除し、シンプルな構成に戻しました ★★★★★
class VehicleImport implements OnEachRow, WithValidation, SkipsEmptyRows, WithStartRow
{
    /**
     * @param Row $row
     *
     * @return void
     */
    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row      = $row->toArray();

        Vehicle::updateOrCreate(
            [
                'id' => $row[0]
            ],
            [
                'model_name'         => $row[1],
                'maker'              => $row[2],
                'vehicle_type'       => $row[3],
                'category'           => $row[4],
                'asset_number'       => $row[5],
                'manufacturing_year' => $row[6],
            ]
        );
    }

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            '0' => 'nullable|numeric|exists:vehicles,id',
            '1' => 'required|string|max:255',
            '2' => 'nullable|string|max:255',
            '3' => 'required|string|max:255',
            '4' => 'required|string',
            '5' => 'nullable|numeric',
            '6' => 'nullable|numeric|digits:4',
        ];
    }

    /**
     * @return int
     */
    public function startRow(): int
    {
        // 1行目のヘッダーをスキップ
        return 2;
    }
}
