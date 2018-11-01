<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')
        //          ->hourly();
        $schedule->call(function () {
            $now_date = date("Y-m-d");
            $day_before = date("Y-m-d H:i:s", strtotime("$now_date  -45 days"));
            $notifications = DB::table('notifications')->where('created_at', '<=', $day_before)->get();
            foreach ($notifications as $notification) {
                $images = DB::table('images')->where('type', '=', 'notification')->where('parent_id', '=', $notification->id)->get();
                if ($images) {
                    foreach ($images as $image) {
                        if(File::exists('images/notifications/' . $image->url))
                            File::delete('images/notifications/' . $image->url);
                        DB::table('images')->where('id', '=', $image->id)->delete();
                    }
                }
                $comments = DB::table('comments')->where('notification_id', '=', $notification->id)->get();
                if ($comments) {
                    foreach ($comments as $comment) {
                        $images = DB::table('images')->where('type', '=', 'comment')->where('parent_id', '=', $comment->id)->get();
                        if ($images) {
                            foreach ($images as $image) {
                                if(File::exists('images/notifications/' . $image->url))
                                    File::delete('images/notifications/' . $image->url);
                                DB::table('images')->where('id', '=', $image->id)->delete();
                            }
                        }
                        DB::table('comments')->where('id', '=', $comment->id)->delete();
                    }
                }
                DB::table('notifications')->where('id', '=', $notification->id)->delete();
            }
        })->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
