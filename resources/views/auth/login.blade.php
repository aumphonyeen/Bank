<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f2f6f3">
    <title>เข้าสู่ระบบพนักงาน | BankLite</title>
    <style>
        @font-face {
            font-family: "Thaisarabun";
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url("/assets/fonts/sarabun-thai-400.woff2") format("woff2");
            unicode-range: U+02D7, U+0303, U+0331, U+0E01-0E5B, U+200C-200D, U+25CC;
        }
        @font-face {
            font-family: "Thaisarabun";
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url("/assets/fonts/sarabun-latin-400.woff2") format("woff2");
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }
        @font-face {
            font-family: "Thaisarabun";
            font-style: normal;
            font-weight: 600;
            font-display: swap;
            src: url("/assets/fonts/sarabun-thai-600.woff2") format("woff2");
            unicode-range: U+02D7, U+0303, U+0331, U+0E01-0E5B, U+200C-200D, U+25CC;
        }
        @font-face {
            font-family: "Thaisarabun";
            font-style: normal;
            font-weight: 600;
            font-display: swap;
            src: url("/assets/fonts/sarabun-latin-600.woff2") format("woff2");
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
        }
        :root { color-scheme: light; font-family: "Thaisarabun", sans-serif; color: #19352b; background: #f2f6f3; font-synthesis: none; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; margin: 0; display: grid; place-items: center; padding: 24px; background: radial-gradient(ellipse at 15% 10%, #e0efe5 0, transparent 38%), #f2f6f3; }
        .layout { display: grid; grid-template-columns: minmax(300px, 1fr) minmax(360px, 470px); width: min(990px, 100%); min-height: 590px; overflow: hidden; border: 1px solid #e4ebe6; border-radius: 24px; background: #fff; box-shadow: 0 26px 80px #18372914; }
        .brand-panel { position: relative; display: flex; flex-direction: column; justify-content: space-between; overflow: hidden; padding: 38px; color: #fff; background: linear-gradient(145deg, #153f30, #1e6846 72%, #26845a); }
        .brand-panel:before, .brand-panel:after { position: absolute; content: ""; border: 1px solid #ffffff19; border-radius: 50%; }
        .brand-panel:before { width: 390px; height: 390px; right: -170px; bottom: -105px; }
        .brand-panel:after { width: 270px; height: 270px; right: -110px; bottom: -45px; }
        .brand, .brand-copy, .security-note { position: relative; z-index: 1; }
        .brand { display: flex; align-items: center; gap: 12px; font-size: 21px; font-weight: 600; }
        .brand-mark { width: 43px; height: 43px; display: grid; place-items: center; border-radius: 14px; background: #ffffff20; font-size: 26px; }
        .brand-copy { max-width: 370px; margin: auto 0; padding: 45px 0; }
        .eyebrow { margin: 0 0 13px; color: #b9dfc6; font-size: 14px; letter-spacing: .3px; }
        .brand-copy h1 { margin: 0 0 14px; font-size: clamp(29px, 4vw, 40px); line-height: 1.15; }
        .brand-copy p, .security-note { color: #d1e5d7; font-size: 15px; line-height: 1.65; }
        .security-note { margin: 0; padding-top: 20px; border-top: 1px solid #ffffff27; }
        .form-panel { display: flex; align-items: center; justify-content: center; padding: 38px; }
        .form-content { width: 100%; max-width: 350px; }
        .mobile-brand { display: none; }
        .form-content h2 { margin: 0 0 5px; font-size: 27px; }
        .intro { margin: 0 0 27px; color: #718178; font-size: 14px; }
        .field { display: grid; gap: 7px; margin-bottom: 16px; }
        label { font-size: 14px; font-weight: 600; }
        input { width: 100%; height: 46px; padding: 0 13px; border: 1px solid #dce6df; border-radius: 10px; background: #fff; color: #19352b; font: inherit; font-size: 15px; outline: none; }
        input:focus { border-color: #41976c; box-shadow: 0 0 0 3px #41976c20; }
        .password-wrap { position: relative; }
        .password-wrap input { padding-right: 70px; }
        .toggle-password { position: absolute; top: 50%; right: 10px; transform: translateY(-50%); border: 0; background: transparent; color: #52715e; font: inherit; font-size: 13px; cursor: pointer; }
        .error-box { margin: 0 0 17px; padding: 10px 12px; border: 1px solid #f0cccc; border-radius: 9px; background: #fff5f5; color: #a13939; font-size: 13px; line-height: 1.45; }
        .error-box ul { margin: 0; padding-left: 19px; }
        .submit { width: 100%; min-height: 47px; margin-top: 5px; border: 0; border-radius: 10px; background: #16734d; color: #fff; font: inherit; font-size: 16px; font-weight: 600; cursor: pointer; transition: background .15s, transform .15s; }
        .submit:hover { background: #10593b; }
        .submit:active { transform: translateY(1px); }
        .demo { margin-top: 21px; padding: 12px 13px; border: 1px solid #dcebe0; border-radius: 10px; background: #f5faf6; color: #55705e; font-size: 12px; line-height: 1.55; }
        .demo strong { color: #28573c; }
        .foot { margin: 23px 0 0; color: #84958a; font-size: 12px; text-align: center; }
        :focus-visible { outline: 3px solid #89c99e; outline-offset: 2px; }
        @media (max-width: 720px) {
            body { padding: 14px; }
            .layout { display: block; width: min(470px, 100%); min-height: 0; border-radius: 18px; }
            .brand-panel { display: none; }
            .form-panel { padding: 30px 25px; }
            .mobile-brand { display: flex; align-items: center; gap: 10px; margin-bottom: 27px; color: #153f30; font-size: 19px; font-weight: 600; }
            .mobile-brand .brand-mark { background: #e8f3ec; color: #16734d; }
            .form-content h2 { font-size: 25px; }
        }
        @media (prefers-reduced-motion: reduce) { *, *:before, *:after { transition-duration: .01ms !important; } }
    </style>
</head>
<body>
    <main class="layout">
        <section class="brand-panel" aria-label="เกี่ยวกับ BankLite">
            <div class="brand"><span class="brand-mark">฿</span><span>BankLite</span></div>
            <div class="brand-copy">
                <p class="eyebrow">STAFF ACCESS · BANKLITE</p>
                <h1>ระบบข้อมูลธนาคาร<br>สำหรับเจ้าหน้าที่</h1>
                <p>เข้าสู่ระบบเพื่อดูภาพรวมบัญชี รายชื่อลูกค้า และรายการธุรกรรมภายในโครงงาน</p>
            </div>
            <p class="security-note">🔒 พื้นที่สำหรับพนักงานเท่านั้น<br>ทุกหน้าข้อมูลต้องเข้าสู่ระบบก่อนใช้งาน</p>
        </section>
        <section class="form-panel">
            <div class="form-content">
                <div class="mobile-brand"><span class="brand-mark">฿</span><span>BankLite</span></div>
                <h2>เข้าสู่ระบบพนักงาน</h2>
                <p class="intro">กรอกรหัสพนักงานหรืออีเมลเพื่อดำเนินการต่อ</p>

                @if ($errors->any())
                    <div class="error-box" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <div class="field">
                        <label for="login">รหัสพนักงานหรืออีเมล</label>
                        <input id="login" name="login" type="text" value="{{ old('login') }}" autocomplete="username" required autofocus placeholder="เช่น EMP0001">
                    </div>
                    <div class="field">
                        <label for="password">รหัสผ่าน</label>
                        <div class="password-wrap">
                            <input id="password" name="password" type="password" autocomplete="current-password" required placeholder="กรอกรหัสผ่าน">
                            <button class="toggle-password" type="button" id="togglePassword" aria-label="แสดงรหัสผ่าน">แสดง</button>
                        </div>
                    </div>
                    <button class="submit" type="submit">เข้าสู่ระบบ</button>
                </form>

                <aside class="demo">
                    <strong>บัญชีทดลองสำหรับโครงงาน</strong><br>
                    รหัสพนักงาน: EMP0001 &nbsp;·&nbsp; รหัสผ่าน: BankLite@123
                </aside>
                <p class="foot">ระบบนี้จัดทำเพื่อการศึกษาเท่านั้น</p>
            </div>
        </section>
    </main>
    <script>
        const passwordInput = document.querySelector('#password');
        const toggleButton = document.querySelector('#togglePassword');
        toggleButton.addEventListener('click', () => {
            const shouldShow = passwordInput.type === 'password';
            passwordInput.type = shouldShow ? 'text' : 'password';
            toggleButton.textContent = shouldShow ? 'ซ่อน' : 'แสดง';
            toggleButton.setAttribute('aria-label', shouldShow ? 'ซ่อนรหัสผ่าน' : 'แสดงรหัสผ่าน');
        });
    </script>
</body>
</html>
