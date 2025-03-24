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
     * バリデーションルール定義
     */
    public function rules()
    {
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i', 'after:clock_in'],
            'break_start' => ['nullable', 'date_format:H:i'],
            'break_end' => ['nullable', 'date_format:H:i', 'after:break_start'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * カスタムエラーメッセージ
     */
    public function messages()
    {
        return [
            'clock_in.required' => '出勤時刻を入力してください。',
            'clock_in.date_format' => '出勤時刻の形式が正しくありません（例：09:00）。',
            
            'clock_out.required' => '退勤時刻を入力してください。',
            'clock_out.date_format' => '退勤時刻の形式が正しくありません（例：18:00）。',
            'clock_out.after' => '退勤時刻は出勤時刻より後の時刻を入力してください。',

            'break_start.date_format' => '休憩開始時刻の形式が正しくありません（例：12:00）。',
            'break_end.date_format' => '休憩終了時刻の形式が正しくありません（例：13:00）。',
            'break_end.after' => '休憩終了時刻は休憩開始時刻より後の時刻を入力してください。',

            'note.string' => '備考は文字列で入力してください。',
            'note.max' => '備考は255文字以内で入力してください。',
        ];
    }
}
