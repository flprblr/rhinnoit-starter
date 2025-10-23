<?php

namespace App\Http\Requests\Maintainers\Permissions;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePermissionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $permission = $this->route('permission'); // viene del route model binding

        return [
            'name' => [
                'required',
                'string',
                'min:3',
                'max:255',
                'unique:permissions,name,'.$permission?->id,
            ],
        ];
    }
}
