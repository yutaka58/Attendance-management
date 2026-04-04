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
                    {{-- 1. ループの開始で $userId を受け取る --}}
                    @foreach($attendances as $userId => $group)
                        @php
                            // Attendanceモデルのメソッドを使ってデータを取得
                            $clockIn = $group->where('work_action_id', 1)->first();
                            $clockOut = $group->where('work_action_id', 2)->first();

                            $restMinutes = \App\Models\Attendance::getRestMinutes($group);
                            $workTime = \App\Models\Attendance::getWorkTimeDisplay($group);
                            $restDisplay = $restMinutes > 0 ? sprintf('%02d:%02d', floor($restMinutes / 60), $restMinutes % 60) : '';
                        @endphp

                        <tr class="list-form__row">
                            {{-- ...名前、出勤、退勤、休憩、合計の表示... --}}
                            <td class="list-form__data">{{ $clockIn->user->name ?? '不明' }}</td>
                            <td class="list-form__data">{{ $clockIn->start_time ?? '' }}</td>
                            <td class="list-form__data">{{ $clockIn->end_time ?? ($clockOut->end_time ?? '') }}</td>
                            <td class="list-form__data">{{ $restDisplay }}</td>
                            <td class="list-form__data">{{ $workTime }}</td>

                            {{-- 2. 詳細リンクで $userId を使用 --}}
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
