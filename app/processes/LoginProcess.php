<?php

use Kernel\Hash;
use Kernel\Token;
use App\Models\User;
use App\Requests\LoginRequest;

class LoginProcess
{
    /**
     * Execute the Process
     *
     * @todo execute
     * @param $callback
     * @return mixed
     */
    public function execute($callback = "")
    {
        $request = new LoginRequest($_POST);

        if ($request->validate()) {
            $attempt = User::where('username', $request->get('username'))
                ->where('password', Hash::encode($request->get('password')))
                ->where('active', 'yes');

            if ($attempt->exists()) {
                $user = $attempt->first();
                $user->remember_token = Token::create();
                $user->save();
                $_SESSION['user'] = $user();
                return Route::redirect('welcome');
            } else {
                Session::setFlash('flash', 'username/password is incorrect.<br><br>');
                return $callback();
            }
        } else {
            return $callback();
        }
    }
}