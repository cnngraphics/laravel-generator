<?php

namespace Labolagen\Generator\Generators\Vuejs;

use Illuminate\Support\Str;
use Labolagen\Generator\Common\CommandData;
use Labolagen\Generator\Generators\BaseGenerator;

class RoutesGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $pathApi;

    /** @var string */
    private $routeContents;

    /** @var string */
    private $apiRouteContents;

    /** @var string */
    private $routesTemplate;

    /** @var string */
    private $apiRoutesTemplate;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->pathApi = $commandData->config->pathApiRoutes.DIRECTORY_SEPARATOR.$this->commandData->config->mSnakePlural.'.php';
        $this->path = $commandData->config->pathRoutes.DIRECTORY_SEPARATOR.$this->commandData->config->mSnakePlural.'.php';
        if (!file_exists($this->pathApi)) {
            file_put_contents($this->pathApi, get_template('vuejs.routes.api_routes', 'laravel-generator'));
        }

        $this->apiRouteContents = file_get_contents($this->pathApi);

        $routesTemplate = get_template('vuejs.routes.routes', 'laravel-generator');
        $apiRoutesTemplate = get_template('vuejs.routes.api_routes_base', 'laravel-generator');
        $this->routesTemplate = fill_template($this->commandData->dynamicVars, $routesTemplate);
        $this->apiRoutesTemplate = fill_template($this->commandData->dynamicVars, $apiRoutesTemplate);
    }

    public function generate()
    {
        $this->routeContents = file_get_contents($this->path);
        $this->routeContents .= "\n\n".$this->routesTemplate;
        $this->apiRouteContents .= "\n\n".$this->apiRoutesTemplate;

        file_put_contents($this->path, $this->routeContents);
        file_put_contents($this->pathApi, $this->apiRouteContents);

        $this->commandData->commandComment("\n".$this->commandData->config->mCamelPlural.' routes added.');
    }

    public function rollback()
    {
        if (file_exists($this->path)) {
            unlink($this->path);
            $this->commandData->commandComment('vuejs routes deleted');
        }

        if (file_exists($this->pathApi)) {
            unlink($this->pathApi);
            $this->commandData->commandComment('vuejs api routes deleted');
        }
    }
}
