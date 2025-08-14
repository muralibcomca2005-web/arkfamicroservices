<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class StoreCourseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Gate::allows('only-admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'string|required|max:255',
            'short_description' => 'string|required|max:500',
            'description' => 'string|required',
            'category' => 'string|required|max:100',
            'price' => 'numeric|required|min:0',

            'lessons' => 'array|required|min:1',
            'lessons.*.title' => 'string|required|max:255',
            'lessons.*.body' => 'string|required',
            'lessons.*.order' => 'integer|required|min:1',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'lessons.required' => 'At least one lesson is required for the course.',
            'lessons.min' => 'At least one lesson is required for the course.',
            'price.min' => 'Price cannot be negative.',
        ];
    }

    /**
     * Handle a failed authorization attempt.
     */
    protected function failedAuthorization()
    {
        abort(response()->json([
            'message' => 'Unauthorized User'
        ], 403));
    }
}
