<?php namespace RWH\WURFL;

use Illuminate\Support\ServiceProvider;
use WURFL_Configuration_InMemoryConfig;
use WURFL_Configuration_Config;
use WURFL_WURFLManagerFactory;

class WURFLServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('ricardoh/wurfl-laravel', 'wurfl');
        $this->commands('wurfl.build');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerWURFLLibrary();
        $this->registerWURFL();
        $this->registerBuildCommand();
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array('wurfl', 'wurfl.library', 'wurfl.build');
    }

    private function registerWURFL()
    {
        $this->app->singleton('wurfl', function () {
            $wurflLibrary = $this->app->make('wurfl.library');
            $wurfl = new WURFL($wurflLibrary);

            return $wurfl;
        });
    }

    private function registerWURFLLibrary()
    {
        $app = $this->app;

        $this->app->singleton('wurfl.library', function () use ($app) {
            global $_SERVER;

            $version = $app['config']->get('wurfl::version');
            $path = $app['config']->get('wurfl::path');
            $matchMode = $app['config']->get('wurfl::match-mode');

            $database = $path . DIRECTORY_SEPARATOR . 'wurfl-' . $version . '.zip';
            $cachePath = $path . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $version;

            $wurflConfig = new WURFL_Configuration_InMemoryConfig();
            $wurflConfig->wurflFile($database);
            $wurflConfig->persistence(
                'file',
                array(WURFL_Configuration_Config::DIR => $cachePath)
            );
            $wurflConfig->cache('null');
            $wurflConfig->allowReload(false);
            $wurflConfig->matchMode($matchMode);

            $wurflManagerFactory = new WURFL_WURFLManagerFactory($wurflConfig);
            $wurflManager = $wurflManagerFactory->create();
            $wurflInfo = $wurflManager->getWURFLInfo();
            $wurfl = $wurflManager->getDeviceForHttpRequest($_SERVER);

            return $wurfl;
        });
    }

    private function registerBuildCommand()
    {
        $this->app->bind('wurfl.build', 'RWH\WURFL\WURFLBuildCommand');
    }
}
