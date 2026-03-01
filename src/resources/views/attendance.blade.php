@extends('layouts.user')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/user.css') }}">
@endsection

@section('content')

<main>

    <div>
        <div>
            <p>勤務中のステータスを表示させるモデルを作成する</p>
        </div>
    </div>
    <div>
        <div>
            <p>日付を表示させる</p>
        </div>
    </div>
    <div>
        <div>
            <p>時間を表示させる</p>
        </div>
    </div>
    <div class="">
        <div class="">
            <button class="work-btn" action="submit">出勤</button>
        </div>
    </div>

</main>

@endsection
