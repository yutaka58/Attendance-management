<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceDetailRequest extends FormRequest
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
            'end_time' => 'after:start_time',
            'rest_start.*' => 'nullable|after:start_time|before:end_time',
            'rest_end.*' => 'nullable|after:rest_start.*|before:end_time',
            'remarks_column' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'rest_start.*.after' => '休憩時間が不適切な値です',
            'rest_end.*.after' => '休憩時間が不適切な値です',
            'rest_end.*.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'remarks_column.required' => '備考を記入してください',
        ];
    }
}
