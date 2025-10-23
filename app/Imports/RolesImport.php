<?php

namespace App\Imports;

use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class RolesImport implements SkipsEmptyRows, ToCollection, WithHeadingRow, WithValidation
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|min:1',
            'name' => 'required|string|min:5|max:255|regex:/^[a-zA-Z\s]+$/',
        ];
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                Role::updateOrCreate(
                    ['id' => $row['id']],
                    [
                        'name' => strtolower(trim($row['name'])),
                        'guard_name' => 'web',
                    ]
                );
            }
        });
    }
}
