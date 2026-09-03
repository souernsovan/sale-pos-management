<?php

namespace App\Http\Requests;

use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('roles', 'name'),
                Rule::notIn(RolesAndPermissionsSeeder::BUILT_IN_ROLES),
            ],
            'permissions' => ['array'],
            'permissions.*' => [Rule::in(RolesAndPermissionsSeeder::allPermissions())],
        ];
    }

    public function messages(): array
    {
        return [
            'name.not_in' => 'That name is reserved for a built-in role.',
        ];
    }
}
