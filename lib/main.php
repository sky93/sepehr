<?php

class main
{
    /**
     * WARNING: This function changes stream_context_set_default. In the feature I'll fix this problem.
     * Gets URL information such as file size, file redirected url, filename, etc...
     *
     * @param $url
     * @param int $timeout
     * @param string $custom_headers
     *
     * @return array|bool(false)
     */
    public function get_info($url, $custom_headers = '', $timeout = 10)
    {
        $current_timeout = ini_get('default_socket_timeout');
        ini_set("default_socket_timeout", $timeout);
        stream_context_set_default(
            [
                'http' => [
                    'method' => 'GET',
                    'header' => $custom_headers
                ]
            ]
        );

        $headers = @get_headers($url, 1);
        if (!$headers)
            return false;

        $lastresp = 0;
        foreach ($headers as $key => $value) { //Make every key lowercase
            if (is_array($value))
                $st = $value[count($value)-1];
            else
                $st = $value;
            if (preg_match("/^HTTP\/1\.[01] (\d\d\d)/", $st, $matches)) {
                $lastresp = $matches[1];
            }
            if (strtolower($key) == $key)
                continue;
            $headers[strtolower($key)] = $headers[$key];
            unset($headers[$key]);
        }

        $file_size = null;
        if (array_key_exists('content-length', $headers)) { //File Size
            if (is_array($headers['content-length']))
                $file_size = $headers['content-length'][count($headers['content-length'])-1];
            else
                $file_size = $headers['content-length'];

        } else {
            $file_size = -1;
        }

        $incorrect_url = false;
        $location = null;
        if (array_key_exists('location', $headers)) { //File Location
            if (is_array($headers['location'])) {
                $location = $headers['location'][count($headers['location']) - 1];
                $location = filter_var($location, FILTER_SANITIZE_URL);
                if ( filter_var($location, FILTER_VALIDATE_URL) === false ) {
                    $incorrect_url = $location;
                    $location = $url;
                }
            } else {
                $location = $headers['location'];
                $location = filter_var($location, FILTER_SANITIZE_URL);
                if ( filter_var($location, FILTER_VALIDATE_URL) === false ) {
                    $incorrect_url = $location;
                    $location = $url;
                }
            }
        } else {
            $location = $url;
        }


        $filename = null;
        if (array_key_exists('content-disposition', $headers) && strpos($headers['content-disposition'], 'filename=') !== false) { //Header contains filename
            if (is_array($headers['content-disposition']))
                $str = $headers['content-disposition'][count($headers['content-disposition']) - 1];
            else
                $str = $headers['content-disposition'];

            if (preg_match('/.*filename=[\'\"]([^\'\"]+)/', $str, $matches)) {
                $filename = urldecode($matches[1]);
            } else if (preg_match("/.*filename=([^ |;]+)/", $str, $matches)) {
                $filename = urldecode($matches[1]);
            }
        } else {
            $filename = urldecode(basename(preg_replace('/\\?.*/', '', $incorrect_url ? $incorrect_url : $location)));
        }

        ini_set("default_socket_timeout", $current_timeout); //restore the default socket time out.
        return [
            'status' => $lastresp,
            'filename' => $filename,
            'file_extension' => pathinfo($filename, PATHINFO_EXTENSION),
            'filesize' => $file_size,
            'location' => $location,
            'full_headers' => $headers,
        ];
    }




