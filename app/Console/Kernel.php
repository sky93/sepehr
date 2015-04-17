<?php namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use DB;
use Config;

class Kernel extends ConsoleKernel {

	/**
	 * The Artisan commands provided by your application.
	 *
	 * @var array
	 */
	protected $commands = [
		'App\Console\Commands\Inspire',
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
	 * @return void
	 */
	protected function schedule(Schedule $schedule)
	{
        $schedule->call(function()
        {
            if (config('leech.auto_delete')) {
                $time = date("Y-m-d H:i:s", time() - (config('leech.auto_delete_time') * 60 * 60));

                $old_files = DB::table('download_list')
                    ->where('date_completed', '<', $time)
                    ->where('keep', '=', 0)
                    ->where('deleted', '=', 0)
                    ->get();

                foreach ($old_files as $old_file) {
                    $res = @unlink(public_path() . '/' . Config::get('leech.save_to') . '/' . $old_file->id . '_' . $old_file->file_name);
                    @unlink(public_path() . '/' . Config::get('leech.save_to') . '/' . $old_file->id . '_' . $old_file->file_name . '.aria2');
                    DB::table('download_list')
                        ->where('id', $old_file->id)
                        ->update(['deleted' => 1]);
                    if (!$res) echo 'Not deleted: ' . public_path() . '/' . Config::get('leech.save_to') . '/' . $old_file->id . '_' . $old_file->file_name . "\n";
                }
            }
        })->everyTenMinutes()->sendOutputTo(storage_path() . '/cron/logs.log');
	}

}
