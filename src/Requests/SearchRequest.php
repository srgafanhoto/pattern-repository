<?php

namespace srgafanhoto\PatternRepository\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

        return [
            'search' => 'nullable|string',
            'sort' => 'nullable|string',
            'page' => 'nullable|numeric',
            'order' => 'nullable|string',
            'filter' => 'nullable|string',
            'searchFields' => 'nullable|string',
            'with' => 'nullable|string',
        ];
    }
}
