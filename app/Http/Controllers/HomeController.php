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

        return view('myfiles_list', array('files' => $users, 'main' => $main));
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
                    if (@unlink(public_path() . '/' . Config::get('leech.save_to') . '/' . $file . '_' . $files_list[$file])) {
                        $message[] = 'Deleted: ' . $file . '_' . $files_list[$file];
                        DB::table('download_list')
                            ->where('id', $file)
                            ->update(['deleted' => 1]);
                    } else {
                        $errors[] = 'Not Deleted: ' . $file . '_' . $files_list[$file];
                    }
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

        return view('myfiles_list', array('files' => $users, 'main' => $main, 'messages' => $message, 'error' => $errors));
    }


    public function downloads()
    {
        $main = new main();
        if (!$main->aria2_online()) abort(404);

        if (Auth::user()->role == 2)
            $users = DB::table('download_list')
                ->whereRaw('(state != 0 OR state IS NULL)')
                ->where('deleted', '=', 0)
                ->get();
        else
            $users = DB::table('download_list')
                ->whereRaw('(state != 0 OR state IS NULL)')
                ->where('user_id', '=', Auth::user()->id)
                ->where('deleted', '=', 0)
                ->get();

        return view('download_list', array('files' => $users, 'main' => $main));
    }


    public function postindex(Request $request)
    {
        $input = $request->only('link', 'http_auth', 'http_username', 'http_password', 'comment', 'hold');

        if (Auth::user()->role != 2) {
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

        if (strpos($input['link'], '.torrent') !== false && Auth::user()->role != 2) {
            return redirect::back()->withErrors('What?! Torrent?! Go away!');
        }

        /**
         *  Get the file size of any remote resource (using get_headers()),
         *  either in bytes or - default - as human-readable formatted string.
         *
         * @author  Stephan Schmitz <eyecatchup@gmail.com>
         * @license MIT <http://eyecatchup.mit-license.org/>
         * @url     <https://gist.github.com/eyecatchup/f26300ffd7e50a92bc4d>
         *
         * @param   string $url Takes the remote object's URL.
         * @param   boolean $formatSize Whether to return size in bytes or formatted.
         * @return  string                 Returns human-readable formatted size
         *                                  or size in bytes (default: formatted).
         */
        /**
         * Returns the size of a file without downloading it, or -1 if the file
         * size could not be determined.
         *
         * @param $url - The location of the remote file to download. Cannot
         * be null or empty.
         *
         * @return The size of the file referenced by $url, or -1 if the size
         * could not be determined.
         */
        function curl_get_file_size($url)
        {
            // Assume failure.
            $result = -1;
            $status = 0;

            $curl = curl_init($url);

            // Issue a HEAD request and follow any redirects.
            curl_setopt($curl, CURLOPT_NOBODY, true);
            curl_setopt($curl, CURLOPT_HEADER, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

            $data = curl_exec($curl);
            curl_close($curl);
            $cl = array();
            $cl[] = -1;
            if ($data) {
                $content_length = "-1";
                $status = "-1";

                if (preg_match("/^HTTP\/1\.[01] (\d\d\d)/", $data, $matches)) {
                    $status = (int)$matches[1];

                }

                if (preg_match("/Content-Length: (\d+)/", $data, $matches)) {
                    $content_length = (int)$matches[1];
                    if ($status == 200 || ($status > 300 && $status <= 308)) $cl[] = (int)$matches[1];
                }

                // http://en.wikipedia.org/wiki/List_of_HTTP_status_codes
                if ($status == 200 || ($status > 300 && $status <= 308)) {
                    $result = $content_length;
                }
            }

            return array(max($cl), $status);
        }

        function mySize($head)
        {
            $length = -1;
            $i = $head;
            $lastresp = 0;
            foreach ($i as $d) {
                if (preg_match("/^HTTP\/1\.[01] (\d\d\d)/", $d, $matches)) {
                    $lastresp = $matches[1];
                    continue;
                }

                if (preg_match("/Content-Length: (\d+)/", $d, $matches) && $lastresp == 200) {
                    $length = $matches[1];
                }
            }

            return array($length, $lastresp);
        }

        //var_dump(get_headers($input['link'],0)) ;
//        echo mySize($input['link'])[0];
//        return;


        $mime_types = array(
            "application/pdf" => "pdf",
            "application/octet-stream" => "exe",
            "application/zip" => "zip",
            "application/msword" => "docx",
            "application/msword" => "doc",
            "application/vnd.ms-excel" => "xls",
            "application/vnd.ms-powerpoint" => "ppt",
            "image/gif" => "gif",
            "image/png" => "png",
            "image/jpeg" => "jpeg",
            "image/jpg" => "jpg",
            "audio/mpeg" => "mp3",
            "audio/x-wav" => "wav",
            "video/mpeg" => "mpeg",
            "video/mpeg" => "mpg",
            "video/mpeg" => "mpe",
            "video/quicktime" => "mov",
            "video/x-msvideo" => "avi",
            "video/3gpp" => "3gp",
            "text/css" => "css",
            "application/javascript" => "jsc",
            "application/javascript" => "js",
            "text/html" => "html"
        );

        //Based on http://stackoverflow.com/questions/11842721/cant-get-remote-filename-to-file-get-contents-and-then-store-file
        function getRealFilename($headers, $url)
        {
            GLOBAL $mime_types;
            // try to see if the server returned the file name to save
            foreach ($headers as $header) {
                if (strpos(strtolower($header), 'content-disposition') !== false) {
                    $tmp_name = explode('=', $header);
                    if (isset($tmp_name[1])) {
                        $f = trim($tmp_name[1], '";\'');
                        if ($f[strlen($f) - 1] === '.') $f = mb_substr($f, 0, -1);
                        $f = urldecode($f);
                        return $f;
                    }
                }
            }

            //we didn't find a file name to save-as in the header

            //so try to guess file extension by content-type
            foreach ($headers as $header) {
                if (strpos(strtolower($header), 'content-type') !== false) {
                    $tmp_name = explode(':', $header);
                    if (isset($tmp_name[1])) {
                        $mime_type = trim($tmp_name[1]);
                        $f = basename(preg_replace('/\\?.*/', '', $url)) . '.' . $mime_types[$mime_type];
                        if ($f[strlen($f) - 1] === '.') $f = mb_substr($f, 0, -1);
                        $f = urldecode($f);
                        return $f;
                    }
                }
            }

            //Nothing. Just grab it from the url
            $stripped_url = preg_replace('/\\?.*/', '', $url);

            $f = basename($stripped_url);
            if ($f[strlen($f) - 1] === '.') $f = mb_substr($f, 0, -1);
            $f = urldecode($f);
            return $f;
        }

        //Typical usage

        //now save the file

        //Typical usage
        //  $file = file_get_contents($input['link']);http://download.jetbrains.com/webide/PhpStorm-8.0.3.exe
//var_dump(get_headers($input['link']));
        $head = get_headers($input['link']);
        $fileSize = mySize($head);
        //  echo $fileSize[0];
        //  return;
        //  $a = 7062159360;
          var_dump($head);
        exit;
        // return;
        //echo $fileSize[0];

        if ($fileSize[0] < 1) {
            return redirect::back()->withErrors(Lang::get('validation.fileSize', array('size' => $fileSize[1])));
        }

        $filename = getRealFilename($head, $input['link']);
        //  echo $filename;
//return;
//        $filename=urldecode($filename);
//        echo "$filename - ";
//        return;


        if ($fileSize[0] > Auth::user()->credit) {
            return redirect::back()->withErrors('No Credit!');
        }


        $q_credit = Auth::user()->queue_credit + $fileSize[0];
//        echo $q_credit . ' ---- '. Auth::user()->credit . '\n\n\<br />';
        if ($q_credit > Auth::user()->credit) {
            return redirect::back()->withErrors('No Q Credit!');
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

        $id = DB::table('download_list')->insertGetId(
            array(
                'user_id' => Auth::user()->id,
                'link' => $input['link'],
                'length' => $fileSize[0],
                'file_name' => $filename,
                'hold' => $hold,
                'http_user' => $http_user,
                'http_password' => $http_pass,
                'comment' => $input['comment'],
            )
        );

        return Redirect::to('downloads');
        // echo $id;

//        return redirect::back()
//            ->withInput($request->only('link'))
//            ->withErrors([
//                'email' => Lang::get('messages.wrongPass'),
//            ]);
//        return view('home');
    }

}