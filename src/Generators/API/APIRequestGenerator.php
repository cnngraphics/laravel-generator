<?php

namespace Labolagen\Generator\Generators\API;

use Labolagen\Generator\Common\CommandData;
use Labolagen\Generator\Generators\BaseGenerator;
use Labolagen\Generator\Generators\ModelGenerator;
use Labolagen\Generator\Utils\FileUtil;

class APIRequestGenerator extends BaseGenerator
{
    /** @var CommandData */
    private $commandData;

    /** @var string */
    private $path;

    /** @var string */
    private $storeFileName;

    /** @var string */
    private $updateFileName;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathApiRequest;
        $this->storeFileName = 'Store'.$this->commandData->modelName.'APIRequest.php';
        $this->updateFileName = 'Update'.$this->commandData->modelName.'APIRequest.php';
    }

    public function generate()
    {
        $this->generateStoreRequest();
        $this->generateUpdateRequest();
    }

    private function generateStoreRequest()
    {
        $templateData = get_template('api.request.store_request', 'laravel-generator');

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->storeFileName, $templateData);

        $this->commandData->commandComment("\nStore Request created: ");
        $this->commandData->commandInfo($this->storeFileName);
    }

    private function generateUpdateRequest()
    {
        $modelGenerator = new ModelGenerator($this->commandData);
        $rules = $modelGenerator->generateUniqueRules();
        $this->commandData->addDynamicVariable('$UNIQUE_RULES$', $rules);

        $templateData = get_template('api.request.update_request', 'laravel-generator');

        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        FileUtil::createFile($this->path, $this->updateFileName, $templateData);

        $this->commandData->commandComment("\nUpdate Request created: ");
        $this->commandData->commandInfo($this->updateFileName);
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->storeFileName)) {
            $this->commandData->commandComment('Store API Request file deleted: '.$this->storeFileName);
        }

        if ($this->rollbackFile($this->path, $this->updateFileName)) {
            $this->commandData->commandComment('Update API Request file deleted: '.$this->updateFileName);
        }
    }
}
