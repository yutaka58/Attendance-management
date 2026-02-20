<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech_Attendance-management</title>

    <link rel="stylesheet" href="{{ asset('css/auth/register.css') }}">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">

</head>
<body>
    <div class="header">
        <div class="header-logo">
            <img src="{{ asset('storage/images/COACHTECHヘッダーロゴ.png') }}" alt="COACHTECHロゴ">
        </div>
    </div>

    <div class="header-title">
        <div class="header-title__grid">
            <h1 class="title-name">会員登録</h1>
        </div>
    </div>

    <form class="form" action="/register" method="get">
        @csrf
        <div class="form-content">
            <div class="form-group__title">
                <span class="form-label__title">名前</span>
            </div>
            <div class="form-content">
                <div class="form-input__text">
                    <input type="text" name="name" value="{{ old('name') }}">
                </div>
            </div>
        </div>
        <div class="form-content">
            <div class="form-group__title">
                <span class="form-label__title">メールアドレス</span>
            </div>
            <div class="form-content">
                <div class="form-input__text">
                    <input type="email" name="email" value="{{ old('email') }}">
                </div>
            </div>
        </div>
        <div class="form-content">
            <div class="form-group__title">
                <span class="form-label__title">パスワード</span>
            </div>
            <div class="form-content">
                <div class="form-input__text">
                    <input type="password" name="password">
                </div>
            </div>
        </div>
        <div class="form-content">
            <div class="form-group__title">
                <span class="form-label__title">パスワード確認 </span>
            </div>
            <div class="form-content">
                <div class="form-input__text">
                    <input type="password" name="pass-confirmation">
                </div>
            </div>
        </div>
    </form>
    
</body>
</html>