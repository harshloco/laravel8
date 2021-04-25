<?php

namespace App\Http\Requests;

class ProductStockStoreRequest extends FormRequestWrapper
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'onHand' => 'nullable|integer',
            'taken' => 'nullable|integer',
            'productionDate'  => 'nullable|date_format:d/m/Y',
        ];
    }
}
