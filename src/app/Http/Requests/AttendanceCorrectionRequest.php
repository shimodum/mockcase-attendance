<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /**
     * 通常のバリデーションルール定義
     */
    public function rules()
    {
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i', 'after:clock_in'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end' => ['nullable', 'date_format:H:i', 'after:break_start'],
            'note' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * エラーメッセージ定義
     */
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
            'break_end.after' => '休憩終了時刻は休憩開始時刻より後の時刻を入力してください',

            'note.required' => '備考を記入してください',
            'note.max' => '備考は255文字以内で入力してください',
        ];
    }

    /**
     * カスタムバリデーション（勤務時間外の休憩を防ぐ）
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');
            $breakStart = $this->input('break_start');
            $breakEnd = $this->input('break_end');

            // 全ての時間が揃っている場合のみチェック
            if ($clockIn && $clockOut) {
                if ($breakStart && $breakStart < $clockIn) {
                    $validator->errors()->add('break_start', '休憩時間が勤務時間外です');
                }
                if ($breakEnd && $breakEnd > $clockOut) {
                    $validator->errors()->add('break_end', '休憩時間が勤務時間外です');
                }
            }
        });
    }
}
