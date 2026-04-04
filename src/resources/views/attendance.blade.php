@extends('layouts.user')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')

<div class="work-grid">
    <div class="work-content">
        <p class="work_status">{{ $work_status->name ?? '勤務外' }}</p>
    </div>
    <div class="work-content">
        <p class="date">{{ $dt }}</p>
    </div>
    <div class="work-content">
        <p class="time">{{ $time }}</p>
    </div>
    <div class="work-content">
        @if ($work_status && $work_status->name === '退勤済')
            <p class="leaving-work">お疲れ様でした。</p>
        @else
            <form action="/attendance" method="post">
                @csrf

                @foreach($display_actions as $action)
                    <button class="work-btn {{ $action->name === '休憩入' ? 'btn-rest' : '' }}" type="submit" name="action_id" value="{{ $action->id }}">{{ $action->name ?? '未設定' }}</button>
                @endforeach
            </form>
        @endif
    </div>
</div>

<script>
    document.querySelector('form').addEventListener('submit', function(e) {
    const statusText = document.querySelector('.work_status');
    const clickedButton = e.submitter;

    if (clickedButton && clickedButton.innerText.includes('出勤')) {
        statusText.innerText = '出勤中...';
    }
});
</script>

@endsection
