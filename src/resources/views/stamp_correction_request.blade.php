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
            <div class="tab-items">
                <a href="/stamp_correction_request/list?pendingRequests" class="tab-item">承認待ち</a>
                <a href="/stamp_correction_request/list?approveRequests" class="tab-item">承認済み</a>
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
                            {{-- 状態 --}}
                            <td class="list-form__data">{{ $status }}</td>
                            {{-- 名前 --}}
                            <td class="list-form__data"></td>
                            {{-- 対象日時 --}}
                            <td class="list-form__data"></td>
                            {{-- 申請理由 --}}
                            <td class="list-form__data"></td>
                            {{-- 申請日時 --}}
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
