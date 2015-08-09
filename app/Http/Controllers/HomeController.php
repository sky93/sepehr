<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use aria2;
use main;
use Torrent;


class HomeController extends Controller
{
    /**
     * Redirect get requests of ping route to /
     *
     */
    public function redirect_ping ()
    {

        return Redirect::to('/');
    }



    /**
     * Redirects links
     *
     */
    public function link ($link)
    {
        $filename_array = explode('_', $link, 2);
        if (count($filename_array) != 2 || ! is_numeric($filename_array[0])){
            abort(404);
        }
        $file = DB::table('download_list')->where('id', '=', $filename_array[0])->where('deleted', '=', '0')->where('state', 0)->first();
        if (! $file || $file->file_name != $filename_array[1]){
            abort(404);
        }
        DB::table('download_list')->where('id', '=', $filename_array[0])->where('deleted', '=', '0')->where('state', 0)->increment('downloads');
        return Redirect::to(Config::get('leech.save_to') . '/' . $link);
    }



    /**
     * Updates online users
     *
     */
    public function ping (Request $request)
    {
        if ($request->ajax() && $request['ping'] == true) {
            DB::table('users')
                ->where('id', Auth::user()->id)
                ->update([
                    'last_seen' => date('Y-m-d H:i:s', time())
                ]);

                return response()->json([
                    'r' => 'k',
                ]);
        }

        return response()->json([
            'r' => 'n',
        ]);

    }


