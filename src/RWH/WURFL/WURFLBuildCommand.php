<?php namespace RWH\WURFL;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use WURFL_Configuration_InMemoryConfig;
use WURFL_UserAgentHandlerChainFactory;
use WURFL_DeviceRepositoryBuilder;
use WURFL_Configuration_Config;
use WURFL_Xml_DevicePatcher;
use WURFL_Storage_Factory;
use WURFL_Context;
use Config;

class WURFLBuildCommand extends Command
{
    protected $name = 'wurfl:build';
    protected $description = 'Build cache for specific WURFL version';

    public function fire()
    {
        $version = $this->argument('version');
        $path = Config::get('wurfl::path');

        $database = $path . DIRECTORY_SEPARATOR . 'wurfl-' . $version . '.zip';
        $cachePath = $path . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . $version;

        $this->info("Database: {$database}");
        $this->info("Cache: {$cachePath}");

        if (!file_exists($database)) {
            $this->error("{$database} doesnt exists");
            exit;
        }

        @mkdir($cachePath, $mode = 0777, true);

        $wurflConfig = new WURFL_Configuration_InMemoryConfig();
        $wurflConfig->wurflFile($database);
        $wurflConfig->persistence(
            'file',
            array(WURFL_Configuration_Config::DIR => $cachePath)
        );
        $wurflConfig->cache('null');

        $persistenceStorage = WURFL_Storage_Factory::create($wurflConfig->persistence);
        $context = new WURFL_Context($persistenceStorage);
        $userAgentHandlerChain = WURFL_UserAgentHandlerChainFactory::createFrom($context);

        $devicePatcher = new WURFL_Xml_DevicePatcher();
        $deviceRepositoryBuilder = new WURFL_DeviceRepositoryBuilder($persistenceStorage, $userAgentHandlerChain, $devicePatcher);

        $deviceRepositoryBuilder->build($wurflConfig->wurflFile, $wurflConfig->wurflPatches);

        $this->info('Cache built');
    }

    protected function getArguments()
    {
        return [
            ['version', InputArgument::REQUIRED, 'WURFL database version'],
        ];
    }
}
