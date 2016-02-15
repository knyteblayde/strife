<!DOCTYPE html>
<html lang="en">
<head>
    <title>Login &mdash; {{ APP_NAME }} </title>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="/css/core.css">
</head>
<body style="background: #f7f7f7">

<div class="container">
    <div class="col-md-3"></div>
    <div class="col-md-6">
        <br>
        <div class="fluent">
            {!Form::open(route('attempt'))!}
            <p class="heading">Login</p>
            <div class="form-group{{!empty(errors('username')) ? ' has-error':''}}">
                <label class="subheading">Username</label>
                <input type="text" class="form-control" name="username">
                <i class="error">{{errors('username')}}</i>
            </div>
            <div class="form-group{{!empty(errors('password')) ? ' has-error':''}}">
                <label class="subheading">Password</label>
                <input type="password" class="form-control" name="password">
                <i class="error">{{errors('password')}}</i>
            </div>
            <i class="error">{!Session::getFlash('flash')!}</i>
            <button type="submit" class="btn btn-submit"><i class="glyphicon glyphicon-lock"></i> Login</button>
            {!Form::close()!}
        </div>
        <br>
        <p class="text-center underline"><a href="/">&larr; home</a></p>
    </div>
    <div class="col-md-3"></div>
</div>

</body>
</html>