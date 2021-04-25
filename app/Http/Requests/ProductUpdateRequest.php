<?php

namespace App\Http\Requests;

class ProductUpdateRequest extends FormRequestWrapper
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'code' => 'required|string|max:255',
            'name' => 'required|max:255',
            'description'  => 'required|max:255',
        ];
    }
}
