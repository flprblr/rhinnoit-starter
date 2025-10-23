<?php

namespace App\Http\Requests\Maintainers\Roles;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoleRequest extends FormRequest
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
        $role = $this->route('role');

        return [
            'name' => ['required', 'string', 'min:3', 'max:255', 'unique:roles,name,'.($role?->id)],
            'permissions' => ['array'],
            'permissions.*' => ['exists:permissions,id'],
        ];
    }
}
