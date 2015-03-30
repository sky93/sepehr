<?php namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Redirect;
use Lang;
use Validator;
use Auth;
use DB;
use aria2;
use main;
use Illuminate\Support\Facades\Config;

class HomeController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | Home Controller
    |--------------------------------------------------------------------------
    |
    | This controller renders your application's "dashboard" for users that
    | are authenticated. Of course, you are free to change or remove the
    | controller as you wish. It is just here to get your app started!
    |
    */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Show the application dashboard to the user.
     *
     * @return Response
     */
    public function index()
    {
        return view('home');
    }


    public function public_files()
    {

        $main = new main();

        $users = DB::table('download_list')
            ->where('public', '=', 1)
            ->where('state', '=', 0)
            ->where('deleted', '=', 0)
            ->get();

        return view('public_list', array('files' => $users, 'main' => $main));
    }

    public function files()
    {

        $main = new main();

        $users = DB::table('download_list')
            ->where('user_id', Auth::user()->id)
            ->where('state', 0)
            ->where('deleted', 0)
            ->get();

        return view('files_list', array('files' => $users, 'main' => $main));
    }


    public function postfiles()
    {

        $main = new main();

        if (!isset($_POST['files']) || empty($_POST['files'])) {
            return redirect::back()->withErrors(Lang::get('messages.file.no.files'));
        }

        $files_query = DB::table('download_list')
            ->where('user_id', Auth::user()->id)
            ->where('state', 0)
            ->where('deleted', 0)
            ->get();

        $auth_files = array();
        $files_list = array();

        foreach ($files_query as $files) {
            $auth_files[] = $files->id;
            $files_list[$files->id] = $files->file_name;
        }

        $message = array();
        $errors = array();
        if ($_POST['action'] === 'delete') {
            foreach ($_POST['files'] as $file) {
                if (in_array($file, $auth_files)) {
                    @unlink(public_path() . '/' . Config::get('leech.save_to') . '/' . $file . '_' . $files_list[$file]);
                    @unlink(public_path() . '/' . Config::get('leech.save_to') . '/' . $file . '_' . $files_list[$file] . '.aria2');
                        $message[] = 'Deleted: ' . $file . '_' . $files_list[$file];
                        DB::table('download_list')
                            ->where('id', $file)
                            ->update(['deleted' => 1]);
                }
            }
        } elseif ($_POST['action'] === 'public') {
            foreach ($_POST['files'] as $file) {
                if (in_array($file, $auth_files)) {
                        $message[] = 'Made Public: ' . $file . '_' . $files_list[$file];
                        DB::table('download_list')
                            ->where('id', $file)
                            ->update(['public' => 1]);
                }
            }
        }

        $users = DB::table('download_list')
            ->where('user_id', Auth::user()->id)
            ->where('state', 0)
            ->where('deleted', 0)
            ->get();

        return view('files_list', array('files' => $users, 'main' => $main, 'messages' => $message, 'error' => $errors));
    }


    public function post_download_id($id, Request $request)
    {
        $main = new main();
        $input = $request->only('action');
        if (!isset($input['action']) || $input['action'] == NULL)
            return view('errors.general', array('error_title' => 'ERROR 401', 'error_message' => 'Permission Denied!'));

        $user_files = DB::table('download_list')
            ->where('user_id', Auth::user()->id)
            ->get();

        $auth_files = array();

        foreach ($user_files as $file) {
            $auth_files[] = $file->id;
            if ($file->id == $id) $file_details = $file;
        }

        if (Auth::user()->role == 2 && (!isset($file_details) || empty($file_details))) { //$file_details for admins may be empty
            $file_details = DB::table('download_list')
                ->where('id', '=', $id)
                ->first();
        }

        if (Auth::user()->role != 2)
            if (!in_array($id, $auth_files))
                return view('errors.general', array('error_title' => 'ERROR 401', 'error_message' => 'Permission Denied!'));

        if (!isset($file_details) || empty($file_details))
            return view('errors.general', array('error_title' => 'ERROR 401', 'error_message' => 'Permission Denied!'));


        if ($input['action'] == 'remove') { //Remove action

            if ($file_details->state == -1) { //Files is downloading or is in queue.
                if (!$main->aria2_online())
                    return view('errors.general', array('error_title' => 'ERROR 10002', 'error_message' => 'Aria2c is not running!'));
                $aria2 = new aria2();
                $aria2->forceRemove(str_pad($file_details->id, 16, '0', STR_PAD_LEFT));
            }

            @unlink(public_path() . '/' . Config::get('leech.save_to') . '/' . $file_details->id . '_' . $file_details->file_name);
            @unlink(public_path() . '/' . Config::get('leech.save_to') . '/' . $file_details->id . '_' . $file_details->file_name . '.aria2');
            DB::table('download_list')
                ->where('id', $file_details->id)
                ->update([
                    'deleted' => 1,
                    'state' => -3
                ]);


//                DB::table('users') //fix queue credit
//                    ->where('id', $file_details->user_id)
//                    ->decrement('queue_credit', $file_details->length);

            return Redirect::to('/downloads');

        } elseif ($input['action'] == 'pause') { //Pause action
            if (!$main->aria2_online()) return view('errors.general', array('error_title' => 'ERROR 10002', 'error_message' => 'Aria2c is not running!'));
            $aria2 = new aria2();
            if ($file_details->state == -1) {
                $aria2->forcePause(str_pad($file_details->id, 16, '0', STR_PAD_LEFT));
                DB::table('download_list')
                    ->where('id', $file_details->id)
                    ->update([
                        'state' => -2
                    ]);
            } else {
                if (!$main->aria2_online()) return view('errors.general', array('error_title' => 'ERROR 10002', 'error_message' => 'Aria2c is not running!'));
                $aria2->unpause(str_pad($file_details->id, 16, '0', STR_PAD_LEFT));
                DB::table('download_list')
                    ->where('id', $file_details->id)
                    ->update([
                        'state' => NULL
                    ]);
            }
        } elseif ($input['action'] == 'retry') { //retry action (we just change the state to NULL
            DB::table('download_list')
                ->where('id', $file_details->id)
                ->update([
                    'state' => NULL
                ]);
        }

        return Redirect::to('/files/' . $id);

    }

    public function download_id($id)
    {

        if (Auth::user()->role != 2)
            $file = DB::table('download_list')
                ->where('id', '=', $id)
                ->where('deleted', '=', 0)
                ->first();
        else
            $file = DB::table('download_list')
                ->where('id', '=', $id)
                ->first();

        if (!$file || !$file->public)
            if (!$file || ($file->user_id != Auth::user()->id && Auth::user()->role != 2)) {
                return view('errors.general', array('error_title' => 'ERROR 404', 'error_message' => 'This file does not exist or you do not have the right permission to view this file.'));
            }

        $main = new main();
        $aria2 = new aria2();
        return view('files.file_details', array('file' => $file, 'main' => $main, 'aria2' => $aria2));
    }


    public function downloads()
    {
        $main = new main();
        if (!$main->aria2_online()) return view('errors.general', array('error_title' => 'ERROR 10002', 'error_message' => 'Aria2c is not running!'));

        if (Auth::user()->role == 2) //Admins need to see all downloads + username
            $users = DB::table('download_list')
                ->join('users', 'download_list.user_id', '=', 'users.id')
                ->select('download_list.*', 'users.username')
                ->whereRaw('(state != 0 OR state IS NULL)')
                ->where('deleted', '=', 0)
                ->get();
        else
            $users = DB::table('download_list')
                ->whereRaw('(state != 0 OR state IS NULL)')
                ->where('user_id', '=', Auth::user()->id)
                ->where('deleted', '=', 0)
                ->get();

        $aria2 = new aria2();
        return view('download_list', array('files' => $users, 'main' => $main, 'aria2' => $aria2));
    }


    public function postindex(Request $request)
    {
        $input = $request->only('link', 'http_auth', 'http_username', 'http_password', 'comment', 'hold');

        if (Auth::user()->role !== 2) { //for debug only we don't check url validation for admins
            $this->validate($request, [
                'link' => 'required|url',
                'comment' => 'max:140'
            ]);
        }else{
            $this->validate($request, [
                'link' => 'required',
                'comment' => 'max:140'
            ]);
        }

        if ($input['http_auth']) {
            $this->validate($request, [
                'http_username' => 'required|max:64',
                'http_password' => 'required|max:64'
            ]);
        }

        if (strpos($input['link'], '.torrent') !== false && Auth::user()->role != 2) { //I'll delete this 'if' very soon.
            return redirect::back()->withErrors('What?! Torrent?! Go away!');
        }

        $main = new main();

        $blocked = $main->isBlocked($input['link']);
        if ($blocked){
            return redirect::back()->withErrors($blocked);
        }

        $url_inf = $main->get_info($input['link']);

        $fileSize = $url_inf['filesize'];
        $filename = $url_inf['filename'];

        if ($url_inf['status'] != 200){
            return redirect::back()->withErrors('File not found or it has been moved!' . " (" . $url_inf['status'] . ')');
        }

        $blocked_ext = Config::get('leech.blocked_ext');
        if (array_key_exists($url_inf['file_extension'], $blocked_ext)) {
            if ($blocked_ext[$url_inf['file_extension']] === false){
                return redirect::back()->withErrors('.' . $url_inf['file_extension'] . ' files are blocked by system administrator. Sorry.');
            }else{
                $filename = pathinfo($url_inf['filename'],PATHINFO_FILENAME) . '.' . $blocked_ext[$url_inf['file_extension']];
            }
        }

        if ($fileSize < 1) {
            return redirect::back()->withErrors('Invalid File Size!' . " (" . $url_inf['status']. ')');
        }

        if ($fileSize > Auth::user()->credit) {
            return redirect::back()->withErrors('Not enough Credits!');
        }


        $q_credit = Auth::user()->queue_credit + $fileSize;
        if ($q_credit > Auth::user()->credit) {
            return redirect::back()->withErrors('You have too many files in your queue. Please wait until they finish up.');
        }


        if (empty($filename)) {
            return redirect::back()->withErrors('Invalid Filename!');
        }


        DB::table('users')
            ->where('id', Auth::user()->id)
            ->update([
                'queue_credit' => $q_credit
            ]);

        $hold = $input['hold'] ? 1 : 0;

        if ($input['http_auth']) {
            $http_user = $input['http_username'];
            $http_pass = $input['http_password'];
        } else {
            $http_user = $http_pass = NULL;
        }

        DB::table('download_list')->insertGetId(
            array(
                'user_id' => Auth::user()->id,
                'link' => $url_inf['location'],
                'length' => $fileSize,
                'file_name' => $filename,
                'hold' => $hold,
                'http_user' => $http_user,
                'http_password' => $http_pass,
                'comment' => $input['comment'],
            )
        );

        return Redirect::to('downloads');

    }

}