@extends('layouts.user')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')

<div class="list-form">
    <div class="list-form__grid">
        <div class="list-form__heading">
            <h2 class="list-form__title">勤怠詳細</h2>
        </div>
        <div class="list-inner">
            <table class="list-form__table">
                <tr class="list-form__row">
                    <th class="list-form__label">名前</th>
                    <td class="list-form__data">
                        <div class="list-form__data-container">
                            <span class="form-container" style="border: none;">西　玲奈</span>
                        </div>
                    </td>
                </tr>
                <tr class="list-form__row">
                    <th class="list-form__label">日付</th>
                    <td class="list-form__data">
                        <div class="list-form__data-container">
                            <span class="form-container" style="border: none;">2026年</span>
                            <span class="form-container" style="border: none;">03月15日</span>
                        </div>
                    </td>
                </tr>
                <tr class="list-form__row">
                    <th class="list-form__label">出勤・退勤</th>
                    <td class="list-form__data">
                        <div class="list-form__data-container">
                            <span class="form-container">出勤時間が入る</span>
                            <span class="separator">～</span>
                            <span class="form-container">退勤時間が入る</span>
                        </div>
                    </td>
                </tr>
                <tr class="list-form__row">
                    <th class="list-form__label">休憩</th>
                    <td class="list-form__data">
                        <div class="list-form__data-container">
                            <span class="form-container">休憩入</span>
                            <span class="separator">～</span>
                            <span class="form-container">休憩戻</span>
                        </div>
                    </td>
                </tr>
                <tr class="list-form__row">
                    <th class="list-form__label">休憩2</th>
                    <td class="list-form__data">
                        <div class="list-form__data-container">
                            <span class="form-container">休憩入2</span>
                            <span class="separator">～</span>
                            <span class="form-container">休憩戻2</span>
                        </div>
                    </td>
                </tr>
                <tr class="list-form__row">
                    <th class="list-form__label">備考</th>
                    <td class="list-form__data">
                        <div class="list-form__data-container">
                            <div class="list-form__data-wrapper">
                                <span class="remarks-column"></span>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
            <div class="correction-btn">
                <button class="correction">修正</button>
            </div>
        </div>
    </div>
</div>

@endsection
