<?php

class main
{
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


    public function aria2_online()
    {
        $host = $url = preg_replace("(^https?://)", "", config('leech.aria2_ip'));
        if (@fsockopen($host, config('leech.aria2_port'), $errno, $errstr, config('leech.aria2_time_out')))
            return true;
        else
            return false;
    }


    public function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}