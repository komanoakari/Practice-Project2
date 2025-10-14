<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'start_time' => ['required', 'before:end_time'],
            'end_time' => ['required', 'after:start_time'],
            'remarks' => ['required'],
            'break_starts' => ['nullable', 'array'],
            'break_end' => ['nullable', 'array'],
        ];
    }

    public function messages()
    {
        return [
            'start_time.required' => '出勤時間を入力してください',
            'start_time.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'end_time.required' => '退勤時間を入力してください',
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'remarks.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $startTime = $this->start_time;
            $endTime = $this->end_time;
            $breakStarts = $this->input('break_starts', []);
            $breakEnds = $this->input('break_ends', []);
            $count = max(count($breakStarts), count($breakEnds));

            for ($i = 0; $i < $count; $i++) {
                $breakStart = $breakStarts[$i] ?? null;
                $breakEnd = $breakEnds[$i] ?? null;

                if (empty($breakStart) && empty($breakEnd)) {
                    continue;
                }

                if (empty($breakStart) || empty($breakEnd)) {
                    $validator->errors()->add('break_error', '休憩時間が不適切な値です');
                    break;
                }

                if ($breakStart < $startTime || $breakStart > $endTime) {
                    $validator->errors()->add('break_error', '休憩時間が不適切な値です');
                    break;
                }

                if ($breakEnd > $endTime) {
                    $validator->errors()->add('break_error', '休憩時間もしくは退勤時間が不適切な値です');
                    break;
                }

                if ($breakStart >= $breakEnd) {
                    $validator->errors()->add('break_error', '休憩時間が不適切な値です');
                    break;
                }
            }
        });
    }
}