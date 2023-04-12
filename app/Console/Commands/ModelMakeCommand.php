<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\ModelMakeCommand as BaseCommand;
use Illuminate\Support\Str;

class ModelMakeCommand extends BaseCommand
{
    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildClass($name)
    {
        $stub = $this->files->get($this->getStub());

        $class = Str::snake(class_basename($name));
        $class = Str::of($class)->upper();
        return $this
            ->replaceNamespace($stub, $name)
            ->replaceTable($stub, $class)
            ->replaceClass($stub, $name);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('pivot')) {
            return parent::getStub();
        }

        return resource_path('../stubs/model.stub');
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceTable(&$stub, $name)
    {
        $stub = str_replace(
            'DummyTable',
            $name,
            $stub
        );

        return $this;
    }
}
