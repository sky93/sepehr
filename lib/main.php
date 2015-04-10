<?php

class main
{
    /**
     * WARNING: This function chages stream_context_set_default. In the feature I'll fix this problem.
     *
     * @param $url
     * @param int $timeout
     * @param string $http_username
     * @param string $http_password
     *
     * @return array|bool(false)
     */
    public function get_info($url, $timeout = 10, $http_username = '', $http_password = ''){ //fixed issue #2
        $current_timeout = ini_get('default_socket_timeout');
        ini_set("default_socket_timeout", $timeout);
        stream_context_set_default(
            array(
                'http' => array(
                    'method' => 'GET'
                )
            )
        );

        $headers = @get_headers($url, 1);
        if (!$headers) return false;

        $headerso = $headers;

        $lastresp = 0;
        foreach ($headers as $key => $value) { //Make every key lowercase
            if (is_array($value))
                $st = $value[count($value)-1];
            else
                $st = $value;
            if (preg_match("/^HTTP\/1\.[01] (\d\d\d)/", $st, $matches)) {
                $lastresp = $matches[1];
            }
            if (strtolower($key) == $key) continue;
            $headers[strtolower($key)] = $headers[$key];
            unset($headers[$key]);
        }

        $file_size = NULL;
        if (array_key_exists('content-length', $headers)) { //File Size
            if (is_array($headers['content-length']))
                $file_size = $headers['content-length'][count($headers['content-length'])-1];
            else
                $file_size = $headers['content-length'];

        } else {
            $file_size = -1;
        }

        $location = NULL;
        if (array_key_exists('location', $headers)) { //File Location
            if (is_array($headers['location']))
                $location = $headers['location'][count($headers['location'])-1]; //todo: add FILTER_SANITIZE_URL validate
            else
                $location = $headers['location'];
        } else {
            $location = $url;
        }


        $filename = NULL;
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
            $filename = urldecode(basename(preg_replace('/\\?.*/', '', $location)));
        }


        ini_set("default_socket_timeout", $current_timeout); //restore the default socket time out.
        return array(
            'status' => $lastresp,
            'filename' => $filename,
            'file_extension' => pathinfo($filename, PATHINFO_EXTENSION),
            'filesize' => $file_size,
            'location' => $location,
            'full_headers' => $headers
        );
    }


    public function trusted_ip($ip)
    {
        $whitelist = array(
            '127.0.0.1',
            '::1'
        );

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

    public function aria2_online()
    {
        $host = $url = preg_replace("(^https?://)", "", config('leech.aria2_ip'));
        if (@fsockopen($host, config('leech.aria2_port'), $errno, $errstr, config('leech.aria2_time_out')))
            return true;
        else
            return false;
    }


    public function formatBytes($bytes, $precision = 2, $dec_point = '.', $thousands_sep = ',')
    {
        $negative = $bytes < 0;
        if ($negative) $bytes *= -1;
        $size = $bytes;
        $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
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
        $pri_addrs = array (
            '10.0.0.0|10.255.255.255', // single class A network
            '172.15.0.0|172.31.255.255', // 16 contiguous class B network
            '192.168.0.0|192.168.255.255', // 256 contiguous class C network
            '169.254.0.0|169.254.255.255', // Link-local address also refered to as Automatic Private IP Addressing
            '127.0.0.0|127.255.255.255' // localhost
        );

        $long_ip = ip2long ($ip);
        if ($long_ip != -1) {

            foreach ($pri_addrs AS $pri_addr) {
                list ($start, $end) = explode('|', $pri_addr);

                // IF IS PRIVATE
                if ($long_ip >= ip2long ($start) && $long_ip <= ip2long ($end)) {
                    return true;
                }
            }
        }

        return false;
    }
}