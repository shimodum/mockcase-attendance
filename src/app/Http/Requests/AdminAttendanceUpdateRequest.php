<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'date' => ['required', 'date'],
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
            'date.required' => '日付は必須です',
            'date.date' => '日付の形式が正しくありません',
            'clock_in.required' => '出勤時刻は必須です',
            'clock_in.date_format' => '出勤時刻は「HH:MM」形式で入力してください',
            'clock_out.required' => '退勤時刻は必須です',
            'clock_out.date_format' => '退勤時刻は「HH:MM」形式で入力してください',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.break_start.date_format' => '休憩開始時刻は「HH:MM」形式で入力してください',
            'breaks.*.break_end.date_format' => '休憩終了時刻は「HH:MM」形式で入力してください',
            'breaks.*.break_end.after' => '休憩終了は開始より後の時刻を指定してください',
            'note.required' => '備考を記入してください',
            'note.max' => '備考は255文字以内で入力してください',
        ];
    }

    public function withValidator($validator) //休憩時間が勤務時間外でないこと のバリデーション
    {
        $validator->after(function ($validator) {
            $clockIn = Carbon::parse("{$this->date} {$this->clock_in}");
            $clockOut = Carbon::parse("{$this->date} {$this->clock_out}");

            if (is_array($this->breaks)) {
                foreach ($this->breaks as $index => $break) {
                    if (!empty($break['break_start']) && !empty($break['break_end'])) {
                        $breakStart = Carbon::parse("{$this->date} {$break['break_start']}");
                        $breakEnd = Carbon::parse("{$this->date} {$break['break_end']}");

                        if ($breakStart < $clockIn || $breakEnd > $clockOut) {
                            $validator->errors()->add("breaks.{$index}.break_start", '休憩時間が勤務時間外です');
                        }
                    }
                }
            }
        });
    }
}
