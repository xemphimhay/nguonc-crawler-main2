<?php

namespace KKPhim\Crawler\KKPhimCrawler;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as SP;
use KKPhim\Crawler\KKPhimCrawler\Console\CrawlerScheduleCommand;
use KKPhim\Crawler\KKPhimCrawler\Option;

class KKPhimCrawlerServiceProvider extends SP
{
    /**
     * Get the policies defined on the provider.
     *
     * @return array
     */
    public function policies()
    {
        return [];
    }

    public function register()
    {

        config(['plugins' => array_merge(config('plugins', []), [
            'haiau009/kkphim-crawler' =>
            [
                'name' => 'KKPhim Crawler',
                'package_name' => 'haiau009/kkphim-crawler',
                'icon' => 'la la-hand-grab-o',
                'entries' => [
                    ['name' => 'Crawler', 'icon' => 'la la-hand-grab-o', 'url' => backpack_url('/plugin/kkphim-crawler')],
                    ['name' => 'Option', 'icon' => 'la la-cog', 'url' => backpack_url('/plugin/kkphim-crawler/options')],
                ],
            ]
        ])]);

        config(['logging.channels' => array_merge(config('logging.channels', []), [
            'kkphim-crawler' => [
                'driver' => 'daily',
                'path' => storage_path('logs/haiau009/kkphim-crawler.log'),
                'level' => env('LOG_LEVEL', 'debug'),
                'days' => 7,
            ],
        ])]);

        config(['kkphim.updaters' => array_merge(config('kkphim.updaters', []), [
            [
                'name' => 'KKPhim Crawler',
                'handler' => 'KKPhim\Crawler\KKPhimCrawler\Crawler'
            ]
        ])]);
    }

    public function boot()
    {
        $this->commands([
            CrawlerScheduleCommand::class,
        ]);

        $this->app->booted(function () {
            $this->loadScheduler();
        });

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'kkphim-crawler');
    }

    protected function loadScheduler()
    {
        $schedule = $this->app->make(Schedule::class);
        $schedule->command('kkphim:plugins:kkphim-crawler:schedule')->cron(Option::get('crawler_schedule_cron_config', '*/10 * * * *'))->withoutOverlapping();
    }
}
