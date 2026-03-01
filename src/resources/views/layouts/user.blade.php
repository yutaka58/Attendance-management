<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech_Attendance-management</title>

    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user.css') }}">
    @yield('css')

</head>
<body>

<div class="header">
    <div class="header-logo">
        <img src="{{ asset('storage/images/COACHTECHヘッダーロゴ.png') }}" alt="COACHTECHロゴ">
    </div>
    <ul class="header-nav">
        <li class="header-nav__item">
            <a class="header-nav__link" href="/attendance">勤怠</a>
        </li>
        <li class="header-nav__item">
            <a class="header-nav__link" href="/attendance/list">勤怠一覧</a>
        </li>
        <li class="header-nav__item">
            <a class="header-nav__link" href="/stamp_correction_request/list">申請</a>
        </li>
        <li class="header-nav__item">
            <form action="/logout" method="post">
                @csrf
                <button class="header-nav__link-button" type="submit" >ログアウト</button>
            </form>
        </li>
    </ul>
</div>

<main>
    @yield('content')
</main>

</body>
</html>