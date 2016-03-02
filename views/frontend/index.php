<?php extend('layouts/frontend',['title' => 'Homepage']) ?>

<style type="text/css">
body{
  background: #1C2126;
  text-align: center;
}
img{
	margin-top: 15%;
}
.quote{
	text-align: center;
	color: #eecc99;
	font-size: 16px;
	text-shadow: 0 2px 3px #000000
}
.quote i{
	font-size: 14px;
}

center{
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
	"Highly intelligent people don't die, they just go offline." <br>
	<i>-Anonymous</i>
</p>

<center>
	&copy; 2016 Strife Framework. All rights reserved.
</center>

<?php endExtend() ?>