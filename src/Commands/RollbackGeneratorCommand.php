<?php

namespace Labolagen\Generator\Commands;

use Illuminate\Console\Command;
use Labolagen\Generator\Common\CommandData;
use Labolagen\Generator\Generators\API\APIControllerGenerator;
use Labolagen\Generator\Generators\API\APIRequestGenerator;
use Labolagen\Generator\Generators\API\APIRoutesGenerator;
use Labolagen\Generator\Generators\API\APITestGenerator;
use Labolagen\Generator\Generators\FactoryGenerator;
use Labolagen\Generator\Generators\MigrationGenerator;
use Labolagen\Generator\Generators\ModelGenerator;
use Labolagen\Generator\Generators\RepositoryGenerator;
use Labolagen\Generator\Generators\RepositoryTestGenerator;
use Labolagen\Generator\Generators\Scaffold\BreadcrumbGenerator;
use Labolagen\Generator\Generators\Scaffold\ControllerGenerator;
use Labolagen\Generator\Generators\Scaffold\LocaleGenerator;
use Labolagen\Generator\Generators\Scaffold\MenuGenerator;
use Labolagen\Generator\Generators\Scaffold\RequestGenerator;
use Labolagen\Generator\Generators\Scaffold\RoutesGenerator;
use Labolagen\Generator\Generators\Scaffold\ViewGenerator;
use Labolagen\Generator\Generators\VueJs\ControllerGenerator as VueJsControllerGenerator;
use Labolagen\Generator\Generators\VueJs\ModelJsConfigGenerator;
use Labolagen\Generator\Generators\VueJs\RoutesGenerator as VueJsRoutesGenerator;
use Labolagen\Generator\Generators\VueJs\ViewGenerator as VueJsViewGenerator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class RollbackGeneratorCommand extends Command
{
    /**
     * The command Data.
     *
     * @var CommandData
     */
    public $commandData;
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'labolagen:rollback';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Rollback a full CRUD API and Scaffold for given model';

    /**
     * @var Composer
     */
    public $composer;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();

        $this->composer = app()['composer'];
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        if (!in_array($this->argument('type'), [
            CommandData::$COMMAND_TYPE_API,
            CommandData::$COMMAND_TYPE_SCAFFOLD,
            CommandData::$COMMAND_TYPE_API_SCAFFOLD,
            CommandData::$COMMAND_TYPE_VUEJS,
        ])) {
            $this->error('invalid rollback type');
        }

        $this->commandData = new CommandData($this, $this->argument('type'));
        $this->commandData->config->mName = $this->commandData->modelName = $this->argument('model');

        $this->commandData->config->init($this->commandData, ['tableName', 'prefix', 'plural', 'views']);

        $views = $this->commandData->getOption('views');
        if (!empty($views)) {
            $views = explode(',', $views);
            $viewGenerator = new ViewGenerator($this->commandData);
            $viewGenerator->rollback($views);

            $this->info('Generating autoload files');
            $this->composer->dumpOptimized();

            return;
        }

        $migrationGenerator = new MigrationGenerator($this->commandData);
        $migrationGenerator->rollback();

        $modelGenerator = new ModelGenerator($this->commandData);
        $modelGenerator->rollback();

        $repositoryGenerator = new RepositoryGenerator($this->commandData);
        $repositoryGenerator->rollback();

        $requestGenerator = new APIRequestGenerator($this->commandData);
        $requestGenerator->rollback();

        $controllerGenerator = new APIControllerGenerator($this->commandData);
        $controllerGenerator->rollback();

        $routesGenerator = new APIRoutesGenerator($this->commandData);
        $routesGenerator->rollback();

        $requestGenerator = new RequestGenerator($this->commandData);
        $requestGenerator->rollback();

        $controllerGenerator = new ControllerGenerator($this->commandData);
        $controllerGenerator->rollback();

        $viewGenerator = new ViewGenerator($this->commandData);
        $viewGenerator->rollback();

        $routeGenerator = new RoutesGenerator($this->commandData);
        $routeGenerator->rollback();

        $controllerGenerator = new VueJsControllerGenerator($this->commandData);
        $controllerGenerator->rollback();

        $routesGenerator = new VueJsRoutesGenerator($this->commandData);
        $routesGenerator->rollback();

        $viewGenerator = new VueJsViewGenerator($this->commandData);
        $viewGenerator->rollback();

        $modelJsConfigGenerator = new ModelJsConfigGenerator($this->commandData);
        $modelJsConfigGenerator->rollback();

        $factoryGenerator = new FactoryGenerator($this->commandData);
        $factoryGenerator->rollback();

        $localeGenerator = new LocaleGenerator($this->commandData);
        $localeGenerator->rollback();

        $breadCrumbGenerator = new BreadCrumbGenerator($this->commandData);
        $breadCrumbGenerator->rollback();

        if ($this->commandData->getAddOn('tests')) {
            $repositoryTestGenerator = new RepositoryTestGenerator($this->commandData);
            $repositoryTestGenerator->rollback();

            $apiTestGenerator = new APITestGenerator($this->commandData);
            $apiTestGenerator->rollback();
        }

        if ($this->commandData->config->getAddOn('menu.enabled')) {
            $menuGenerator = new MenuGenerator($this->commandData);
            $menuGenerator->rollback();
        }

        $this->info('Generating autoload files');
        $this->composer->dumpOptimized();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    public function getOptions()
    {
        return [
            ['tableName', null, InputOption::VALUE_REQUIRED, 'Table Name'],
            ['prefix', null, InputOption::VALUE_REQUIRED, 'Prefix for all files'],
            ['plural', null, InputOption::VALUE_REQUIRED, 'Plural Model name'],
            ['views', null, InputOption::VALUE_REQUIRED, 'Views to rollback'],
        ];
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['model', InputArgument::REQUIRED, 'Singular Model name'],
            ['type', InputArgument::REQUIRED, 'Rollback type: (api / scaffold / api_scaffold)'],
        ];
    }
}
