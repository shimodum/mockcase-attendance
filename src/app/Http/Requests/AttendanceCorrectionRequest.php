<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i', 'after:clock_in'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end' => ['nullable', 'date_format:H:i', 'after:breaks.*.break_start'],
            'note' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時刻を入力してください',
            'clock_in.date_format' => '出勤時刻は「H:i」形式で入力してください',

            'clock_out.required' => '退勤時刻を入力してください',
            'clock_out.date_format' => '退勤時刻は「H:i」形式で入力してください',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',

            'breaks.*.break_start.date_format' => '休憩開始時刻は「H:i」形式で入力してください',
            'breaks.*.break_end.date_format' => '休憩終了時刻は「H:i」形式で入力してください',
            'breaks.*.break_end.after' => '休憩終了時刻は休憩開始時刻より後の時刻を入力してください',

            'note.required' => '備考を記入してください',
            'note.max' => '備考は255文字以内で入力してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = Carbon::createFromFormat('H:i', $this->input('clock_in'));
            $clockOut = Carbon::createFromFormat('H:i', $this->input('clock_out'));

            // 複数休憩の勤務時間外チェック
            if (is_array($this->breaks)) {
                foreach ($this->breaks as $index => $break) {
                    if (!empty($break['break_start']) && !empty($break['break_end'])) {
                        $start = Carbon::createFromFormat('H:i', $break['break_start']);
                        $end = Carbon::createFromFormat('H:i', $break['break_end']);

                        if ($start < $clockIn || $end > $clockOut) {
                            $validator->errors()->add("breaks.$index.break_start", '休憩時間が勤務時間外です');
                        }

                        if ($end < $clockIn) {
                            $validator->errors()->add("breaks.$index.break_end", '休憩終了時刻が出勤前になっています');
                        }
                    }
                }
            }
        });
    }
}
