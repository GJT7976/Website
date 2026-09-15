<?php
// Deliberately a standalone, fully self-contained document — no @vite(),
// no compiled CSS/JS, no dependency on a database connection beyond the
// one Setting::get() call below (which already has a hardcoded fallback).
// Laravel prerenders this once, as plain HTML, at the moment maintenance
// mode is turned on (Admin\MaintenanceController@store, or `php artisan
// down --render=errors.503`), and serves that frozen HTML on every
// request afterwards — including the earliest possible request handling,
// before the framework/Composer autoloader even finishes booting (see
// public/index.php). If this view depended on the Vite manifest the way
// the rest of the site does, a broken/missing build (exactly what took
// the site down once already — see HOSTINGER_DEPLOYMENT.md) would make
// it impossible to even show a maintenance page, let alone the real site.
$businessName = class_exists(\App\Models\Setting::class)
    ? \App\Models\Setting::get('business_name', 'Niagara Inde Apps')
    : 'Niagara Inde Apps';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>We'll be right back — <?php echo htmlspecialchars($businessName, ENT_QUOTES); ?></title>
    <style>
        @font-face {
            font-family: 'Lato';
            src: url('/fonts/lato/Lato-Regular.ttf') format('truetype');
            font-weight: 400;
            font-style: normal;
            font-display: swap;
        }
        @font-face {
            font-family: 'Lato';
            src: url('/fonts/lato/Lato-Bold.ttf') format('truetype');
            font-weight: 700;
            font-style: normal;
            font-display: swap;
        }

        :root {
            color-scheme: light;
            --offwhite: #faf9f6;
            --border: #e3e7e5;
            --navy: #16232b;
            --navy-soft: #3a4a52;
            --niagara-500: #2f7a5c;
            --niagara-600: #235f47;
            --niagara-50: #eef6f1;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            font-family: 'Lato', ui-sans-serif, system-ui, sans-serif;
            background-color: var(--offwhite);
            color: var(--navy);
        }

        .card {
            max-width: 30rem;
            width: 100%;
            background: #fff;
            border: 1px solid var(--border);
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(22, 35, 43, 0.08);
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .mark {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            border-radius: 0.75rem;
            background-color: var(--niagara-500);
            color: #fff;
            font-weight: 700;
            font-size: 1.25rem;
            margin-bottom: 1.25rem;
        }

        h1 {
            font-size: 1.375rem;
            font-weight: 700;
            margin: 0 0 0.75rem;
        }

        p {
            margin: 0 0 0.5rem;
            color: var(--navy-soft);
            line-height: 1.6;
        }

        .eyebrow {
            display: inline-block;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--niagara-600);
            background-color: var(--niagara-50);
            border-radius: 999px;
            padding: 0.25rem 0.75rem;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <span class="mark" aria-hidden="true">N</span>
        <div><span class="eyebrow">Quick maintenance</span></div>
        <h1>We'll be right back</h1>
        <p><?php echo htmlspecialchars($businessName, ENT_QUOTES); ?> is offline for a few minutes while we make some updates.</p>
        <p>No action needed on your end — just check back shortly.</p>
    </div>
</body>
</html>
