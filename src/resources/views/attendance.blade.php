@extends('layouts.user')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="work-grid">
    <div class="work-content">
        <p class="work_status">{{ $work_status->name ?? '未設定' }}</p>
    </div>
    <div class="work-content">
        <p class="date">{{ $dt }}</p>
    </div>
    <div class="work-content">
        <p class="time">{{ $time }}</p>
    </div>
    <div class="work-content">
        <form action="/attendance" method="post">
            @csrf

            @foreach($display_actions as $action)
                <button class="work-btn" type="submit" name="action_id" value="{{ $action->id }}">{{ $action->name ?? '未設定' }}</button>
            @endforeach
        </form>
    </div>
</div>

@endsection
