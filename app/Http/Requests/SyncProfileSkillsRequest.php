<?php

namespace App\Http\Requests;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SyncProfileSkillsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->role === RoleEnum::Pelamar;
    }

    protected function prepareForValidation(): void
    {
        if (is_array($this->input('skills'))) {
            $this->merge(['skills' => array_map(
                static fn (mixed $skill): mixed => is_string($skill) ? trim($skill) : $skill,
                $this->input('skills'),
            )]);
        }
    }

    public function rules(): array
    {
        return [
            'skills' => ['sometimes', 'array', 'max:30'],
            'skills.*' => ['required', 'string', 'max:80'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $skills = $this->input('skills', []);
            if (! is_array($skills)) {
                return;
            }

            $normalized = array_map(static fn (mixed $skill): string => mb_strtolower(trim((string) $skill)), $skills);
            if (count($normalized) !== count(array_unique($normalized))) {
                $validator->errors()->add('skills', 'Hapus keahlian yang duplikat sebelum menyimpan.');
            }
        }];
    }
}
