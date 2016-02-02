<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Config;
use Illuminate\Http\Request;

use App\User;

use aria2;
use main;


class AdminController extends Controller
{

    public function payments()
    {
        $tracks = DB::table('payments')
            ->join('users', 'payments.user_id', '=', 'users.id')
            ->select('payments.*', 'users.username', 'users.first_name', 'users.last_name')
            ->whereNotNull('verifyCode')
            ->where('verifyCode', '=' , '0')
            ->get();

        $main = new main();

        return view('payment.history', ['main' => $main, 'tracks' => $tracks]);
    }

    public function stat()
    {
        $main = new main();

        if (!$main->aria2_online()) {
            return view('errors.general', array('error_title' => 'ERROR 10002', 'error_message' => 'Aria2c is not running!'));
        }

        $aria2 = new aria2();

        return view('tools.stats', ['main' => $main, 'aria2' => $aria2]);
    }




    public function post_stat(Request $request)
    {
        $main = new main();

        if ($request->ajax()) {
            if (isset($request['gs'])) {
                if (!$main->aria2_online()) {
                    return response()->json(['ERROR 10002' => 'Aria2 is not running!']);
                }

                $aria2 = new aria2();

                return response()->json([
                    'speed' => $main->formatBytes($aria2->getGlobalStat()['result']['downloadSpeed'], 3),
                    'speed_b' => round($aria2->getGlobalStat()['result']['downloadSpeed'] / 1024, 0),
                    'time' => time(),
                    'numActive' => $aria2->getGlobalStat()['result']['numActive'],
                    'numStopped' => $aria2->getGlobalStat()['result']['numStopped'],
                    'numWaiting' => $aria2->getGlobalStat()['result']['numWaiting']
                ]);

            } elseif (isset($request['lf'])) {
                $table = [];

                $files = DB::table('download_list')
                    ->leftJoin('users', 'users.id', '=', 'download_list.user_id')
                    ->select('download_list.*', 'users.username')
                    ->orderBy('id','DEC')
                    ->take(20)
                    ->get();

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
                        $status = Lang::get('messages.error_id', ['id' => $file->state]);

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
            } elseif (isset($request['sz'])) {
                $total = disk_total_space($main->get_storage_path());
                $free = disk_free_space($main->get_storage_path());

                return response()->json([
                    'free' => $main->formatBytes($free, 3),
                    'total' => $main->formatBytes($total, 3),
                    'used' => $main->formatBytes($total-$free, 3),
                    'percent' => round(((($total-$free) * 100) / $total), 2),
                ]);
            }
        }

        return redirect::back();
    }




    public function user_details_credits($user_name)
    {
        $user = DB::table('users')
            ->where('username', '=', $user_name)
            ->first();

        if ($user == null) {
            return view('errors.general', [
                'error_title' => 'ERROR 404',
                'error_message' => 'This file does not exist or you do not have the right permission to view this file.'
            ]);
        }

        $tracks = DB::table('credit_log')
            ->select('credit_log.*', 'users.username')
            ->join('users', 'credit_log.agent', '=', 'users.id')
            ->where('user_id', '=', $user->id)
            ->get();

        $main = new main();

        return view('tools.user_credits', ['main' => $main, 'user' => $user, 'tracks' => $tracks]);
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
            return view('errors.general', [
                'error_title' => 'ERROR 10002',
                'error_message' => 'Aria2c is not running!'
            ]);

        $aria2 = new aria2();

        return view('tools.aria2console', ['main' => $main, 'aria2' => $aria2]);
    }




    public function post_aria2console(Request $request)
    {
        $input = $request->only('function', 'param');

        if (!$request->ajax()) {
            $this->validate($request, [
                'function' => 'required'
            ]);
        }

        $functions = ['addUri', 'addTorrent', 'addMetalink', 'remove', 'forceRemove', 'pause', 'pauseAll', 'forcePause', 'forcePauseAll', 'unpause', 'unpauseAll', 'tellStatus', 'getUris', 'getFiles', 'getPeers', 'getServers', 'tellActive', 'tellWaiting', 'tellStopped', 'changePosition', 'changeUri', 'getOption', 'changeOption', 'getGlobalOption', 'changeGlobalOption', 'getGlobalStat', 'purgeDownloadResult', 'removeDownloadResult', 'getVersion', 'getSessionInfo', 'shutdown', 'forceShutdown', 'saveSession', 'multicall'];

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
                return view('errors.general', [
                    'error_title' => 'ERROR 10002',
                    'error_message' => 'Aria2c is not running!'
                ]);
            }


        $input['param'] = trim($input['param']);
        $params = $input['param'];
        $params = '[' . $params . ']';

        $aria2 = new aria2();

        $res = call_user_func_array([$aria2, 'JSON_INPUT' . $input['function']], [$params]);

        if ($request->ajax()) {
            return response()->json($res);
        }

