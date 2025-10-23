<?php

namespace App\Exports;

use App\Models\Permission;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PermissionsExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function collection()
    {
        return Permission::select('id', 'name', 'created_at', 'updated_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Created At',
            'Updated At',
        ];
    }

    public function map($permission): array
    {
        return [
            $permission->id,
            $permission->name,
            $permission->created_at,
            $permission->updated_at,
        ];
    }
}
