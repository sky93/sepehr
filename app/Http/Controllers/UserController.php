<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Lang;
use App\User;
use Hash;
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
        $this->validate($request, [
            'username' => 'required|min:5|max:16', 'password' => 'required'
        ]);

        $main = new main();
        if (!$main->trusted_ip($_SERVER['REMOTE_ADDR'])){
            $this->validate($request, [
                'g-recaptcha-response' => 'required|captcha'
            ]);
        }

        $credentials = $request->only('username', 'password');

        //Now unconfirmed users or banned users cannot login
        $credentials['active'] = 1;

        if (Auth::attempt($credentials, $request->has('remember')))
        {
            return Redirect::intended('/');
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
     * Get the path to the login route.
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
            'name' => 'required|min:2|max:32',
            'username' => 'required|min:5|max:16|unique:users,username',
            'credit' => 'required|numeric',
            'password' => 'required|min:6|confirmed:password_confirmation',
            'email' => 'required|email|unique:users,email'
        ]);

        $user = new User;

        $user->name = $request['name'];
        $user->username = $request['username'];
        $user->email = $request['email'];
        $user->password = Hash::make($request['password']);
        $user->credit = $request['credit'] * 1024 * 1024;


        $user->save();

        return redirect()->back()->with('message', $request['username']);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

	/**
     * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
    public function store()
	{
		//
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
