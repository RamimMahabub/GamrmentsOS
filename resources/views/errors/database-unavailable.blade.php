<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Workspace unavailable | GarmentOS</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px; background: #efeff2; color: #202124; font: 16px/1.6 "Segoe UI", Arial, sans-serif; }
        main { width: 100%; max-width: 480px; padding: 32px; background: #fff; border: 1px solid #dedee5; border-radius: 16px; }
        .brand { margin: 0 0 24px; color: #526500; font-weight: 650; }
        h1 { margin: 0 0 16px; font-size: 28px; line-height: 1.25; }
        p { color: #686a74; }
        a { display: inline-block; margin-top: 12px; padding: 10px 18px; border-radius: 8px; background: #526500; color: #fff; text-decoration: none; font-weight: 600; }
        a:focus-visible { outline: 3px solid #526500; outline-offset: 4px; }
    </style>
</head>
<body>
    <main>
        <p class="brand">GarmentOS</p>
        <h1>Workspace temporarily unavailable</h1>
        <p>We couldn’t connect to the database. Please try again shortly.</p>
        <a href="{{ url()->current() }}">Try again</a>
    </main>
</body>
</html>
