@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endsection

@section('content')


<div class="list-form">
    <div class="list-form__grid">
        <div class="list-form__heading">
            <h2 class="list-form__title">{{ $currentDay->format('Y年n月j日') }}の勤怠</h2>
        </div>
        <div class="list-inner">
            <div class="list-calender">
                <a href="?date={{ $prevDay }}" class="calender-btn">← 前日</a>
                <span class="current-day">
                    <i class="fa-regular fa-calendar-days"></i>
                    <span style="margin-left:10px">{{ $currentDay->format('Y/m/d') }}</span>
                    <input type="date" id="date-picker" value="{{ $currentDay->format('Y-m-d') }}" onchange="location.href='?date=' + this.value">
                </span>
                <a href="?date={{ $nextDay }}" class="calender-btn">翌日 →</a>
            </div>
            <table class="list-form__table">
                <thead>
                    <tr class="list-form__row">
                        <th class="list-form__label">名前</th>
                        <th class="list-form__label">出勤</th>
                        <th class="list-form__label">退勤</th>
                        <th class="list-form__label">休憩</th>
                        <th class="list-form__label">合計</th>
                        <th class="list-form__label">詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $userId => $group) {{-- $group はそのユーザーのその日の打刻記録 --}}
                        @php
                            // 1. 基本データの取得
                            $firstAttendance = $group->first(); // 最初のレコード（名前取得用）
                            $clockIn = $group->where('work_action_id', 1)->first(); // 出勤
                            $clockOut = $group->where('work_action_id', 2)->first(); // 退勤

                            // 2. 休憩時間の計算
                            $totalRestMinutes = 0;
                            $restStarts = $group->where('work_action_id', 3); // 休憩入

                            foreach($restStarts as $start) {
                                $end = $group->where('work_action_id', 4)
                                             ->where('created_at', '>', $start->created_at)
                                             ->first();
                                if ($end) {
                                    $totalRestMinutes += $start->created_at->diffInMinutes($end->created_at);
                                }
                            }

                            // 表示用フォーマット
                            $restTimeDisplay = sprintf('%02d:%02d', floor($totalRestMinutes / 60), $totalRestMinutes % 60);

                            // 3. 勤務時間の計算
                            $workTimeDisplay = '--:--';
                            if ($clockIn && $clockOut) {
                                $totalStayMinute = $clockIn->created_at->diffInMinutes($clockOut->created_at);
                                $netWorkMinutes = max(0, $totalStayMinute - $totalRestMinutes);
                                $workTimeDisplay = sprintf('%02d:%02d', floor($netWorkMinutes / 60), $netWorkMinutes % 60);
                            }
                        @endphp
                        <tr class="list-form__row">
                            {{-- 名前 --}}
                            <td class="list-form__data">{{ $firstAttendance->user->name ?? '不明' }}</td>
                            {{-- 出勤 --}}
                            <td class="list-form__data">{{ $clockIn ? $clockIn->created_at->format('H:i'): null }}</td>
                            {{-- 退勤 --}}
                            <td class="list-form__data">{{ $clockOut ? $clockOut->created_at->format('H:i'): null }}</td>
                            {{-- 休憩合計時間 --}}
                            <td class="list-form__data">{{ $totalRestMinutes > 0 ? $restTimeDisplay : '' }}</td>
                            {{-- 勤務時間 --}}
                            <td class="list-form__data">{{ ($clockIn && $clockOut) ? $workTimeDisplay : '' }}</td>
                            <td class="list-form__data">
                                <a href="/admin/attendance/{{ $currentDay->format('Y-m-d') }}?user_id={{ $userId }}" class="detail-link">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
