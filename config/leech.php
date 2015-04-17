<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Auto Delete
    |--------------------------------------------------------------------------
    |
    | Delete files automatically
    | Time in hours
    |
    */
    'auto_delete' => true,
    'auto_delete_time' => 24 * 3,



    /*
    |--------------------------------------------------------------------------
    | Save Directory
    |--------------------------------------------------------------------------
    |
    | Where are the downloaded files stored.
    | Do not add slashes (/) before or after directory name.
    |
    */

    'save_to' => 'storage',


    /*
    |--------------------------------------------------------------------------
    | Google Analytics
    |--------------------------------------------------------------------------
    |
    | Google Analytics tracking ID
    |
    */

    'GA' => 'UA-60769821-1',



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
    | IP Block Kind
    |--------------------------------------------------------------------------
    |
    | Options: 'both', 'private' or 'public'
    |
    | private: If IP address is private (class C, local, etc...)
    | public: If IP address is public
    | both: Block IP even when the IP address is public
    |
    */

    'ip_block_kind' => 'both',



    /*
    |--------------------------------------------------------------------------
    | IP Block duration
    |--------------------------------------------------------------------------
    |
    | Change the duration of IP Block in minutes.
    |
    */

    'ip_block_duration' => 5,


    /*
    |--------------------------------------------------------------------------
    | Password Retry Count
    |--------------------------------------------------------------------------
    |
    | How many times user should enter password wrong. After that, IP block will activate.
    |
    */

    'password_retry_count' => 2,




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



    /*
    |--------------------------------------------------------------------------
    | Blocked File Extensions
    |--------------------------------------------------------------------------
    |
    | if false, system won't let the user to download the file. otherwise the file extension will be
    | renamed to the value.
    |
    */

    'blocked_ext' => array(
        'php' => '_php_',
        'html' => '_html_',
        'torrent' => false,
        'php4' =>  '_php4_',
    ),




    /*
    |--------------------------------------------------------------------------
    | Blocked Hosts
    |--------------------------------------------------------------------------
    |
    | Easily block a host...
    |
    */

    'blocked_hosts' => array(
        'localhost',
        '127.0.0.1',
        '::1',
        '178.236.33.163',
        'sepehr.sadjad.ac.ir',
        'repo.sadjad.ac.ir',
        '178.236.33.162',
    ),



    /*
    |--------------------------------------------------------------------------
    | Blocked Ports
    |--------------------------------------------------------------------------
    |
    | Easily block a port...
    | We also support port range.
    |
    */

    'blocked_ports' => array(
        '20-79',
        '81-442',
        '444-999',
        '10000',
    ),




    /*
    |--------------------------------------------------------------------------
    | Blocked Schemes
    |--------------------------------------------------------------------------
    |
    | Easily block a scheme...
    |
    */



    'blocked_schemes' => array(
        'ftp',
        'rsync',
    ),

    /*
    |--------------------------------------------------------------------------
    | Rename Regex
    |--------------------------------------------------------------------------
    |
    | What regex Arial Leech should use for rename file validation
    |
    */

    'rename_regex' => '/^[A-Za-z0-9-.()_ ]+$/',



    /*
    |--------------------------------------------------------------------------
    | Message on site
    |--------------------------------------------------------------------------
    |
    | Show a message on web site only one. If you change the content of 'change_message',
    | the message will show up again on client's browser.
    |
    */

    'show_change_message' => true,
    'change_title1' => "Welcome To Sadjad University's Leecher",
    'change_title2' => "Sepehr",
    'change_message' => '<p dir="rtl">با این سیستم شما می توانید فایل های خود را در خانه به سیستم اضافه کنید. سیستم شروع به دانلود فایل خواهد نمود و سپس می توانید در دانشگاه با سرعت بسیار زیاد فایل های خود را بدون کم شدن از حجم اینترنت دانشگاه شما دانلود نمایید.</p><p dir="rtl">• در صورت داشتن مشکل یا سوال با ایمیل <a target="_blank" href="mailto:sepehr@sadjad.ac.ir">sepehr@sadjad.ac.ir</a> در ارتباط باشید.</p><p dir="rtl">• در صورت یافتن باگ نرم افزاری، مشکل را در <a target="_blank" href="https://github.com/Becca4Eva/Aria-Leecher/issues">اینجا</a> ثبت نمایید.</p><p dir="rtl">• برای مشاهده ی آخرین تغییرات سیستم، به <a target="_blank" href="https://github.com/Becca4Eva/Aria-Leecher/commits">اینجا</a> بروید.</p><p dir="rtl">لازم به ذکر است خرید اینترنتی به زودی فراهم می گردد. همچنین برای دانلود فایل ها از دانشگاه، بایستی از اینترنت خارج شوید تا از حجم حساب اینترنتی شما کسر نگردد.</p><p>با تشکر</p>',



    /*
    |--------------------------------------------------------------------------
    | Logo
    |--------------------------------------------------------------------------
    |
    | External links not allowed.
    |
    */

    'logo_address' => 'img/logo.png',



    /*
    |--------------------------------------------------------------------------
    | Delete user
    |--------------------------------------------------------------------------
    |
    | Let Admins delete users
    |
    */

    'user_delete' => false,




    /*
    |--------------------------------------------------------------------------
    | Public Files
    |--------------------------------------------------------------------------
    |
    | Let Users make their own files public
    |
    | Options:
    |
    | 'all' : all users with any role can make a file public.
    | 'admin' : Only administrators can make a file public.
    */

    'public' => 'admin',

];