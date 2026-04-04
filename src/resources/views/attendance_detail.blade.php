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

        <form action="/attendance/detail/{id}" method="post">
            @csrf

            <input type="hidden" name="attendance_id" value="{{ $attendance->id ?? '' }}">
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
                                <input type="time" name="start_time" class="form-control {{ ($correction?->status == 0 || $isApproved) ? 'readonly-input' : '' }}" value="{{ old('start_time', $start_time) }}"{{ (isset($correction) && $correction->status == 0) ? 'readonly' : '' }}>
                                <span class="separator">～</span>
                                <input type="time" name="end_time" class="form-control {{ ($correction?->status == 0 || $isApproved) ? 'readonly-input' : '' }}" value="{{ old('end_time', $end_time) }}" {{ (isset($correction) && $correction->status == 0) ? 'readonly' : '' }}>
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
                        @if(!empty($rest['start']) || (!$correction && !$isApproved))
                            <tr class="list-form__row">
                                <th class="list-form__label">
                                    休憩{{ $index == 0 ? '': $index + 1 }}
                                </th>
                                <td class="list-form__data">
                                    <div class="list-form__data-container">
                                        <input type="time" name="rest_start[]" class="form-control {{ ($correction?->status == 0 || $isApproved) ? 'readonly-input' : '' }}" value="{{ old('rest_start.'.$index, $rest['start']) }}" {{ (isset($correction) && $correction->status == 0) ? 'readonly' : '' }}></input>
                                        <span class="separator">～</span>
                                        <input type="time" name="rest_end[]" class="form-control {{ ($correction?->status == 0 || $isApproved) ? 'readonly-input' : '' }}" value="{{ old('rest_end.'.$index, $rest['end']) }}" {{ (isset($correction) && $correction->status == 0) ? 'readonly' : '' }}></input>
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
                                {{-- 承認待ち(status:0) または 承認済み($isApproved) の場合は入力不可 --}}
                                @if(($correction && $correction->status == 0) || $isApproved)
                                    <span class="remarks-text" style="display: inline-block; padding: 20px 0; font-weight:bold;">
                                        {{ old('remarks_column', $correction->remarks ?? ($attendance->remarks ?? '')) }}
                                    </span>
                                    <input type="hidden" name="remarks_column" value="{{ old('remarks_column', $correction->remarks ?? '') }}">
                                @else
                                    {{-- 未申請の場合のみ入力枠を表示 --}}
                                    <input type="text" name="remarks_column" class="remarks-column"
                                        value="{{ old('remarks_column', $correction->remarks ?? ($attendance->remarks ?? '')) }}">
                                @endif
                            </div>
                        </td>
                    </tr>
                </table>
                <div class="correction-btn">
                    {{-- 申請が全くない場合のみ「修正」ボタンを出す --}}
                    @if(!$correction)
                        <button type="submit" name="correction" class="correction">修正</button>

                    {{-- 承認待ち(status:0) の場合 --}}
                    @elseif($correction->status == 0)
                        <p class="waiting-message" style="color: red; font-weight: bold;">
                            *承認待ちのため修正はできません。
                        </p>

                    {{-- 承認済み(status:1) の場合 --}}
                    @elseif($correction->status == 1)
                        <button type="button" class="correction approved" disabled style="background-color: #ccc; cursor: not-allowed; border: none;">
                            承認済み
                        </button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
