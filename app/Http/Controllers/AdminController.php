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

    public function users()
    {
        $main = new main();

        $users = DB::table('users')
            ->get();

        return view('tools.users', array('users' => $users, 'main' => $main));
    }


}
