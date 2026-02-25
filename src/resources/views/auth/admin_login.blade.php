<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>coachtech_Attendance-management</title>

    <link rel="stylesheet" href="{{ asset('css/auth/admin_login.css') }}">
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
            <h1 class="title-name">管理者ログイン</h1>
        </div>
    </div>

    <form class="form" action="/admin/login" method="get">
        @csrf
        <div class="form-content">
            <div class="form-group__title">
                <span class="form-label__title">メールアドレス</span>
            </div>
            <div class="form-content">
                <div class="form-input__text">
                    <input type="email" name="email" value="{{ old('email') }}">
                </div>
                @error('email')
                    <div class="error-message" style="color: red;">{{ $message }}</div>
                @enderror
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
                @error('password')
                    <div class="error-message" style="color: red;">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="login-button">
            <button class="login-btn" type="submit">管理者ログインする</button>
        </div>
    </form>

</body>
</html>