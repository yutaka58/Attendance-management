@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_attendance_staff.css') }}">
@endsection

@section('content')

<div class="list-form">
    <div class="list-form__grid">
        <div class="list-form__heading">
            <h2 class="list-form__title">{{ $user->name }}さんの勤怠</h2>
        </div>
        <div class="list-inner">
            <div class="list-calender">
                <a href="?month={{ $prevMonth }}" class="calender_btn">← 前月</a>
                <span class="current-month">
                    <input type="date" id="date-picker" value="{{ $currentMonth->format('Y-m-d') }}" onchange="location.href='?date=' + this.value">
                    <i class="fa-regular fa-calendar-days"></i>
                    <span style="margin-left: 10px;">{{ $currentMonth->format('Y/m') }}</span>
                </span>
                <a href="?month={{ $nextMonth }}" class="calender_btn">翌月 →</a>
            </div>
            <table class="list-form__table">
                <thead>
                    <tr class="list-form__row">
                        <th class="list-form__label">日付</th>
                        <th class="list-form__label">出勤</th>
                        <th class="list-form__label">退勤</th>
                        <th class="list-form__label">休憩</th>
                        <th class="list-form__label">合計</th>
                        <th class="list-form__label">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $id => $group)
                        @php
                            // 1. 出勤・退勤データを取得
                            $clockIn = $group->where('work_action_id', 1)->first(); // 出勤
                            $clockOut = $group->where('work_action_id', 2)->first(); // 退勤

                            // 2. 休憩時間の合計時間（分）
                            $totalRestMinutes = 0;
                            $restStarts = $group->where('work_action_id', 3); // 休憩入

                            foreach($restStarts as $start) {
                                // この休憩入に対応する「休憩戻（ID:4）」を、この日のデータから探す
                                $end = $group->where('work_action_id', 4)
                                    ->where('created_at', '>' , $start->created_at)
                                    ->first();

                                if ($end) {
                                    // Carbonの差分メソッドで「分」を加算
                                    $totalRestMinutes += $start->created_at->diffInMinutes($end->created_at);
                                }
                            }

                            // 「分」を「00:00」形式に変換
                            $hours = floor($totalRestMinutes / 60);
                            $minutes = $totalRestMinutes % 60;
                            $restTimeDisplay = sprintf('%02d:%02d', $hours, $minutes);

                            // 3. 勤務時間の計算
                            $workTimeDisplay = '--:--';
                            if ($clockIn && $clockOut) {
                                // 出勤から退勤までの時間を「分」で取得
                                $totalStayMinute = $clockIn->created_at->diffInMinutes($clockOut->created_at);
                                // 総時間 - 休憩時間 = 実労働時間（分）
                                $netWorkMinutes = $totalStayMinute - $totalRestMinutes;
                                // 0分以下にならないようマイナス値を防止
                                $netWorkMinutes = max(0, $netWorkMinutes);

                                // 「分」を「00:00」形式に変換
                                $workHours = floor($netWorkMinutes / 60);
                                $workMinutes = $netWorkMinutes % 60;
                                $workTimeDisplay = sprintf('%02d:%02d', $workHours, $workMinutes);
                            }
                        @endphp

                        <tr class="list-form__row">
                            {{-- 日付 --}}
                            <td class="list-form__data">{{ Carbon\Carbon::parse($id)->isoFormat('MM/DD(ddd)') }}</td>
                            {{-- 出勤 --}}
                            <td class="list-form__data">{{ $clockIn ? $clockIn->created_at->format('H:i'): null }}</td>
                            {{-- 退勤 --}}
                            <td class="list-form__data">{{ $clockOut ? $clockOut->created_at->format('H:i'): null }}</td>
                            {{-- 休憩合計時間 --}}
                            <td class="list-form__data">{{ $totalRestMinutes > 0 ? $restTimeDisplay : '' }}</td>
                            {{-- 勤務時間 --}}
                            <td class="list-form__data">{{ ($clockIn && $clockOut) ? $workTimeDisplay : '' }}</td>
                            <td class="list-form__data">
                                <a href="/admin/attendance/{{ $id }}?user_id={{ $user->id }}" class="detail-link">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="correction-btn">
                <button type="submit" name="correction" class="correction">CSV出力</button>
            </div>
        </div>
    </div>
</div>


@endsection
