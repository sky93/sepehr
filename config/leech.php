<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Save Directory
	|--------------------------------------------------------------------------
	|
	| Where are the downloaded files stored.
	| Do not add slashes (/) before or after directory name.
	|
	*/

	'save_to' => 'files',



    /*
	|--------------------------------------------------------------------------
	| Trusted IP
	|--------------------------------------------------------------------------
	|
	| Won't show Google Re-captcha for these IP ranges. Cool huh?
	| Add a range using: -
    | Example: '178.2-50.33.0-255'
	|
	*/

	'trusted_ip' => array(
        '172.15-31.0-255.0-255', // 256 contiguous class C network
        '169.254.0-255.0-255',   // Link-local address also referred to as Automatic Private IP Addressing
        '192.168.0-255.0-255',   // 16 contiguous class B network
        //'0-255.0-255.0-255.0-255',   // Add as much as you want!
    ),
    'trust_localhost' => true,   // Trust 127.0.0.1 and ::1
    'trust_ipv6' => false,




    /*
	|--------------------------------------------------------------------------
	| Aria 2 RPC Settings
	|--------------------------------------------------------------------------
	|
    | Do not add slashes (/) before or after routes.
	|
	*/

	'aria2_ip' => 'http://127.0.0.1',
    'aria2_port' => 6800,
    'aria2_route' => 'jsonrpc',



    /*
    |--------------------------------------------------------------------------
    | Aria 2 RPC Connect Time Out
    |--------------------------------------------------------------------------
    |
    | In seconds, 0 is infinity
    |
    */

    'aria2_time_out' => 1,

];
