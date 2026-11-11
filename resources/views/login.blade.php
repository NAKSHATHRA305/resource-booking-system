<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin login</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Zen+Kaku+Gothic+New:wght@400;700;900&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box}
body{margin:0;min-height:100vh;display:grid;place-items:center;background:#F5F6F8;color:#171A21;font-family:"Zen Kaku Gothic New","Hiragino Sans","Yu Gothic",system-ui,Arial,sans-serif}
.box{width:360px;max-width:92vw;background:#fff;border-top:1px solid #171A21;padding:32px 28px}
h1{margin:0 0 6px;font-size:28px;font-weight:900;letter-spacing:-.02em}
p{margin:0 0 20px;color:#5E6573;font-size:14px}
label{display:block;font-size:13px;font-weight:700;margin-bottom:6px}
input{width:100%;padding:11px 12px;border:1px solid #CDD2DB;border-radius:3px;font-size:15px;font-family:inherit}
input:focus{outline:2px solid #2B3FA6;border-color:#2B3FA6}
button{margin-top:16px;width:100%;background:#2B3FA6;color:#fff;border:0;border-radius:3px;padding:11px;font-size:14px;font-weight:700;cursor:pointer;font-family:inherit}
button:hover{background:#1D2C7A}
.err{color:#B3261E;font-weight:700;font-size:14px;margin-top:10px}
a{display:inline-block;margin-top:18px;color:#2B3FA6;font-size:14px}
</style>
</head>
<body>
  <form class="box" method="post" action="/login">
    @csrf
    <h1>Admin login</h1>
    <p>Booking, cancelling and stopping sales need the admin password. Viewing is open to everyone.</p>
    <label for="pw">Password</label>
    <input id="pw" type="password" name="password" autofocus required>
    @error('password')<div class="err">{{ $message }}</div>@enderror
    <button type="submit">Log in</button>
    <a href="/">Back to the chart</a>
  </form>
</body>
</html>