@extends('layouts.user')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')

<div class="list-form">
    <div class="list-form__grid">
        <div class="list-form__heading">
            <h2 class="list-form__title">勤怠一覧</h2>
        </div>
        <div class="list-inner">
            <table class="list-form__table">
                <tr class="list-form__row">
                    <th class="list-form__label">名前</th>
                    <td class="list-form__data"></td>
                </tr>
                <tr class="list-form__row">
                    <th class="list-form__label">日付</th>
                    <td class="list-form__data"></td>
                </tr>
                <tr class="list-form__row">
                    <th class="list-form__label">出勤・退勤</th>
                    <td class="list-form__data"></td>
                </tr>
                <tr class="list-form__row">
                    <th class="list-form__label">休憩2</th>
                    <td class="list-form__data"></td>
                </tr>
                <tr class="list-form__row">
                    <th class="list-form__label">備考</th>
                    <td class="list-form__data"></td>
                </tr>
            </table>
        </div>
    </div>
</div>

@endsection
