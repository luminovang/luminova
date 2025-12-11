<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="robots" content="noindex"/>
    <meta http-equiv="Cache-Control" content="no-store, max-age=0, no-cache"/>
    <meta http-equiv="refresh" content="3600"/>
    <link rel="shortcut icon" type="image/png" href="./favicon.png">
    <title>System on Maintenance</title>
    <style>
        <?= preg_replace('#[\r\n\t ]+#', ' ', file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'maintenance.css')) ?>
    </style>
</head>
<body>
<article>
    <h1>Maintenance Mode</h1>
    <div class="container">
        <p>Maintenance is currently ongoing. We apologize for any inconvenience.</p>
        <p><?= date('l, F jS, Y - g:i A'); ?></p>
        <p>Current Timezone: <?= date_default_timezone_get(); ?></p>
    </div>
    <div class="timer">
        <p class="day"></p>
        <p class="hour"></p>
        <p class="minute"></p>
        <p class="second"></p>
    </div>
</article>

<script>
    const stringDate = new Date(Date.now() + 60 * 1000).toLocaleString();
    const countDay = new Date(stringDate);
    const startCountdown = () => {
            const now = new Date();
            const counter = countDay - now;
            const second = 1000;
            const minute = second * 60;
            const hour = minute * 60;
            const day = hour * 24;
            const textDay = Math.floor(counter / day);
            const textHour = Math.floor((counter % day) / hour);
            const textMinute = Math.floor((counter % hour) / minute);
            const textSecond = Math.floor((counter % minute) / second);

            if (textSecond < 0) {
              theDay = 0;
              theHour = 0;
              theMinute = 0;
              theSecond = 0;

              window.location.reload();
            } else {
              theDay = textDay;
              theHour = textHour;
              theMinute = textMinute;
              theSecond = textSecond;
            }
            document.querySelector(".day").innerText = theDay + ' Days';
            document.querySelector(".hour").innerText = theHour + ' Hours';
            document.querySelector(".minute").innerText = theMinute + ' Minutes';
            document.querySelector(".second").innerText = theSecond + ' Seconds';
        }
        startCountdown();
        setInterval(startCountdown, 1000);
</script>
</body>
</html>