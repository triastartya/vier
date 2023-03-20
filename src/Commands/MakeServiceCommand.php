<?php

namespace Viershaka\Vier\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputOption;

class MakeServiceCommand extends GeneratorCommand
{
    protected $type = 'Service';

    protected $name = 'make:service';

    protected $description = 'Create a new service class';

    public function handle()
    {
        parent::handle();
    }

    protected function getStub()
    {
        $model = $this->option('model');

        if (!$model) {
            return __DIR__.'/../stubs/service.plain.stub';
        } else {
            return __DIR__.'/../stubs/service.model.stub';
        }
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Services';
    }

    protected function getOptions()
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Create the class even if it already exists'],
            ['model', null, InputOption::VALUE_OPTIONAL, 'Model of the service']
        ];
    }

    protected function buildClass($name): string
    {
        $stub = $this->files->get($this->getStub());

        return $this->replaceNamespace($stub, $name)
            ->replaceModel($stub, $this->option('model'))
            ->replaceClass($stub, $name);
    }

    protected function replaceModel(&$stub, $model)
    {
        if ($model) {
            $namespace = $this->qualifyModel($model);
    
            $stub = str_replace(['{{ model }}', '{{model}}'], $model, $stub);
            $stub = str_replace(['{{ namespacedModel }}', '{{namespacedModel}}'], $namespace, $stub);
        }

        return $this;
    }
}