    /**
     * Checks of an IP address is in white list or not.
     *
     * @param $ip
     * @return bool
     */
    public function trusted_ip($ip)
    {
        $whitelist = [
            '127.0.0.1',
            '::1'
        ];

        if (in_array($ip, $whitelist))
            return config('leech.trust_localhost') ? true : false;


        if (strpos($ip, ":"))
            return config('leech.trust_ipv6') ? true : false;

        $IPs = config('leech.trusted_ip');
        foreach ($IPs as $tIP) {
            $a = explode('.', $ip);
            list($w, $x, $y, $z) = explode('.', $tIP);

            $wa = explode('-', $w);
            $xa = explode('-', $x);
            $ya = explode('-', $y);
            $za = explode('-', $z);

            if (sizeof($a) !== 4) return false;

            if (sizeof($wa) === 1) $wa[] = $wa[0];
            if (sizeof($xa) === 1) $xa[] = $xa[0];
            if (sizeof($ya) === 1) $ya[] = $ya[0];
            if (sizeof($za) === 1) $za[] = $za[0];

            if (
                $a[0] >= $wa[0] && $a[0] <= $wa[1] &&
                $a[1] >= $xa[0] && $a[1] <= $xa[1] &&
                $a[2] >= $ya[0] && $a[2] <= $ya[1] &&
                $a[3] >= $za[0] && $a[3] <= $za[1]
            )
                return true;
        }
        return false;
    }




    /**
     * Checks if the entered URL is blocked or not by checking
     * URL Scheme, Port and Host.
     *
     * @param $url
     * @return bool|string
     */
    public function isBlocked($url)
    {
        $purl = parse_url($url);

        $schemes = config('leech.blocked_schemes');
        foreach ($schemes as $scheme){
            if ($purl['scheme'] == $scheme){
                return $scheme . ' is blocked by system administrator.';
            }
        }

        $ports = config('leech.blocked_ports');
        if (!isset($purl['port'])) $purl['port'] = 80;
        foreach ($ports as $port){
            $prange = explode('-',$port);
            if (count($prange) === 1) $prange[] = $prange[0];
            if ($purl['port'] >= $prange[0] && $purl['port'] <= $prange[1]){
                return 'Port ' . $purl['port'] . ' is blocked by system administrator.';
            }
        }

        $hosts = config('leech.blocked_hosts');
        foreach ($hosts as $host){
            if ($purl['host'] == $host){
                return $purl['host'] . ' is blocked by system administrator.';
            }
        }

        return false;
    }




    /**
     * Checks if Aria2 is online
     *
     * @return bool
     */
    public function aria2_online()
    {
        $host = $url = preg_replace("(^https?://)", "", config('leech.aria2_ip'));
        if (@fsockopen($host, config('leech.aria2_port'), $errno, $errstr, config('leech.aria2_time_out')))
            return true;
        else
            return false;
    }




    /**
     * Converts bytes to B, KB , MB, ..., YB
     *
     * @param $bytes
     * @param int $precision
     * @param string $dec_point
     * @param string $thousands_sep
     * @return string
     */
    public function formatBytes($bytes, $precision = 2, $dec_point = '.', $thousands_sep = ',')
    {
        $negative = $bytes < 0;
        if ($negative) $bytes *= -1;
        $size = $bytes;
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        $sz = $size / pow(1024, $power);
        if ($sz - round($sz) == 0) $precision = 0;
        if ($negative) $sz *= -1;
        return number_format($sz, $precision, $dec_point, $thousands_sep) . ' ' . $units[$power];
    }




    /**
     * Check if an ip is private to prevent show online scripts and stuff.
     * source: http://stackoverflow.com/a/13818126/2570054
     * @param $ip
     * @return bool
     */
    public function ip_is_private($ip)
    {
        $pri_addrs = [
            '10.0.0.0|10.255.255.255', // single class A network
            '172.15.0.0|172.31.255.255', // 16 contiguous class B network
            '192.168.0.0|192.168.255.255', // 256 contiguous class C network
            '169.254.0.0|169.254.255.255', // Link-local address also refered to as Automatic Private IP Addressing
            '127.0.0.0|127.255.255.255' // localhost
        ];

        $long_ip = ip2long ($ip);
        if ($long_ip != -1) {

            foreach ($pri_addrs as $pri_addr) {
                list ($start, $end) = explode('|', $pri_addr);

                // IF IS PRIVATE
                if ($long_ip >= ip2long ($start) && $long_ip <= ip2long ($end)) {
                    return true;
                }
            }
        }
        return false;
    }




    /**
     * Converts hours to day if hours is more than 24
     *
     * @param $h
     * @return string
     */
    public function hours2day($h)
    {
        if ($h > 24){
            return round($h/24) == 1 ? round($h/24) . ' Day': round($h/24) . ' Days';
        }
        return round($h) . ' Hours';
    }




