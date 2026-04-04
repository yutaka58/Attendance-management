@extends('layouts.user')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')

<div class="list-form">
    <div class="list-form__grid">
        <div class="list-form__heading">
            <h2 class="list-form__title">勤怠一覧</h2>
        </div>
        <div class="list-inner">
            <div class="list-calender">
                <a href="?month={{ $prevMonth }}" class="calender-btn">← 前月</a>
                <span class="current-month">
                    <i class="fa-regular fa-calendar-days"></i>
                    <span style="margin-left:10px">{{ $currentMonth->format('Y/m') }}</span>
                    <input type="date" id="date-picker" value="{{ $currentMonth->format('Y-m-d') }}" onchange="location.href='?date=' + this.value">
                </span>
                <a href="?month={{ $nextMonth }}" class="calender-btn">翌月 →</a>
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
                            // Attendanceモデルのメソッドを使ってデータを取得
                            $clockIn = $group->where('work_action_id', 1)->first();
                            $clockOut = $group->where('work_action_id', 2)->first();

                            $restMinutes = \App\Models\Attendance::getRestMinutes($group);
                            $workTime = \App\Models\Attendance::getWorkTimeDisplay($group);
                            $restDisplay = $restMinutes > 0 ? sprintf('%02d:%02d', floor($restMinutes / 60), $restMinutes % 60) : '';
                        @endphp

                        <tr class="list-form__row">
                            {{-- 日付 --}}
                            <td class="list-form__data">{{ \Carbon\Carbon::parse($id)->isoFormat('MM/DD(ddd)') }}</td>

                            {{-- 出勤 --}}
                            <td class="list-form__data">
                                {{ $clockIn->start_time ?? ($clockIn ? $clockIn->created_at->format('H:i') : '') }}
                            </td>

                            {{-- 退勤 --}}
                            <td class="list-form__data">
                                {{ $clockIn->end_time ?? ($clockOut->end_time ?? ($clockOut ? $clockOut->created_at->format('H:i') : '')) }}
                            </td>

                            {{-- 休憩 --}}
                            <td class="list-form__data">{{ $restDisplay }}</td>

                            {{-- 合計 --}}
                            <td class="list-form__data">{{ $workTime }}</td>

                            <td class="list-form__data">
                                <a href="/attendance/detail/{{ $id }}" class="detail-link">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>


@endsection