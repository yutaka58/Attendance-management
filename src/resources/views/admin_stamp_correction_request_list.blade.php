@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_stamp_correction_request_list.css') }}">
@endsection

@section('content')


<div class="list-form">
    <div class="list-form__grid">
        <div class="list-form__heading">
            <h2 class="list-form__title">申請一覧</h2>
        </div>
        <div class="list-inner">
            <div class="tab-items">
                <a href="/stamp_correction_request/list?page=pending" class="tab-item {{ $page == 'pending' ? 'active' : '' }}">承認待ち</a>
                <a href="/stamp_correction_request/list?page=approve" class="tab-item {{ $page == 'approve' ? 'active' : '' }}">承認済み</a>
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
                    @if($page == 'pending')
                        @foreach($pendingRequests as $request)
                            <tr class="list-form__row">
                                {{-- 状態 --}}
                                <td class="list-form__data">承認待ち</td>
                                {{-- 名前 --}}
                                <td class="list-form__data">{{ $request->user->name }}</td>
                                {{-- 対象日時 --}}
                                <td class="list-form__data">{{ $request->attendance?->created_at?->format('Y/m/d') ?? '' }}</td>
                                {{-- 申請理由 --}}
                                <td class="list-form__data">{{ $request->remarks ?? '' }}</td>
                                {{-- 申請日時 --}}
                                <td class="list-form__data">{{ $request->created_at->format('Y/m/d') }}</td>
                                <td class="list-form__data">
                                    <a href="/admin/stamp_correction_request/approve/{{ $request->id }}" class="detail-link">詳細</a>
                                </td>
                            </tr>
                        @endforeach

                    @else
                        @foreach($approveRequests as $request)
                            <tr class="list-form__row">
                                {{-- 状態 --}}
                                <td class="list-form__data">承認済み</td>
                                {{-- 名前 --}}
                                <td class="list-form__data">{{ $request->user->name }}</td>
                                {{-- 対象日時 --}}
                                <td class="list-form__data">{{ $request->attendance?->created_at?->format('Y/m/d') ?? '' }}</td>
                                {{-- 申請理由 --}}
                                <td class="list-form__data">{{ $request->remarks ?? '' }}</td>
                                {{-- 申請日時 --}}
                                <td class="list-form__data">{{ $request->created_at->format('Y/m/d') }}</td>
                                <td class="list-form__data">
                                    <a href="/admin/stamp_correction_request/approve/{{ $request->id }}" class="detail-link">詳細</a>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>



@endsection