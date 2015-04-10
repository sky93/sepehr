<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Lang;
use App\User;
use Hash;
use Config;
use DB;
use main;

class UserController extends Controller {
    /**
     * The registrar implementation.
     *
     * @var Registrar
     */
    protected $registrar;

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		//
	}

    public function login()
    {
        return view('auth.login', array('main' => new main()));
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request)
    {
        //Now unconfirmed users or banned users cannot login
        $credentials['active'] = 1;

        $credentials = $request->only('username', 'password');

        $ip = DB::table('ip_blacklist')
            ->where('ip', '=', $request->getClientIp())
            ->where('username', '=', $credentials['username'])
            ->orderBy('date', 'desc')
            ->get();

        if (count($ip) >= Config::get('leech.password_retry_count')){
            $diffrence_mins = Config::get('leech.ip_block_duration') - round(abs(time() - strtotime($ip[0]->date)) / 60);
            if($diffrence_mins > 0){
                return redirect($this->loginPath())
                    ->withInput($request->only('username', 'remember', 'password'))
                    ->withErrors([
                        'IP_Block' => Lang::get('errors.ip_block', ['min' => $diffrence_mins])
                    ]);
            }
            else{
                DB::table('ip_blacklist')
                    ->where('ip', '=', $request->getClientIp())
                    ->where('username', '=', $credentials['username'])
                    ->orderBy('date', 'desc')
                    ->delete();
            }
        }

        $this->validate($request, [
            'username' => 'required|min:5|max:16', 'password' => 'required'
        ]);

        $main = new main();
        if (!$main->trusted_ip($_SERVER['REMOTE_ADDR'])){
            $this->validate($request, [
                'g-recaptcha-response' => 'required|captcha'
            ]);
        }

        if (Auth::attempt($credentials, $request->has('remember')))
        {
            DB::table('ip_blacklist')
                ->where('ip', '=', $request->getClientIp())
                ->where('username', '=', $credentials['username'])
                ->orderBy('date', 'desc')
                ->delete();

            return Redirect::intended('/');
        }

        $main = new main();
        if (($main->ip_is_private($request->getClientIp()) && (Config::get('leech.ip_block_kind') == 'private' || Config::get('leech.ip_block_kind') == 'both')) || (!$main->ip_is_private($request->getClientIp()) && (Config::get('leech.ip_block_kind') == 'public' || Config::get('leech.ip_block_kind') == 'both'))) {
            DB::table('ip_blacklist')->insert(
                array(
                    'username' => $credentials['username'],
                    'password' => $credentials['password'],
                    'ip' => $request->getClientIp()
                )
            );
        }

        return redirect($this->loginPath())
            ->withInput($request->only('username', 'remember', 'password'))
            ->withErrors([
                'email' => Lang::get('messages.wrongPass'),
            ]);
    }


    /**
     * Get the path to the login route.
     *
     * @return string
     */
    public function loginPath()
    {
        return property_exists($this, 'loginPath') ? $this->loginPath : 'login';//todo check this.
    }

    /**
     * Shows register form
     *
     * @return string
     */
    public function register()
    {
        return view('auth.register');
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function postregister(Request $request)
    {
        $this->validate($request, [
            'first_name' => 'required|min:2|max:32',
            'last_name' => 'required|min:2|max:32',
            'username' => 'required|min:5|max:16|unique:users,username',
            'credit' => 'required|numeric',
            'password' => 'required|min:6|confirmed:password_confirmation',
            'email' => 'required|email|unique:users,email'
        ]);

        $user = new User;

        $user->first_name = $request['first_name'];
        $user->last_name = $request['last_name'];
        $user->username = $request['username'];
        $user->email = $request['email'];
        $user->password = Hash::make($request['password']);
        $user->credit = $request['credit'] * 1024 * 1024;

        $user->save();

        return redirect()->back()->with('message', $request['username']);
    }

    /**
     * Shows change password form
     *
     */
    public function password()
    {
        return view('auth.change_password');
    }

	/**
     * Changes user password
	 *
	 */
    public function post_password(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required|min:6',
            'new_password' => 'required|min:6|confirmed:new_password_confirmation',
        ]);

        if (!Hash::check($request['old_password'], Auth::user()->password)){
            return redirect()->back()
                ->withInput($request->only('username', 'remember', 'password'))
                ->withErrors([
                    'Password_not_match' => Lang::get('errors.wrong_pass')
                ]);
        }

        DB::table('users')
            ->where('id', Auth::user()->id)
            ->update([
                'password' => Hash::make($request['new_password'])
            ]);


        return redirect()->back()
            ->withInput($request->only('old_password', 'new_password', 'new_password_confirmation'))
            ->with('message', 'Your password has been changed!')
            ->header('refresh', '5;url=http://google.com');
    }

	/**
     * Display the specified resource.
	 *
     * @param  int $id
	 * @return Response
	 */
    public function show($id)
	{
		//
	}

	/**
     * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function edit($id)
	{
		//
	}

	/**
     * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
    public function update($id)
	{
		//
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function logout()
	{
        Auth::logout();
        return redirect('/');
	}

    /**
     * Get the failed login message.
     *
     * @return string
     */
    protected function getFailedLoginMesssage()
    {
        return '';
    }

}
