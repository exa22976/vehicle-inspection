<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UserExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return User::select('id', 'name', 'email', 'is_admin')->get();
    }

    public function headings(): array
    {
        return ['id', 'name', 'email', 'is_admin (1=管理者, 0=担当者)'];
    }
}