    /**
     * Shows main form
     *
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
            ->orderBy('date_added', 'DESC')
            ->get();

        return view('public_list', ['files' => $users, 'main' => $main]);
    }

    public function files()
    {
        $main = new main();

        $users = DB::table('download_list')
            ->where('user_id', Auth::user()->id)
            ->where('state', 0)
            ->where('deleted', 0)
            ->orderBy('date_added', 'DESC')
            ->get();

        return view('files_list', ['files' => $users, 'main' => $main]);
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

        $auth_files = [];
        $files_list = [];

        foreach ($files_query as $files) {
            $auth_files[] = $files->id;
            $files_list[$files->id] = $files->file_name;
        }

        $message = [];
        $errors = [];
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
            if (Auth::user()->public == 1) {
                foreach ($_POST['files'] as $file) {
                    if (in_array($file, $auth_files)) {
                        $message[] = 'Made Public: ' . $file . '_' . $files_list[$file];
                        DB::table('download_list')
                            ->where('id', $file)
                            ->update(['public' => 1]);
                    }
                }
            }else{
                return redirect::back()->withErrors(Lang::get('errors.cannot_public'));
            }
        } elseif ($_POST['action'] === 'never') {
            if ((Auth::user()->role == 2 && (Config::get('leech.keep') == 'admin' || Config::get('leech.keep') == 'all')) || (Auth::user()->role != 2 && Config::get('leech.keep') == 'all')) {
                foreach ($_POST['files'] as $file) {
                    if (in_array($file, $auth_files)) {
                        $message[] = $file . '_' . $files_list[$file] . ' Won\'t be deleted.';
                        DB::table('download_list')
                            ->where('id', $file)
                            ->update(['keep' => 1]);
                    }
                }
            } else {
                return redirect::back()->withErrors(Lang::get('errors.cannot_keep'));
            }
        }

        $users = DB::table('download_list')
            ->where('user_id', Auth::user()->id)
            ->where('state', 0)
            ->where('deleted', 0)
            ->get();

        return view('files_list', ['files' => $users, 'main' => $main, 'messages' => $message, 'error' => $errors]);
    }




    public function post_download_id($id, Request $request)
    {
        $main = new main();
        $input = $request->only('action', 'new_name');
        if (!isset($input['action']) || $input['action'] == null) {
            return view('errors.general', array('error_title' => 'ERROR 401', 'error_message' => 'Permission Denied!'));
        }

        $user_files = DB::table('download_list')
            ->where('user_id', Auth::user()->id)
            ->get();

        $auth_files = [];

        foreach ($user_files as $file) {
            $auth_files[] = $file->id;
            if ($file->id == $id) {
                $file_details = $file;
            }
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

            $aria2 = new aria2();

            if ($file_details->state == -1) { //Files is downloading
                if (!$main->aria2_online())
                    return view('errors.general', array('error_title' => 'ERROR 10002', 'error_message' => 'Aria2c is not running!'));
                $aria2->forceRemove(str_pad($file_details->id, 16, '0', STR_PAD_LEFT));
            } else {
                @unlink(public_path() . '/' . Config::get('leech.save_to') . '/' . $file_details->id . '_' . $file_details->file_name);

                //try to delete .aria2 file if exists.
                @unlink(public_path() . '/' . Config::get('leech.save_to') . '/' . $file_details->id . '_' . $file_details->file_name . '.aria2');

                DB::table('download_list')
                    ->where('id', $file_details->id)
                    ->update([
                        'deleted' => 1,
                        //'state' => -3 //decided to remove this because we lose the last state of the file after delete.
                    ]);

                if (+$file_details->state === 0 && $file_details->state !== null) {
                    return Redirect::to('files');
                } else {
                    return Redirect::to('downloads');
                }
            }
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
                $aria2->unpause(str_pad($file_details->id, 16, '0', STR_PAD_LEFT));
                DB::table('download_list')
                    ->where('id', $file_details->id)
                    ->update([
                        'state' => NULL
                    ]);
            }
        } elseif ($input['action'] == 'retry') { //retry action (we just change the state to null)
            DB::table('download_list')
                ->where('id', $file_details->id)
                ->update([
                    'state' => null
                ]);
            return Redirect::to('/downloads/');
        } elseif ($input['action'] == 'public' && $file_details->state == 0){
            if (Auth::user()->public == 1) {
                DB::table('download_list')
                    ->where('id', '=', $file_details->id)
                    ->update(['public' => DB::raw('!public')]);
                return Redirect::to('/files/' . $file_details->id);
            } else {
                return redirect::back()->withErrors(Lang::get('errors.cannot_public'));
            }
        } elseif ($input['action'] == 'rename' && $file_details->state == 0 && isset($input['new_name']) && !empty($input['new_name'])){
            if(preg_match(Config::get('leech.rename_regex'), $input['new_name'])) {
            $blocked_ext = Config::get('leech.blocked_ext');
            $ext = pathinfo($input['new_name'], PATHINFO_EXTENSION);
            if (array_key_exists($ext, $blocked_ext)) {
                if ($blocked_ext[$ext] === false){
                    return redirect::back()->withErrors('.' . $ext . ' files are blocked by the system administrator. Sorry.');
                } else {
                    $filename = pathinfo($input['new_name'],PATHINFO_FILENAME) . '.' . $blocked_ext[$ext];
                }
            } else {
                $filename = pathinfo($input['new_name'],PATHINFO_FILENAME) . '.' . $ext;
            }
                $result = @rename(public_path() . '/' . Config::get('leech.save_to') . '/' . $file_details->id . '_' . $file_details->file_name, public_path() . '/' . Config::get('leech.save_to') . '/' . $file_details->id . '_' . $filename);
                if ($result) {
                    DB::table('download_list')
                        ->where('id', '=', $file_details->id)
                        ->update(['file_name' => $filename]);
                    return Redirect::to('/files/' . $file_details->id)->with('message' , 'File successfully renamed to <strong>' . $filename . '</strong>.');
                } else {
                    return redirect::back()->withErrors('It is not possible to change the filename. Sorry.');
                }
            } else {
                return redirect::back()->withErrors('Filename is not in valid format.');
            }
        } elseif ($input['action'] == 'sha1' && $file_details->state == 0){
            $sha1 = sha1_file(public_path() . '/' . Config::get('leech.save_to') . '/' . $file_details->id . '_' . $file_details->file_name);
            if ($sha1){
                return Redirect::to('/files/' . $file_details->id)->with('message' , 'SHA1: <kbd>' . $sha1 . '</kbd>.');
            } else {
                return redirect::back()->withErrors('It is not possible to get SHA1. Sorry.');
            }
        } elseif ($input['action'] == 'md5' && $file_details->state == 0){
            $sha1 = md5_file(public_path() . '/' . Config::get('leech.save_to') . '/' . $file_details->id . '_' . $file_details->file_name);
            if ($sha1){
                return Redirect::to('/files/' . $file_details->id)->with('message' , 'MD5: <kbd>' . $sha1 . '</kbd>.');
            } else {
                return redirect::back()->withErrors('It is not possible to get MD5. Sorry.');
            }
        }

        return Redirect::back();
    }




    public function download_id($id)
    {
        if (Auth::user()->role != 2) {
            $file = DB::table('download_list')
                ->where('id', '=', $id)
                ->where('deleted', '=', 0)
                ->first();
        } else {
            $file = DB::table('download_list')
                ->where('id', '=', $id)
                ->first();
        }

        if (!$file || !$file->public)
            if (!$file || ($file->user_id != Auth::user()->id && Auth::user()->role != 2)) {
                return view('errors.general', [
                    'error_title' => 'ERROR 404',
                    'error_message' => 'This file does not exist or you do not have the right permission to view this file.'
                ]);
            }

        $main = new main();
        $aria2 = new aria2();

        return view('files.file_details', array('file' => $file, 'main' => $main, 'aria2' => $aria2));
    }




    public function downloads()
    {
        $main = new main();
        if (!$main->aria2_online()) return view('errors.general', array('error_title' => 'ERROR 10002', 'error_message' => 'Aria2c is not running!'));

        if (Auth::user()->role == 2) { //Admins need to see all downloads + usernames
            $users = DB::table('download_list')
                ->leftjoin('users', 'download_list.user_id', '=', 'users.id')
                ->select('download_list.*', 'users.username', 'users.first_name', 'users.last_name')
                ->whereRaw('(state != 0 OR state IS NULL)')
                ->where('deleted', '=', 0)
                ->orderBy('date_added', 'DESC')
                ->get();
        } else {
            $users = DB::table('download_list') //Normal users just see their downloads
                ->whereRaw('(state != 0 OR state IS NULL)')
                ->where('user_id', '=', Auth::user()->id)
                ->where('deleted', '=', 0)
                ->orderBy('date_added', 'DESC')
                ->get();
        }

        $aria2 = new aria2();
        return view('download_list', array('files' => $users, 'main' => $main, 'aria2' => $aria2));
    }

    public function post_downloads(Request $request)
    {
        if (Auth::user()->role == 2) { //Admins need to see all downloads + username
            $users = DB::table('download_list')
                ->whereRaw('(state != 0 OR state IS NULL)')
                ->where('deleted', '=', 0)
                ->get();
        } else {
            $users = DB::table('download_list')
                ->whereRaw('(state != 0 OR state IS NULL)')
                ->where('user_id', '=', Auth::user()->id)
                ->where('deleted', '=', 0)
                ->get();
        }

        $aria2 = new aria2();
        $main = new main();

        $json = [];
        foreach ($users as $file){
            $status = 1;
            $downloaded_speed_kb = $downloaded_speed = $downloaded_size = $connections = $numPieces = $numSeeders = 0;
            if (isset($aria2->tellStatus(str_pad($file->id, 16, '0', STR_PAD_LEFT))["result"])) {
                $result = $aria2->tellStatus(str_pad($file->id, 16, '0', STR_PAD_LEFT))["result"];
            } else {
                $result = null;
            }

            if (isset($result['numPieces'])) {
                $numPieces = $result['numPieces'];
            }

            if (isset($result['numSeeders'])) {
                $numSeeders = $result['numSeeders'];
            }

            if (isset($result['connections'])) {
                $connections = $result['connections'];
            }

            if (isset($result['completedLength'])) {
                $downloaded_size = $result['completedLength'];
            }

            if ($downloaded_size == 0) {
                $downloaded_size = $file->completed_length;
            }

            if (isset($result['downloadSpeed'])) {
                $speed_bytes = $result['downloadSpeed'];
                $downloaded_speed = $main->formatBytes($speed_bytes, 0) . '/s';
                $downloaded_speed_kb = round($speed_bytes/1024);
            }


            // -1   : Files is downloading by Aria2
            // -2   : Files is paused
            // null : Files is in queue
            if ($file->state != -1) {
                if ($file->state == null) {
                    $downloaded_speed = 'In queue';
                } elseif ($file->state == -2) {
                    $downloaded_speed = 'Paused';
                    $status = 2;
                } elseif ($file->state == -3) {
                    $downloaded_speed = 'Zipping';
                    $status = 4;
                } else {
                    $downloaded_speed = 'Error (' . $file->state . ')';
                    $status = 3;
                }
            }

            // Type: t = Torrent, n = Normal Download
            if ($file->torrent) {
                $json[$file->id] = [
                    'type' => 't',
                    'status' => $status,
                    'speed' => $downloaded_speed,
                    'dled_size' => $main->formatBytes($downloaded_size,1),
                    'pprog' => round($downloaded_size/$file->length*100,0) . '%',
                    'speed_kb' => $downloaded_speed_kb,
                    'numPieces' => $numPieces,
                    'numSeeders' => $numSeeders,
                    'connections' => $connections
                ];
            } else {
                $json[$file->id] = [
                    'type' => 'n',
                    'status' => $status,
                    'speed' => $downloaded_speed,
                    'dled_size' => $main->formatBytes($downloaded_size,1),
                    'pprog' => round($downloaded_size/$file->length*100,0) . '%',
                    'speed_kb' => $downloaded_speed_kb
                ];
            }
        }

        return response()->json($json);
    }




    public function postindex(Request $request)
    {
        $input = $request->only(
            'link',
            'http_auth',
            'http_username',
            'http_password',
            'comment',
            'hold',
            'id',
            'type',
            'torrent_file_name',
            't_submit_name',
            'fetch_filter'
        );

        $main = new main();
        if ($main->word_filter($input['link']) || $main->word_filter($input['comment']) || $main->word_filter($input['torrent_file_name'])) {
            if ($request->ajax()) {
                return response()->json([
                    'type' => 'error',
                    'message' => Lang::get('messages.blocked_file')
                ]);
            } else {
                return redirect::back()->withErrors(Lang::get('messages.blocked_file'));
            }
        }

return time();

        if ($request->ajax() && $input['type'] == 'fetch') {

            $v = Validator::make(
                $input,
                [
                    'link' => 'required|url'
                ]
            );
            if ($v->fails()) {
                return response()->json([
                    'result' => 'error',
                    'message' => 'Link is invalid.'
                ]);
            }

            //echo $html = file_get_contents($input['link']);

            //get_headers($input['link'], 1)


            $link = $main->get_info($input['link']);
           // echo $link['content_type'];
           // print_r($link['full_headers']['content-type']);

            if (isset($link['full_headers']['content-type'])){
                if (is_array($link['full_headers']['content-type'])) {
                    $contet_type = $link['full_headers']['content-type'][count($link['full_headers']['content-type']) - 1];
                }else{
                    $contet_type = $link['full_headers']['content-type'];
                }

                if (strpos($contet_type, 'text/') === false) {
                    return response()->json([
                        'result' => 'error',
                        'message' => 'Not a valid HTML link.'
                    ]);
                }
            }else{
                return response()->json([
                    'result' => 'error',
                    'message' => 'Not a valid HTML link.'
                ]);
            }

            $html = file_get_contents($input['link']);
            $pattern = '`.*?((http|https)://[\w#$&+,\/:;=?@.-]+)[^\w#$&+,\/:;=?@.-]*?`i';

            $links = [];
            if (preg_match_all($pattern,$html,$matches)) {
                foreach($matches[1] as $url) {
                    if ($input['fetch_filter'] == '' || strpos($url, $input['fetch_filter']) !== false) {
                        $links[] = $url;
                    }
                }

                if (count($links)) {
                    return response()->json([
                        'result' => 'ok',
                        'links' => $links
                    ]);
                } else {
                    return response()->json([
                        'result' => 'error',
                        'message' => 'No links found with the provided filter :('
                    ]);
                }

            }else{
                return response()->json([
                    'result' => 'error',
                    'message' => 'Could not find any link.'
                ]);
            }


        }elseif (! empty($input['torrent_file_name']) && ! empty($input['t_submit_name'])) { // Final Submit for torrents

            $path = public_path() . '/' . Config::get('leech.save_to') . '/torrent/' . Auth::user()->username . '_' . $input['torrent_file_name'];
            if (! file_exists($path)) {
                return redirect::back()->withErrors('Couldn\'t get your request');
            }
            $torrent = new Torrent($path);

            if (! $torrent->is_torrent($path)) {
                return redirect::back()->withErrors('Your Torrent file is not valid!');
            }

            $torrent_size = $torrent->size();
            if ($torrent_size < 1) {
                return redirect::back()->withErrors('Invalid Torrent size.');
            }

            if ($torrent_size > Auth::user()->credit) {
                return Redirect::to('/buy');
            }

            $q_credit = DB::table('download_list')
                    ->where('user_id', '=', Auth::user()->id)
                    ->where('deleted', '=', '0')
                    ->where(function ($query) {
                        $query->whereNull('state');
                        $query->orWhere('state', '<>', '0');
                    })
                    ->sum('length') + $torrent_size;

            if ($q_credit > Auth::user()->credit) {
                return redirect::back()->withErrors('You have too many files in your queue. Please wait until they finish up.');
            }

            $main = new main();
            $zip_name = $main->sanitize_filename($input['t_submit_name'] . '.zip');

            $hold = $input['hold'] ? -2 : null;

            DB::table('download_list')->insertGetId(
                [
                    'user_id' => Auth::user()->id,
                    'link' => $path,
                    'length' => $torrent_size,
                    'file_name' => $zip_name,
                    'state' => $hold,
                    'http_user' => null,
                    'http_password' => null,
                    'comment' => $input['comment'],
                    'torrent' => 1,
                    'date_added' => date('Y-m-d H:i:s', time())
                ]
            );

            return Redirect::to('downloads');

        } elseif ($request->ajax() && $input['type'] == 'torrent') {

            if (isset($_FILES[0])) {
                $maxsize    = 5 * 1024 * 1024; //5 MB
                $acceptable = [
                    'application/x-bittorrent'
                ];
                if(($_FILES[0]['size'] >= $maxsize) || ($_FILES[0]['size'] == 0)) {
                    return response()->json([
                        'result' => 'error',
                        'message' => 'File too large. File must be less than 5 megabytes.'
                        ]);
                }
                if(! in_array($_FILES[0]['type'], $acceptable) && !empty($_FILES[0]['type']) ) {
                    return response()->json([
                        'result' => 'error',
                        'message' => 'Invalid file type. Only Torrent files are accepted.'
                    ]);
                }

                $path = public_path() . '/' . Config::get('leech.save_to') . '/torrent/';
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                $path .= Auth::user()->username . '_' . $_FILES[0]['name'];

                move_uploaded_file($_FILES[0]['tmp_name'], $path);

                $torrent = new Torrent($path);

                $torrent->name();
                $main = new main();

                $new_content = [];
                foreach($torrent->content() as $key => $value) {
                    $key = str_replace('\\', '/', $key);
                    $new_content[] = $key . ' (' . $main->formatBytes($value,2) . ')';
                }

                $paths = $new_content;
                sort($paths);
                $array = [];
                foreach ($paths as $path) {
                    $path = trim($path, '/');
                    $list = explode('/', $path);
                    $n = count($list);

                    $arrayRef = &$array; // start from the root
                    for ($i = 0; $i < $n; $i++) {
                        $key = $list[$i];
                        $arrayRef = &$arrayRef[$key]; // index into the next level
                    }
                }

                $GLOBALS['rec'] = '{ "core" : { "data" : [';
                function rec ($array) {
                    $c = count($array);
                    foreach ($array as $key => $value) {
                        if (is_array($value)) {
                            $GLOBALS['rec'] .= '{"text": "' . $key . '"';
                            $GLOBALS['rec'] .= ', "children": [';
                                rec($value);
                            $GLOBALS['rec'] .= ']}';
                        } else {
                            $GLOBALS['rec'] .= '"' . $key . '"';
                            $c--;
                            if($c) $GLOBALS['rec'] .= ",";
                        }
                    }
                }
                rec($array);
                $GLOBALS['rec'] .= ']}}';
                $JSTreeContent = $GLOBALS['rec'];
                unset($GLOBALS['rec']);

                return response()->json([
                    'result' => 'ok',
                    'size' => $main->formatBytes($torrent->size(), 1) ,
                    'name' =>  $torrent->name(),
                    'file_name' => $_FILES[0]['name'],
                    'hash' => $torrent->hash_info(),
                    'comment' => $torrent->comment(),
                    'piece_length' => $main->formatBytes($torrent->piece_length(),3),
                    'content' => $JSTreeContent
                ]);


            } else {
                return response()->json([
                'result' => 'error',
                'message' => 'Nothing uploaded to the server.'
            ]);
            }

        } elseif ($request->ajax() && $input['type'] == 'check') {
            $v = Validator::make(
                $input,
                [
                    'link' => 'required|url'
                ]
            );
            if ($v->fails()) {
                $message = $v->messages();
                if ($request->ajax()) {
                    return response()->json([
                        'type' => 'error',
                        'message' => 'Input Error.'
                    ]);
                } else {
                    return redirect::back()->withErrors($message);
                }
            }
            if (strpos($input['link'], '.torrent') !== false && Auth::user()->role != 2) { //I'll delete this 'if' very soon.
                if ($request->ajax()) {
                    return response()->json([
                        'type' => 'error',
                        'message' => 'What?! Torrent?! Go away!'
                    ]);
                } else {
                    return redirect::back()->withErrors('What?! Torrent?! Go away!');
                }
            }
            $main = new main();

            $blocked = $main->isBlocked($input['link']);
            if ($blocked) {
                if ($request->ajax()) {
                    return response()->json([
                        'type' => 'error',
                        'message' => $blocked
                    ]);
                } else {
                    return redirect::back()->withErrors($blocked);
                }
            }
            $url_inf = $main->get_info($input['link']);
            if ($request->ajax()) {
                return response()->json([
                    'type' => 'success',
                    'status' => $url_inf['status'],
                    'filename' => $url_inf['filename'],
                    'file_extension' => $url_inf['file_extension'],
                    'filesize' => $main->formatBytes($url_inf['filesize'],1),
                    'location' => $url_inf['location']
                ]);
            } else {
                return redirect::back()->withErrors('sth is wrong.');
            }
        }
        else
        {
            $v = Validator::make(
                $input,
                [
                    'link' => 'required|url',
                    'comment' => 'max:140'
                ]
            );

            if ($v->fails()) {
                $message = $v->messages();
                if ($request->ajax()) {
                    return response()->json([
                        'type' => 'error',
                        'id' => $input['id'],
                        'message' => 'Input Error.'
                    ]);
                } else {
                    return redirect::back()->withErrors($message);
                }
            }


            if ($input['http_auth']) {
                $v = Validator::make(
                    $input,
                    [
                        'http_username' => 'required|max:64',
                        'http_password' => 'required|max:64'
                    ]
                );

                if ($v->fails()) {
                    $message = $v->messages();
                    if ($request->ajax()) {
                        return response()->json([
                            'type' => 'error',
                            'id' => $input['id'],
                            'message' => 'Input Error. HTTP Auth'
                        ]);
                    } else {
                        return redirect::back()->withErrors($message);
                    }

                }
            }

            if (strpos($input['link'], '.torrent') !== false && Auth::user()->role != 2) { //I'll delete this 'if' very soon.
                if ($request->ajax()) {
                    return response()->json([
                        'type' => 'error',
                        'id' => $input['id'],
                        'message' => 'What?! Torrent?! Go away!'
                    ]);
                } else {
                    return redirect::back()->withErrors('What?! Torrent?! Go away!');
                }
            }

            $main = new main();

            $blocked = $main->isBlocked($input['link']);
            if ($blocked) {
                if ($request->ajax()) {
                    return response()->json([
                        'type' => 'error',
                        'id' => $input['id'],
                        'message' => $blocked
                    ]);
                } else {
                    return redirect::back()->withErrors($blocked);
                }
            }

            $url_inf = $main->get_info($input['link']);

            $fileSize = $url_inf['filesize'];
            $filename = $url_inf['filename'];

            if ($url_inf['status'] != 200) {
                if (empty($url_inf['status'])) $url_inf['status'] = "N/A";
                if ($request->ajax()) {
                    return response()->json([
                        'type' => 'error',
                        'id' => $input['id'],
                        'message' => 'File not found or it has been moved! Response code was not 200' . " (" . $url_inf['status'] . ')'
                    ]);
                } else {
                    return redirect::back()->withErrors('File not found or it has been moved! Response code was not 200' . " (" . $url_inf['status'] . ')');
                }
            }

            $blocked_ext = Config::get('leech.blocked_ext');
            if (array_key_exists($url_inf['file_extension'], $blocked_ext)) {
                if ($blocked_ext[$url_inf['file_extension']] === false) {
                    if ($request->ajax()) {
                        return response()->json([
                            'type' => 'error',
                            'id' => $input['id'],
                            'message' => '.' . $url_inf['file_extension'] . ' files are blocked by system administrator. Sorry.'
                        ]);
                    } else {
                        return redirect::back()->withErrors('.' . $url_inf['file_extension'] . ' files are blocked by system administrator. Sorry.');
                    }
                } else {
                    $filename = pathinfo($url_inf['filename'], PATHINFO_FILENAME) . '.' . $blocked_ext[$url_inf['file_extension']];
                }
            }

            if ($fileSize < 1) {
                if ($request->ajax()) {
                    return response()->json([
                        'type' => 'error',
                        'id' => $input['id'],
                        'message' => 'Invalid File Size!' . " (" . $url_inf['status'] . ')'
                    ]);
                } else {
                    return redirect::back()->withErrors('Invalid File Size!' . " (" . $url_inf['status'] . ')');
                }
            }

            if ($fileSize > Auth::user()->credit) {
                if ($request->ajax()) {
                    return response()->json([
                        'type' => 'error',
                        'id' => $input['id'],
                        'message' => 'Not enough Credits!'
                    ]);
                } else {
                    return Redirect::to('/buy');
                    //return redirect::back()->withErrors('Not enough Credits!');
                }
            }

            $q_credit = DB::table('download_list')
                    ->where('user_id', '=', Auth::user()->id)
                    ->where('deleted', '=', '0')
                    ->where(function ($query) {
                        $query->whereNull('state');
                        $query->orWhere('state', '<>', '0');
                    })
                    ->sum('length') + $fileSize;

            if ($q_credit > Auth::user()->credit) {
                if ($request->ajax()) {
                    return response()->json([
                        'type' => 'error',
                        'id' => $input['id'],
                        'message' => 'You have too many files in your queue. Please wait until they finish up.'
                    ]);
                } else {
                    return redirect::back()->withErrors('You have too many files in your queue. Please wait until they finish up.');
                }
            }

            if (empty($filename)) {
                if ($request->ajax()) {
                    return response()->json([
                        'type' => 'error',
                        'id' => $input['id'],
                        'message' => 'Invalid Filename!'
                    ]);
                } else {
                    return redirect::back()->withErrors('Invalid Filename!');
                }
            }

//        DB::table('users')
//            ->where('id', Auth::user()->id)
//            ->update([
//                'queue_credit' => $q_credit
//            ]);

            $hold = $input['hold'] ? -2 : null;

            if ($input['http_auth']) {
                $http_user = $input['http_username'];
                $http_pass = $input['http_password'];
            } else {
                $http_user = $http_pass = null;
            }

            DB::table('download_list')->insertGetId(
                [
                    'user_id' => Auth::user()->id,
                    'link' => $url_inf['location'],
                    'length' => $fileSize,
                    'file_name' => $filename,
                    'state' => $hold,
                    'http_user' => $http_user,
                    'http_password' => $http_pass,
                    'comment' => $input['comment'],
                    'torrent' => 0,
                    'date_added' => date('Y-m-d H:i:s', time())
                ]
            );

            if ($request->ajax()) {
                return response()->json([
                    'type' => 'success',
                    'id' => $input['id'],
                    'message' => 'Link Added!'
                ]);
            } else {
                return Redirect::to('downloads');
            }
        }
    }
}