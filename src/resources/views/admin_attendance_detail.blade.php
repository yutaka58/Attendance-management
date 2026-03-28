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

        <form action="/admin/attendance/{{ $currentDay->format('Y-m-d') }}?user_id={{ $userId }}" method="post">
            @csrf

            <input type="hidden" name="attendance_id" value="{{ $attendance->id ?? '' }}">
            <div class="list-inner">
                <table class="list-form__table">
                    <tr class="list-form__row">
                        <th class="list-form__label">名前</th>
                        <td class="list-form__data">
                            <div class="list-form__data-container">
                                <span class="form-container" style="border: none;">{{ $user->name ?? '不明' }}</span>
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
                                <input type="time" name="start_time" class="form-control {{ isset($correction) ? 'readonly-input' : '' }}" value="{{ old('start_time', $start_time) }}" {{ isset($correction) ? 'readonly' : '' }}>
                                <span class="separator">～</span>
                                <input type="time" name="end_time" class="form-control {{ isset($correction) ? 'readonly-input' : '' }}" value="{{ old('end_time', $end_time) }}" {{ isset($correction) ? 'readonly' : '' }}>
                                @error('start_time')
                                    <p class="error_message">{{ $message }}</p>
                                @enderror
                                @error('end_time')
                                    <p class="error_message">{{ $message }}</p>
                                @enderror
                            </div>
                        </td>
                    </tr>
                    @foreach($rests as $index => $rest)
                        {{-- 承認待ちの時は、値がある行のみ表示 --}}
                        @if(!isset($correction) || !empty($rest['start']))
                            <tr class="list-form__row">
                                <th class="list-form__label">
                                    休憩{{ $index == 0 ? '': $index + 1 }}
                                </th>
                                <td class="list-form__data">
                                    <div class="list-form__data-container">
                                        <input type="time" name="rest_start[]" class="form-control {{ isset($correction) ? 'readonly-input' : '' }}" value="{{ old('rest_start.'.$index, $rest['start']) }}" {{ isset($correction) ? 'readonly' : '' }}></input>
                                        <span class="separator">～</span>
                                        <input type="time" name="rest_end[]" class="form-control {{ isset($correction) ? 'readonly-input' : '' }}" value="{{ old('rest_end.'.$index, $rest['end']) }}" {{ isset($correction) ? 'readonly' : '' }}></input>
                                        @error('rest_start.'.$index)
                                            <p class="error_message">{{ $message }}</p>
                                        @enderror
                                        @error('rest_end.'.$index)
                                            <p class="error_message">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    <tr class="list-form__row">
                        <th class="list-form__label">備考</th>
                        <td class="list-form__data">
                            <div class="list-form__remarks-container">
                                <input type="text" name="remarks_column" class="remarks-column" value="{{ old('remarks_column', $correction->remarks ?? '') }}" {{ isset($correction) ? 'readonly' : '' }}>
                                @error('remarks_column')
                                    <p class="error_message">{{ $message }}</p>
                                @enderror
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="correction-btn">
                    @if(!isset($correction))
                        <button type="submit" name="correction" class="correction">修正</button>
                    @else
                        <p class="waiting-message" style="color: red; font-weight: bold;">
                            *承認待ちのため修正はできません。
                        </p>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>


@endsection
