<?php

namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class UsersImport implements SkipsEmptyRows, ToCollection, WithHeadingRow, WithValidation
{
    public function rules(): array
    {
        return [
            'id' => 'integer|min:1',
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|max:255',
            'password' => [
                'nullable',
                'string',
                'min:12',
                'max:128',
                'regex:/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[^\w\s]).{12,128}$/',
            ],
            'status' => 'nullable',
            'dni' => 'nullable|string|max:20',
            'phone' => 'nullable|string|max:20',
        ];
    }

    public function collection(Collection $rows)
    {
        DB::transaction(function () use ($rows) {
            foreach ($rows as $row) {
                User::updateOrCreate(
                    ! empty($row['id'])
                        ? ['id' => $row['id']]
                        : ['email' => strtolower(trim($row['email']))],
                    array_filter([
                        'name' => trim($row['name']),
                        'email' => strtolower(trim($row['email'])),
                        'status' => $this->normalizeStatus($row['status'] ?? null),
                        'dni' => $row['dni'] ?? null,
                        'phone' => $row['phone'] ?? null,
                        'password' => $row['password'] ?? null,
                    ], fn ($v) => $v !== null)
                );
            }
        });
    }

    private function normalizeStatus($value): bool
    {
        if ($value === null || $value === '') {
            return true;
        }

        if (is_bool($value)) {
            return $value;
        }

        $str = strtolower(trim((string) $value));

        if (in_array($str, ['1', 'true', 'activo', 'active'])) {
            return true;
        }

        if (in_array($str, ['0', 'false', 'inactivo', 'inactive'])) {
            return false;
        }

        if (is_numeric($value)) {
            return ((int) $value) === 1;
        }

        return (bool) $value;
    }
}