    /**
     * Returns storage path. It also creates path if it does't exist.
     *
     * @return string
     */
    public function get_storage_path()
    {
        $path = public_path() . '/' . Config::get('leech.save_to') . '/';
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $symbol_link = @readlink($path);
        return $symbol_link ? $symbol_link : $path;
    }




    /**
     * Returns time ago...
     *
     * @param $datetime
     * @param bool $full
     * @return string
     */
    function time_elapsed_string($datetime, $full = false)
    {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = [
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        ];
        foreach ($string as $k => &$v) {
            if ($diff->$k){
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full){
            $string = array_slice($string, 0, 1);
        }
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }



    /**
     * Function: sanitize
     * Returns a sanitized string, typically for URLs and Filename.
     *
     * Parameters:
     *     $string - The string to sanitize.
     *     $force_lowercase - Force the string to lowercase?
     *     $anal - If set to *true*, will remove all non-alphanumeric characters.
     */
    function sanitize_filename($string, $force_lowercase = true, $anal = false) {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "_", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<",  ">", "/", "?");
        $clean = trim($string);
        $clean = trim($clean, '.');
        $clean = trim(str_replace($strip, "_", strip_tags($clean)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
        return ($force_lowercase) ?
            (function_exists('mb_strtolower')) ?
                mb_strtolower($clean, 'UTF-8') :
                strtolower($clean) :
            $clean;
    }




    /**
     *
     * Checks if the string contains blocked words.
     *
     * @param $string
     * @return bool
     */
    function word_filter($string) {
        $words = config('leech.blocked_words');
        if(preg_match("[$words]", $string) == true) {
            return true;
        }
        return false;
    }



    private function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int) ($log / 8) + 1; // length in bytes
        $bits = (int) $log + 1; // length in bits
        $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd >= $range);
        return $min + $rnd;
    }

    function getToken($length)
    {
        $token = '';
        $codeAlphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $codeAlphabet.= 'abcdefghijklmnopqrstuvwxyz';
        $codeAlphabet.= '0123456789';
        $max = strlen($codeAlphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $main = new main();
            $token .= $codeAlphabet[$main->crypto_rand_secure(0, $max)];
        }
        return $token;
    }



    /**
     *
     * Convert unsafe headers to standard headers
     *
     * @param $user_agent
     * @param $cookie
     * @param $headers
     * @return string
     */
    function convert_header($user_agent, $cookie, $headers) {
        if (! $user_agent) {
            $custom_user_agent = 'User-Agent: ' . env('APP_NAME', 'SEPEHR') . '/' . env('VERSION', '2.0') . "\n\r";
        } else {
            $user_agent = trim(preg_replace('/\s+/', ' ', $user_agent));
            $custom_user_agent = 'User-Agent: ' . $user_agent . "\n\r";
        }

        if (! $cookie) {
            $custom_cookie = '';
        } else {
            $cookie = trim(preg_replace('/\s+/', ' ', $cookie));
            $custom_cookie = 'Cookie: ' . $cookie . "\n\r";
        }

        if (! $headers) {
            $custom_headers = '';
        } else {
            $headers = str_replace("\n", "\n\r", $headers);
            $custom_headers = $headers;
        }

        $header = $custom_user_agent . $custom_cookie . $custom_headers;
        $header = preg_replace("/[\r\n]+/", "\n", $header);
        $header = rtrim($header, "\n\r");
        return $header;
    }


    /**
     *
     * Get all torrent information
     *
     * @param $path
     * @return array
     */
    function torrent_info($path){
        $full_path = $path;
        $torrent = new Torrent($path);

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

        return [
            'result' => 'ok',
            'size' => $main->formatBytes($torrent->size(), 1) ,
            'name' =>  $torrent->name(),
            'file_name' => basename($full_path),
            'hash' => $torrent->hash_info(),
            'comment' => $torrent->comment(),
            'piece_length' => $main->formatBytes($torrent->piece_length(),3),
            'content' => $JSTreeContent
        ];

    }
}