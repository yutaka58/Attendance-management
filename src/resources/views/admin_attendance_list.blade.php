@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endsection

@section('content')


<div class="list-form">
    <div class="list-form__grid">
        <div class="list-form__heading">
            <h2 class="list-form__title">{{ $currentMonth->format('Y年n月j日') }}の勤怠</h2>
        </div>
        <div class="list-inner">
            <div class="list-calender">
                <a href="?month={{ $prevMonth }}" class="calender_btn">前月</a>
                <span class="current-month">
                    <i class="fa-solid fa-calendar-days"></i> {{ $currentMonth->format('Y/m/d') }}
                </span>
                <a href="?month={{ $nextMonth }}" class="calender_btn">翌月</a>
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
                        <tr class="list-form__row">
                            {{-- 名前 --}}
                            <td class="list-form__data"></td>
                            {{-- 出勤 --}}
                            <td class="list-form__data"></td>
                            {{-- 退勤 --}}
                            <td class="list-form__data"></td>
                            {{-- 休憩合計時間 --}}
                            <td class="list-form__data"></td>
                            {{-- 勤務時間 --}}
                            <td class="list-form__data"></td>
                            <td class="list-form__data">
                                <a href="admin/attendance/{id}" class="detail-link">詳細</a>
                            </td>
                        </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


@endsection
