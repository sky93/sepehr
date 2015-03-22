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

    public function stat(){
        $main = new main();
        if (!$main->aria2_online()) return view('errors.general', array('error_title' => 'ERROR 10002', 'error_message' => 'Aria2c is not running!'));

        return view('tools.stats', array('main' => $main));

    }

    public function postuser_details($username){
        if (!empty($username) || !isset($_POST['action'])) {
            if ($_POST['action'] == 'delete') {
                $atu = Auth::user()->username;
                $user = User::where('username', '=', $username);
                $user->delete();
                if ($atu == $username) {
                    Auth::logout();
                }
                return Redirect::to('/tools/users');
            } elseif ($_POST['action'] == 'ban') {
                $user = User::where('username', '=', $username)->first();
                $active = $user->active;
                if ($active != 0){
                    $user->active = 0;
                }else{
                    $user->active = 1;
                }
                $user->save();

                if ( Auth::user()->username == $username) {
                    Auth::logout();
                }
            }
        }
        return Redirect::to('/tools/users/' . $username);

    }

    public function user_details($user_name)
    {

        $users = User::where('username', '=', $user_name)->first();

        if ($users == null)
            return view('errors.general', array('error_title' => 'ERROR 404', 'error_message' => 'The user you are looking for might have been removed, had its name changed, or is temporarily unavailable.'));

        $main = new main();

        $user_detailed = DB::table('download_list')
            ->select(DB::raw('
            SUM(completed_length) as completed_length_sum,
            SUM(length) as length_sum,
            COUNT(*) as total_files_deleted,
            (select COUNT(*) from download_list where deleted=0 AND user_id = ' . $users->id . ') as total_files,
            (select COUNT(*) from download_list where state > 0 AND deleted=0 AND user_id = ' . $users->id . ') as total_error_files,
            (select COUNT(*) from download_list where state > 0 AND user_id = ' . $users->id . ') as total_error_files_deleted,
            (select COUNT(*) from download_list where state is NULL AND user_id = ' . $users->id . ') as total_download_queue

            '))
            ->where('user_id', '=', $users->id)
            ->first();

        $user_files = DB::table('download_list')
            ->where('user_id' ,'=', $users->id)
            ->get();

        return view('tools.userdetails', array('user_files' => $user_files, 'user' => $users, 'userd' => $user_detailed, 'main' => $main));
    }

    public function users()
    {
        $main = new main();

        $users = DB::table('users')
            ->get();

        return view('tools.users', array('users' => $users, 'main' => $main));
    }


}
