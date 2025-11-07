<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping; // VehicleExportと同様にMappingを追加
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings; // VehicleExportと同様に文字化け対策を追加

// VehicleExportと同様のインターフェースを実装
class UserExport implements FromCollection, WithHeadings, WithMapping, WithCustomCsvSettings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // collection() では関連情報を含めて全データを取得するだけにする
        return User::with('department', 'vehicles')->get();
    }

    /**
     * CSVのヘッダー（項目名）行を定義
     * @return array
     */
    public function headings(): array
    {
        return [
            'ID',
            '名前',
            'メールアドレス',
            '管理者フラグ',
        ];
    }

    /**
     * 各行のデータを整形する
     * （このメソッドを丸ごと追加）
     *
     * @param mixed $user
     * @return array
     */
    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->email,
            $user->is_admin ? 'はい' : 'いいえ',
        ];
    }

    /**
     * Excelでの文字化けを防ぐためにBOMを付与する設定
     * （このメソッドを丸ごと追加）
     *
     * @return array
     */
    public function getCsvSettings(): array
    {
        return [
            'use_bom' => true,
        ];
    }
}
