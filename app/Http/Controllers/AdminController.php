<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Request;
use Lang;
use aria2;
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

    public function aria2console()
    {
        $main = new main();
        if (!$main->aria2_online())
            return view('errors.general', array('error_title' => 'ERROR 10002', 'error_message' => 'Aria2c is not running!'));

        $aria2 = new aria2();

        return view('tools.aria2console', array('main' => $main, 'aria2' => $aria2));
    }


    public function post_aria2console(Request $request)
    {
        $input = $request->only('function', 'param');

        if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
            $this->validate($request, [
                'function' => 'required'
            ]);
        }

        $functions = array('addUri', 'addTorrent', 'addMetalink', 'remove', 'forceRemove', 'pause', 'pauseAll', 'forcePause', 'forcePauseAll', 'unpause', 'unpauseAll', 'tellStatus', 'getUris', 'getFiles', 'getPeers', 'getServers', 'tellActive', 'tellWaiting', 'tellStopped', 'changePosition', 'changeUri', 'getOption', 'changeOption', 'getGlobalOption', 'changeGlobalOption', 'getGlobalStat', 'purgeDownloadResult', 'removeDownloadResult', 'getVersion', 'getSessionInfo', 'shutdown', 'forceShutdown', 'saveSession', 'multicall');

        if (!in_array($input['function'], $functions)) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                return response()->json(['Error' => 'The function does not exist in Aria2 functions.']);
            } else {
                return redirect::back()->withErrors('The function does not exist in Aria2 functions.');
            }
        }

        $main = new main();
        if (!$main->aria2_online())
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                return response()->json(['ERROR 10002' => 'Aria2c is not running!']);
            } else {
                return view('errors.general', array('error_title' => 'ERROR 10002', 'error_message' => 'Aria2c is not running!'));
            }


        $input['param'] = trim($input['param']);
        $params = $input['param'];
        $params = '[' . $params . ']';

        $aria2 = new aria2();

        $res = call_user_func_array(array($aria2, 'JSON_INPUT' . $input['function']), array($params));

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            return response()->json($res);
        }

        return redirect()->back()
            ->withInput()
            ->with('result', $res);
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
