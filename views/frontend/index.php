<?php extend('layouts/frontend', ['title' => 'Homepage']) ?>

    <style type="text/css">
        body {
            background: #1C2126;
            text-align: center;
        }

        img {
            margin-top: 15%;
        }

        .quote {
            text-align: center;
            color: #ff2469;
            font-size: 20px;
            text-shadow: 0 2px 3px #000000
        }

        .quote small {
            font-size: 14px;
            color: #cccccc;
        }

        center {
            font-size: 11px;
            position: fixed;
            bottom: 20px;
            left: 40%;
            right: 40%;
            min-width: 100px;
        }
    </style>

    <img src="img/strife.png" width="200">
    <p class="quote">
        <?php
        $quotes = [
            '"Life is either you fall down and give up, or fall down and learn."',
            '"Highly intelligent people don\'t die, they just go offline."',
            '"Dreams don\'t come when we just dream of it, we must do."'
        ]
        ?>
        {{$quotes[rand(0,2)]}}
        <br>
        <small>[ Strife Framework v2 build 88 - October 23, 2016 ]</small>
    </p>

    <center>
        &copy; 2016 Strife Framework. All rights reserved.
    </center>

<?php endExtend() ?>