        return redirect()->back()
            ->withInput()
            ->with('result', $res);
    }




    public function postuser_details($username)
    {
        if (! empty($username) || ! isset($_POST['action'])) {
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
            } elseif ($_POST['action'] == 'hard_logout') {
                $user = User::where('username', '=', $username)->first();
                $user->login_token = null;
                $user->save();
            }
        }
        return Redirect::to('/tools/users/' . $username);
    }





    public function user_details($user_name)
    {
        $users = User::where('username', '=', $user_name)->first();

        if ($users == null) {
            return view('errors.general', [
                'error_title' => 'ERROR 404',
                'error_message' => 'The user you are looking for might have been removed, had its name changed, or is temporarily unavailable.'
            ]);
        }

        $main = new main();

        $user_detailed = DB::table('download_list')
            ->select(DB::raw('
            SUM(completed_length) as completed_length_sum,
            SUM(length) as length_sum,
            COUNT(*) as total_files_deleted,
            (select COUNT(*) from download_list where deleted=0 AND user_id = ' . $users->id . ') as total_files,
            (select SUM(`length`) from download_list where (state is null or state <> 0) AND deleted = 0 AND user_id = ' . $users->id . ') as queue_credit,
            (select COUNT(*) from download_list where state > 0 AND deleted=0 AND user_id = ' . $users->id . ') as total_error_files,
            (select COUNT(*) from download_list where state > 0 AND user_id = ' . $users->id . ') as total_error_files_deleted,
            (select COUNT(*) from download_list where (state is null or state <> 0) AND deleted = 0 AND user_id = ' . $users->id . ') as total_download_queue

            '))
            ->where('user_id', '=', $users->id)
            ->first();

        $user_files = DB::table('download_list')
            ->where('user_id' ,'=', $users->id)
            ->get();

        return view('tools.userdetails', ['user_files' => $user_files, 'user' => $users, 'userd' => $user_detailed, 'main' => $main]);
    }




    public function users()
    {
        $main = new main();

        $page = Input::get('page');

        if (!$page) {
            $page = 1;
        }

        $users_count = DB::table('users')->count();
        $skip = ($page - 1) * 20;
        $take = 20;

        if ($page == 'all') {
            $skip = 0;
            $take = $users_count;
        }

        $users = DB::table('users')
            ->where('id', '>' , 0)
            ->skip($skip)
            ->take($take)
            ->get();

        return view('tools.users', ['users' => $users, 'main' => $main, 'users_count' => $users_count]);
    }



    public function post_users(Request $request)
    {
        $main = new main();

        $input = $request->only('id', 'first_name', 'last_name', 'username', 'email', 'public', 'torrent', 'active');
        $post_back = $input;

        if ($input['id'] == trim('')) $input['id'] = '%';
        if ($input['first_name'] == trim('')) $input['first_name'] = '%';
        if ($input['last_name'] == trim('')) $input['last_name'] = '%';
        if ($input['username'] == trim('')) $input['username'] = '%';
        if ($input['email'] == trim('')) $input['email'] = '%';

        if ($input['torrent'] == 2) $input['torrent'] = 1;
        elseif ($input['torrent'] == 3) $input['torrent'] = 0;
        else $input['torrent'] = '%';

        if ($input['public'] == 2) $input['public'] = 1;
        elseif ($input['public'] == 3) $input['public'] = 0;
        else $input['public'] = '%';

        if ($input['active'] == 2) $input['active'] = 1;
        elseif ($input['active'] == 3) $input['active'] = 0;
        else $input['active'] = '%';

        $users = DB::table('users')
            ->where('id', '>' , 0)
            ->where('id', 'like' , '%' . $input['id'] . '%')
            ->where('first_name', 'like' , '%' . $input['first_name'] . '%')
            ->where('last_name', 'like' , '%' . $input['last_name'] . '%')
            ->where('username', 'like' , '%' . $input['username'] . '%')
            ->where('email', 'like' , '%' . $input['email'] . '%')
            ->where('active', 'like' , '%' . $input['active'] . '%')
            ->where('public', 'like' , '%' . $input['public'] . '%')
            ->where('torrent', 'like' , '%' . $input['torrent'] . '%')
            ->get();

        return view('tools.users', [
            'users' => $users,
            'main' => $main,
            'page' => false,
            'id' => $post_back['id'],
            'username' => $post_back['username'],
            'last_name' => $post_back['last_name'],
            'first_name' => $post_back['first_name'],
            'email' => $post_back['email'],
            'active' => $post_back['active'],
            'public' => $post_back['public'],
            'torrent' => $post_back['torrent'],
        ]);
    }



    public function files(Request $request)
    {
        $main = new main();

        if (Input::get('showall') == 1) {
            $files = DB::table('download_list')
                ->leftJoin('users', 'users.id', '=', 'download_list.user_id')
                ->select('download_list.*', 'users.username', 'users.first_name', 'users.last_name')
                ->where('download_list.id', '>' , 0)
                ->where('deleted', '=' , 0)
                ->where('state', '=' , 0)
                ->orderBy('id','DEC')
                ->get();

            return view('tools.all_files', ['files' => $files, 'main' => $main]);
        }

        $page = Input::get('page');
        if (!$page)
            $page = 1;

        $files_count = DB::table('download_list')->count();

        $files = DB::table('download_list')
            ->leftJoin('users', 'users.id', '=', 'download_list.user_id')
            ->select('download_list.*', 'users.username', 'users.first_name', 'users.last_name')
            ->where('download_list.id', '>' , 0)
            ->orderBy('id','DEC')
            ->skip(($page - 1) * 100)
            ->take(100)
            ->get();

        return view('tools.all_files', ['files' => $files, 'main' => $main, 'files_count' => $files_count]);
    }

}
