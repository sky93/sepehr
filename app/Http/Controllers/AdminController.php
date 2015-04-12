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
use Validator;
use main;
use Illuminate\Support\Facades\Config;

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

    public function stat()
    {
        $main = new main();
        if (!$main->aria2_online()) return view('errors.general', array('error_title' => 'ERROR 10002', 'error_message' => 'Aria2c is not running!'));

        $aria2 = new aria2();
        return view('tools.stats', array('main' => $main, 'aria2' => $aria2));
    }




    public function post_stat(Request $request)
    {
        if ($request->ajax()) {
            if (isset($request['gs'])) {
                $main = new main();
                if (!$main->aria2_online())
                    return response()->json(['ERROR 10002' => 'Aria2 is not running!']);

                $aria2 = new aria2();

                return response()->json([
                    'speed' => $main->formatBytes($aria2->getGlobalStat()['result']['downloadSpeed'], 3),
                    'speed_b' => round($aria2->getGlobalStat()['result']['downloadSpeed'] / 1024, 0),
                    'time' => time(),
                    'numActive' => $aria2->getGlobalStat()['result']['numActive'],
                    'numStopped' => $aria2->getGlobalStat()['result']['numStopped'],
                    'numWaiting' => $aria2->getGlobalStat()['result']['numWaiting']
                ]);
            }elseif(isset($request['lf'])){
                $table = [];
                $files = DB::table('download_list')
                    ->leftJoin('users', 'users.id', '=', 'download_list.user_id')
                    ->select('download_list.*', 'users.username')
                    ->orderBy('id','DEC')
                    ->take(20)
                    ->get();

                $main = new main();
                foreach($files as $file) {
                    if ($file->state == null)
                        $status = Lang::get('messages.in_queue');//in_queue
                    elseif ($file->state == 0)
                        $status = Lang::get('messages.finished');
                    elseif ($file->state == -1)
                        $status = Lang::get('messages.downloading');
                    elseif ($file->state == -2)
                        $status = Lang::get('messages.paused');
                    elseif ($file->state == -3)
                        $status = Lang::get('messages.deleted');
                    else
                        $status = Lang::get('messages.error_id', ['id' => $file->status]);



                    $table[] = [
                        'id' => $file->id,
                        'file_name' => $file->file_name,
                        'length' => $main->formatBytes($file->length, 1),
                        'date_added' => $file->date_added,
                        'details' => url('/files/' . $file->id),
                        'details_t' => Lang::get('messages.details'),
                        'state' => $status,
                        'username' => $file->username,
                        'username_l' => url('tools/users/' . $file->username)
                    ];
                }
                return response()->json($table);
            }
        }

        return redirect::back();
    }




    public function user_details_credits($user_name)
    {
        $user = User::where('username', '=', $user_name)->first();

        $tracks = DB::table('credit_log')
            ->select('credit_log.*', 'users.username')
            ->join('users', 'credit_log.agent', '=', 'users.id')
            ->where('user_id', '=', $user->id)
            ->get();

        $main = new main();
        return view('tools.user_credits', array('main' => $main, 'user' => $user, 'tracks' => $tracks));
    }




    public function postuser_details_credits(Request $request, $user_name)
    {
        $input = $request->only('new_credit');

        $this->validate($request, [
            'new_credit' => 'required|numeric'
        ]);
        if ($input['new_credit'] < 0) {
            return redirect::back()->withErrors(Lang::get('errors.neg_num'));
        }

        $input['new_credit'] *=  1024 * 1024 * 1024;
        $user = User::where('username', '=', $user_name)->first();
        $old_credit = $user->credit;

        DB::table('users')
            ->where('username', $user_name)
            ->update(['credit' => $input['new_credit']]);

        DB::table('credit_log')->insert(
            array(
                'user_id' => $user->id,
                'credit_change' =>  $input['new_credit'] - $old_credit,
                'agent' => Auth::user()->id,
            )
        );

        return redirect::back();
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

        if (!$request->ajax()) {
            $this->validate($request, [
                'function' => 'required'
            ]);
        }

        $functions = array('addUri', 'addTorrent', 'addMetalink', 'remove', 'forceRemove', 'pause', 'pauseAll', 'forcePause', 'forcePauseAll', 'unpause', 'unpauseAll', 'tellStatus', 'getUris', 'getFiles', 'getPeers', 'getServers', 'tellActive', 'tellWaiting', 'tellStopped', 'changePosition', 'changeUri', 'getOption', 'changeOption', 'getGlobalOption', 'changeGlobalOption', 'getGlobalStat', 'purgeDownloadResult', 'removeDownloadResult', 'getVersion', 'getSessionInfo', 'shutdown', 'forceShutdown', 'saveSession', 'multicall');

        if (!in_array($input['function'], $functions)) {
            if ($request->ajax()) {
                return response()->json(['Error' => 'The function does not exist in Aria2 functions.']);
            } else {
                return redirect::back()->withErrors('The function does not exist in Aria2 functions.');
            }
        }

        $main = new main();
        if (!$main->aria2_online())
            if ($request->ajax()) {
                return response()->json(['ERROR 10002' => 'Aria2c is not running!']);
            } else {
                return view('errors.general', array('error_title' => 'ERROR 10002', 'error_message' => 'Aria2c is not running!'));
            }


        $input['param'] = trim($input['param']);
        $params = $input['param'];
        $params = '[' . $params . ']';

        $aria2 = new aria2();

        $res = call_user_func_array(array($aria2, 'JSON_INPUT' . $input['function']), array($params));

        if ($request->ajax()) {
            return response()->json($res);
        }

        return redirect()->back()
            ->withInput()
            ->with('result', $res);
    }




    public function postuser_details($username)
    {
        if (!empty($username) || !isset($_POST['action'])) {
            if ($_POST['action'] == 'delete' && Config::get('leech.user_delete') == true) {
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
            ->where('id', '>' , 0)
            ->get();

        return view('tools.users', array('users' => $users, 'main' => $main));
    }


}
