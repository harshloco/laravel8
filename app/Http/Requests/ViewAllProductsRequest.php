<?php

namespace App\Http\Requests;

class ViewAllProductsRequest extends FormRequestWrapper
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'filters' => 'array|nullable',
            'sortBy' => 'string|nullable',
            'sortType'  => 'string|nullable|in:asc,desc',
            'perPage' =>  'integer|nullable|max:100',
            'pagination' => 'string|nullable|in:true,false',
            'stock' => 'string|nullable|in:true,false',
        ];
    }
}
