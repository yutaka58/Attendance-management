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
                            <span class="form-container" style="border: none;">{{ auth()->user()->name }}</span>
                        </div>
                    </td>
                </tr>
                <tr class="list-form__row">
                    <th class="list-form__label">日付</th>
                    <td class="list-form__data">
                        <div class="list-form__data-container">
                            <span class="form-container" style="border: none;">{{ $year }}年</span>
                            <span class="form-container" style="border: none;">{{ $month }}月{{ $day }}日</span>
                        </div>
                    </td>
                </tr>
                <tr class="list-form__row">
                    <th class="list-form__label">出勤・退勤</th>
                    <td class="list-form__data">
                        <div class="list-form__data-container">
                            <input type="time" class="form-control" value="{{ $start_time }}"></input>
                            <span class="separator">～</span>
                            <input type="time" class="form-control" value="{{ $end_time }}"></input>
                        </div>
                    </td>
                </tr>
                @foreach($rests as $index => $rest)
                    <tr class="list-form__row">
                        <th class="list-form__label">
                            休憩{{ $index == 0 ? '': $index + 1 }}
                        </th>
                        <td class="list-form__data">
                            <div class="list-form__data-container">
                                <input type="time" name="rest_start[]" class="form-control" value="{{ $rest['start'] }}"></input>
                                <span class="separator">～</span>
                                <input type="time" name="rest_end[]" class="form-control" value="{{ $rest['end'] }}"></input>
                            </div>
                        </td>
                    </tr>
                @endforeach
                <tr class="list-form__row">
                    <th class="list-form__label">
                        @if(count($rests) == 0)
                            休憩
                        @else
                            休憩{{ count($rests) + 1 }}
                        @endif
                    </th>
                    <td class="list-form__data">
                        <div class="list-form__data-container">
                            <span class="form-container"></span>
                            <span class="separator">～</span>
                            <span class="form-container"></span>
                        </div>
                    </td>
                </tr>
                <tr class="list-form__row">
                    <th class="list-form__label">備考</th>
                    <td class="list-form__data">
                        <div class="list-form__data-container">
                            <div class="list-form__data-wrapper">
                                <input type="text" class="remarks-column"></input>
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
