@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_attendance_detail.css') }}">
@endsection

@section('content')

<div class="list-form">
    <div class="list-form__grid">
        <div class="list-form__heading">
            <h2 class="list-form__title">勤怠詳細</h2>
        </div>

        <form action="/attendance/detail" method="get">
            @csrf

            <div class="list-inner">
                <table class="list-form__table">
                    <tr class="list-form__row">
                        <th class="list-form__label">名前</th>
                        <td class="list-form__data">
                            <div class="list-form__data-container">
                                <span class="form-container" style="border: none;">{{ auth()->user()->name }}</span>
                            </div>
                        </td>
                    </tr>
                    <tr class="list-form__row">
                        <th class="list-form__label">日付</th>
                        <td class="list-form__data">
                            <div class="list-form__data-container">
                                <span class="form-container" style="border: none;"></span>
                                <span class="form-container" style="border: none;"></span>
                            </div>
                        </td>
                    </tr>
                    <tr class="list-form__row">
                        <th class="list-form__label">出勤・退勤</th>
                        <td class="list-form__data">
                            <div class="list-form__data-container">
                                <input type="time" name="start_time" class="form-control" value="">
                                <span class="separator">～</span>
                                <input type="time" name="end_time" class="form-control" value="">
                            </div>
                        </td>
                    </tr>
                    <tr class="list-form__row">
                        <th class="list-form__label">休憩</th>
                        <td class="list-form__data">
                            <div class="list-form__data-container">
                                <input type="time" name="rest_start[]" class="form-control" value=""></input>
                                <span class="separator">～</span>
                                <input type="time" name="rest_end[]" class="form-control" value=""></input>
                            </div>
                        </td>
                    </tr>
                        <tr class="list-form__row">
                            <th class="list-form__label"></th>
                            <td class="list-form__data">
                                <div class="list-form__data-container">
                                    <input type="time" name="rest_start[]" class="form-control" value=""></input>
                                    <span class="separator">～</span>
                                    <input type="time" name="rest_end[]" class="form-control" value=""></input>
                                </div>
                            </td>
                        </tr>
                    <tr class="list-form__row">
                        <th class="list-form__label">備考</th>
                        <td class="list-form__data">
                            <div class="list-form__remarks-container">
                                <input type="text" name="remarks_column" class="remarks-column" value="">
                            </div>
                        </td>
                    </tr>

                    <div class="correction-btn">
                        <button type="submit" name="correction" class="correction">修正</button>
                    </div>
                </table>
            </div>
        </form>
    </div>
</div>

@endsection
