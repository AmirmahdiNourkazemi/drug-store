<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DrugSearchRequest extends FormRequest
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
        return [
            'q' => 'required|string|min:2|max:255',
            'search_fields' => 'nullable|array',
            'search_fields.*' => 'in:nam_fa,nam_en,mavaredmasraf,avarez,tadakhol',
            'goroh_daroei_cod' => 'nullable|integer',
            'goroh_darmani_cod' => 'nullable|integer',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:1|max:100',
             'with_relations' => 'nullable|boolean',
        ];
    }

       protected function prepareForValidation()
{
    $this->merge([
        'q' => $this->input('q') ?? $this->input('query'),
        'with_relations' => $this->boolean('with_relations'), // Use Laravel's boolean() method
    ]);
}

        public function messages(): array
        {
            return [
                'q.required' => 'عبارت جستجو الزامی است.',
                'q.string' => 'عبارت جستجو باید متن باشد.',
                'with_relations.in' => 'فیلد with_relations باید true یا false باشد.',
                'search_fields.array' => 'فیلدهای جستجو باید آرایه باشد.',
                'search_fields.*.in' => 'فیلد جستجو نامعتبر است.',
            ];
        }
}
