<?php

namespace App\Imports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;

class UserImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return User::updateOrCreate(
            ['id' => $row['id'] ?? null],
            [
                'name'     => $row['name'],
                'email'    => $row['email'],
                'is_admin' => $row['is_admin_1管理者_0担当者'], // ヘッダー名に合わせる
                'password' => Hash::make(str()->random(10)), // ダミーパスワード
            ]
        );
    }
}
