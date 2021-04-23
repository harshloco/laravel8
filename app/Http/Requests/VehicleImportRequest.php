<?php

namespace App\Http\Requests;

class VehicleImportRequest extends FormRequestWrapper
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => 'required|file|max:10000|mimes:csv,txt',

        ];
    }

    public function messages()
    {
        return [
            'file.max'  => 'Maximum file size to upload is 10MB. Try splitting the file.',
        ];
    }
}

