@extends('layouts.user')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/stamp_correction_request.css') }}">
@endsection

@section('content')


<div class="list-form">
    <div class="list-form__grid">
        <div class="list-form__heading">
            <h2 class="list-form__title">申請一覧</h2>
        </div>
        <div class="list-inner">
            <div class="list-calender">
                <a href="" class="calender_btn">← 前月</a>
                <span class="current-month">
                    <i class="fa-solid fa-calendar-days"></i>
                </span>
                <a href="" class="calender_btn">翌月 →</a>
            </div>
            <table class="list-form__table">
                <thead>
                    <tr class="list-form__row">
                        <th class="list-form__label">状態</th>
                        <th class="list-form__label">名前</th>
                        <th class="list-form__label">対象日時</th>
                        <th class="list-form__label">申請理由</th>
                        <th class="list-form__label">申請日時</th>
                        <th class="list-form__label">詳細</th>
                    </tr>
                </thead>
                <tbody>
                        <tr class="list-form__row">
                            {{-- 日付 --}}
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
                                <a href="/attendance/detail" class="detail-link">詳細</a>
                            </td>
                        </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection
