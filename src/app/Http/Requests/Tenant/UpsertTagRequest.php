<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $tag = $this->route('tag');
        $tagId = $tag ? $tag->id : null;

        return [
            'nome_tag' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tenant.tags', 'nome_tag')
                    ->ignore($tagId),
            ],
            'cor' => [
                'nullable',
                'string',
                'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nome_tag.unique' => 'O nome da tag já está em uso.',
            'cor.regex' => 'O campo cor deve ser um código hexadecimal válido (ex: #cccccc).',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('nome_tag')) {
            $this->merge([
                'nome_tag' => mb_strtoupper(trim($this->input('nome_tag'))),
            ]);
        }
    }
}
