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
            <p class="list-calender">カレンダーが入る</p>
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
                    {{-- ここに実際のデータをループで表示させます --}}
                    {{-- @foreach($attendances as $attendance) --}}
                    <tr class="list-form__row">
                        <td class="list-form__data">2026/03/08(日)</td>
                        <td class="list-form__data">09:00</td>
                        <td class="list-form__data">18:00</td>
                        <td class="list-form__data">01:00</td>
                        <td class="list-form__data">08:00</td>
                        <td class="list-form__data">
                            <a href="#" class="detail-link">詳細</a>
                        </td>
                    </tr>
                    {{-- @endforeach --}}
                </tbody>
            </table>
        </div>
    </div>
</div>


@endsection