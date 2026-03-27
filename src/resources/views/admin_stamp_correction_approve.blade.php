@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_stamp_correction_request_approve.css') }}">
@endsection

@section('content')

<div class="list-form">
    <div class="list-form__grid">
        <div class="list-form__heading">
            <h2 class="list-form__title">勤怠詳細</h2>
        </div>


        <input type="hidden" name="attendance_id" value="{{ $attendance->id ?? '' }}">
        <div class="list-inner">
            <table class="list-form__table">
                <tr class="list-form__row">
                    <th class="list-form__label">名前</th>
                    <td class="list-form__data">
                        <div class="list-form__data-container">
                            <span class="form-container" style="border: none;">{{ $correction->user->name }}</span>
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
                            <span class="form-control">{{ old('start_time', $start_time) }}</span>
                            <span class="separator">～</span>
                            <span class="form-control">{{ old('end_time', $end_time) }}</span>
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
                                <span class="form-control" >{{ old('rest_start.'.$index, $rest['start']) }}</span>
                                <span class="separator">～</span>
                                <span class="form-control">{{ old('rest_end.'.$index, $rest['end']) }}</span>
                            </div>
                        </td>
                    </tr>
                @endforeach
                <tr class="list-form__row">
                    <th class="list-form__label">備考</th>
                    <td class="list-form__data">
                        <div class="list-form__remarks-container">
                            <p class="remarks-column">{{ old('remarks_column', $correction->remarks ?? '') }}</p>
                        </div>
                    </td>
                </tr>
            </table>
            <div class="correction-btn">
                <form action="/stamp_correction_request/list" method="post">
                    @csrf
                    <button type="submit" name="correction" class="correction">承認</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
