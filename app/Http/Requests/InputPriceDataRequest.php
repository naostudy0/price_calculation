<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class InputPriceDataRequest extends FormRequest
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
            'enter-time' => 'required | date',
            'leave-time' => 'required | date | after_or_equal:enter-time',
            'course' => 'required | numeric | regex:/^[0-3]+$/',
        ];
    }
}
