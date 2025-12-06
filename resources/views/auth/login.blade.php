<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Informatics Ubaya</title>

    <link href="https://fonts.googleapis.com/css2?family=Ovo&display=swap" rel="stylesheet">

    <style>
        /* ... (Semua style CSS Anda yang lain tetap sama) ... */
        * {
            font-family: 'Ovo', serif;
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body, html {
            height: 100%;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .left {
            flex: 6;
            background-color: #e0e0e0;
            background-image: url('{{ asset('images/batik-if.jpg') }}');
            background-size: cover;
            background-position: center;
        }

        .right {
            flex: 4;
            background-color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 60px;
            overflow-y: auto;
        }

        .login-box {
            width: 100%;
            max-width: 480px;
        }

        .logo {
            display: block;
            margin: 0 auto 40px auto;
            width: 320px;
        }

        h2 {
            font-size: 56px;
            margin-bottom: 16px;
            text-align: left;
        }

        p.subtitle {
            font-size: 24px;
            color: #333;
            margin-bottom: 40px;
        }

        label {
            display: block;
            font-size: 26px;
            margin-top: 26px;
            color: #555;
        }

        input {
            width: 100%;
            padding: 16px 10px;
            margin-top: 10px;
            border: none;
            border-bottom: 2px solid #aaa;
            font-size: 26px;
            outline: none;
        }

        input:focus {
            border-bottom: 3px solid #0a2e6c;
        }

        .btn-login {
            width: 100%;
            background-color: #0a2e6c;
            color: white;
            font-weight: bold;
            font-size: 26px;
            border: none;
            border-radius: 10px;
            padding: 20px;
            margin-top: 40px;
            cursor: pointer;
        }

        .btn-login:hover {
            background-color: #082456;
        }

        .error-box {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            background-color: #ffebeB;
            border: 1px solid #ffcccc;
            color: #a00;
            font-size: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="left"></div>

        <div class="right">
            <div class="login-box">
                <img src="{{ asset('images/logo-if.png') }}" alt="Logo IF Ubaya" class="logo">
                <h2>Login</h2>
                <p class="subtitle">Gunakan Akun UBAYA Anda</p>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                @if ($errors->any())
                    <div class="error-box">
                        {{ $errors->first('login_id') ?: $errors->first() }}
                    </div>
                @endif
                
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <label for="login_id"></label>
                    <input type="text" 
                           id="login_id" 
                           name="login_id" 
                           value="{{ old('login_id') }}" 
                           placeholder="NRP / NPK / Username"
                           required 
                           autofocus>

                    <label for="password"></label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           placeholder="Password Anda"
                           required>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; font-size: 20px;">
                        
                        <div style="display: flex; align-items: center;">
                            <input type="checkbox" name="remember" id="remember" style="width: auto; margin-right: 10px;">
                            <label for="remember" style="display: inline; font-size: 20px; margin-top: 0;">Remember Me</label>
                        </div>
                        <div>
                            <a href="{{ route('password.request') }}" style="color: #0a2e6c; text-decoration: none;">
                                Lupa Password?
                            </a>
                        </div>
                    </div>

                    <button type="submit" class="btn-login">LOGIN</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>