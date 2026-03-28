@extends('layouts.admin')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}">
@endsection

@section('content')


<div class="list-form">
    <div class="list-form__grid">
        <div class="list-form__heading">
            <h2 class="list-form__title">スタッフ一覧</h2>
        </div>
        <div class="list-inner">
            <table class="list-form__table">
                <thead>
                    <tr class="list-form__row">
                        <th class="list-form__label">名前</th>
                        <th class="list-form__label">メールアドレス</th>
                        <th class="list-form__label">月次勤怠</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr class="list-form__row">
                            {{-- 名前 --}}
                            <td class="list-form__data">{{ $user->name }}</td>
                            {{-- メールアドレス --}}
                            <td class="list-form__data">{{ $user->email }}</td>
                            <td class="list-form__data">
                                <a href="/admin/attendance{{ $user->id }}" class="detail-link">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>


@endsection
