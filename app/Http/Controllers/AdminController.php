<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Lang;
use App\User;
use Hash;
use main;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    public function user_details($user_name)
    {
        $users = DB::table('users')
            ->where('username','=',$user_name)
            ->get();

        if ($users == null)
            return view('errors.general', array('error_title' => 'ERROR 404', 'error_message' => 'The user you are looking for might have been removed, had its name changed, or is temporarily unavailable.'));


        return $user_name;
    }

    public function users()
    {
        $main = new main();

        $users = DB::table('users')
            ->get();

        return view('tools.users', array('users' => $users, 'main' => $main));
    }


}
