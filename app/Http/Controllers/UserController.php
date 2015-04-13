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
use Validator;

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
        if (Auth::check())
        {
            return Redirect::to('/');
        }

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
        if (Auth::check())
        {
            return Redirect::to('/');
        }

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

            if (Auth::user()->role == 2){
                return Redirect::to('tools/status');
            }else{
                return Redirect::to('/');
            }
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
        $user->credit = $request['credit'] * 1024 * 1024 * 1024;

        $user->save();

        return redirect()->back()->with('message', $request['username']);
    }

    /**
     * Shows change password form
     *
     */
    public function password($username)
    {
        if (Auth::user()->username == $username || Auth::user()->role == 2)
            return view('auth.change_password');
        else
            return view('errors.general', array('error_title' => 'ERROR 401', 'error_message' => 'Access Denied'));

    }

	/**
     * Changes user password
	 *
	 */
    public function post_password(Request $request, $username)
    {
        if (!(Auth::user()->username == $username || Auth::user()->role == 2))
            return view('errors.general', array('error_title' => 'ERROR 401', 'error_message' => 'Access Denied'));

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
            ->with('message', 'Your password has been changed!');

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

    public function register_csv()
    {
        $main = new main();
        return view('auth.register_csv', array('main' => $main));
    }

    public function postregister_csv(Request $request)
    {
        ignore_user_abort(true);
        ini_set('max_execution_time', 0);
        if (ini_get('max_execution_time') != 0){
            return redirect::back()->withErrors('Could not change max_execution_time variable. Please review php.ini file.');
        };
        if ($request->hasFile('csv_file') && $request->file('csv_file')->isValid())
        {
            if (mb_strtolower($request->file('csv_file')->getClientOriginalExtension()) != 'csv'){
                return redirect::back()->withErrors('The uploaded file is not a valid CSV file.');
            }
            if ($request->file('csv_file')->getClientSize() > 1024 * 1024){
                return redirect::back()->withErrors('The uploaded file is bigger than 1MB.');
            }

            $path = $request->file('csv_file')->move(storage_path().'/csv_files/', date('d-m-Y-H-i', time()) . '-' . Auth::user()->username . '-' . rand(100,999) . '.csv');
            $row = 1;
            if (($handle = fopen("$path", "r")) !== FALSE) {
                $fails = 0;
                $success = 0;
                $conflicts = [];
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $num = count($data);
                    if($num != 4){
                        return redirect::back()->withErrors('CSV file should have 4 rows. First name, Last name, Password and Username');
                    }
//                    echo "<p> $num fields in line $row: <br /></p>\n";
                    $row++;
                    $input['username'] = $data[3];
                    $rules = array('username' => 'unique:users,username');
                    $validator = Validator::make($input, $rules);


                    if ($validator->fails()) {
                        $fails++;
                        $conflicts[] = "Couldn't add user " . $data[3];
                    }
                    else {
                        $success++;
                        $user = new User;

                        $user->first_name = trim($data[0]);
                        $user->last_name =  trim($data[1]);
                        $user->username = trim($data[3]);
                        $password = trim($data[2]);
                        if (empty($password)){
                            $password = $data[3];
                        }
                        $role = $request['role_radio'] == 2 ? 2 : 1;
                        $user->role = $role;

                        $active = $request['active'] == 'active' ? 1 : 0;
                        $user->active = $active;

                        $torrent = $request['torrent'] == 'torrent' ? 1 : 0;
                        $user->torrent = $torrent;

                        $public = $request['public'] == 'public' ? 1 : 0;
                        $user->public = $public;

                        $user->password = Hash::make($password);

                        $user->credit = $request['credit'] * 1024 * 1024 * 1024;

                        $user->save();

                        DB::table('credit_log')->insert(
                            array(
                                'user_id' => $user->id,
                                'credit_change' =>  $request['credit'] * 1024 * 1024 * 1024,
                                'agent' => 0,
                            )
                        );
                    }
                }
                fclose($handle);

            }else{
                return redirect::back()->withErrors('CSV is not valid.');
            }
            if ($success)
                return redirect()->back()
                    ->with('message', $success . ' users added successfully and ' . $fails . ' failed.')
                    ->withErrors($conflicts);
            else
                return redirect()->back()
                    ->withErrors($conflicts);

        }else{
            return redirect::back()->withErrors('CSV file did not upload to server. Try again.');
        }
    }

    public function user_info($username)
    {
        if (Auth::user()->username == $username || Auth::user()->role == 2) {
            $user = DB::table('users')
                ->where('username', '=', $username)
                ->first();
            return view('user.user_info', ['user' => $user]);
        }
        else
            return view('errors.general', array('error_title' => 'ERROR 401', 'error_message' => 'Access Denied'));
    }

    public function post_user_info(Request $request,$username){
        if (Auth::user()->username == $username || Auth::user()->role == 2) {
            $this->validate($request, [
                'email' => 'required|email|unique:users,email'
            ]);

            DB::table('users')
                ->where('id', Auth::user()->id)
                ->update([
                    'email' => $request['email']
                ]);

            if (isset($_GET['first']))
                return Redirect::to('/');
            else
                return redirect()->back()->with('message', Lang::get('messages.info_updates'));
        }
        else
            return view('errors.general', array('error_title' => 'ERROR 401', 'error_message' => 'Access Denied'));
    }



    public function credit_history($user_name)
    {
        if (Auth::user()->username == $user_name || Auth::user()->role == 2) {
            $user = User::where('username', '=', $user_name)->first();

            $tracks = DB::table('credit_log')
                ->select('credit_log.*', 'users.username')
                ->join('users', 'credit_log.agent', '=', 'users.id')
                ->where('user_id', '=', $user->id)
                ->get();

            $main = new main();
            return view('tools.user_credits', array('main' => $main, 'user' => $user, 'tracks' => $tracks));
        }
        else
            return view('errors.general', array('error_title' => 'ERROR 401', 'error_message' => 'Access Denied'));
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
