<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithStartRow;

class UserImport implements OnEachRow, WithValidation, SkipsEmptyRows, WithStartRow
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

        // CSVの「はい」「いいえ」を 1/0 に変換
        $isAdmin = trim($row[3]) === 'はい' ? 1 : 0;

        // ID（$row[0]）が存在すれば更新、なければ新規作成
        $user = User::updateOrCreate(
            [
                'id' => $row[0]
            ],
            [
                'name'     => $row[1],
                'email'    => $row[2],
                'is_admin' => $isAdmin,
            ]
        );

        if ($user->wasRecentlyCreated) {
            $user->password = Hash::make('password');
            $user->save();
        }
    }

    /**
     * バリデーションルール
     * @return array
     */
    public function rules(): array
    {
        return [
            '0' => 'nullable|numeric|exists:users,id',
            '1' => 'required|string|max:255',
            '2' => 'required|string|email|max:255',
            '3' => 'required|string|in:はい,いいえ',
        ];
    }

    /**
     * 開始行（ヘッダーをスキップ）
     * @return int
     */
    public function startRow(): int
    {
        // 1行目のヘッダーをスキップ
        return 2;
    }
}
