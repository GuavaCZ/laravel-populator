<?php

namespace Guava\LaravelPopulator\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Stringable;
use function str;

class MakePopulatorCommand extends GeneratorCommand
{

    protected $name = 'make:populator';

    protected $description = 'Creates a new populator definition class';

    protected $type = 'Populator';

    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/populator.stub.php';
    }

    protected function getSampleStub(): string {
        return __DIR__ . '/../../stubs/sample.stub.json';
    }

    protected function qualifyClass($name): string
    {
        return parent::qualifyClass(ucwords(str($name)
            ->whenEndsWith('Populator',
                fn(Stringable $str) => $str,
                fn(Stringable $str) => $str->append('Populator')
            ), "\t\r\n\f\v/")
        );
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Populators';
    }

    /**
     * Creates the files for the populator.
     *
     * @return bool
     * @throws FileNotFoundException
     */
    public function handle(): bool
    {
        parent::handle();

        $name = str($this->getNameInput())
            ->beforeLast('Populator')
//            ->append('/user/')
            ->append('/')
            ->append(str($this->getSampleStub())
                ->afterLast('/')
                ->replace('.stub', '')
            )
            ->lower();
        $path = database_path('populators/' . $name);

        $this->makeDirectory($path);

//        $this->files->put($path, file_get_contents($this->getSampleStub()));

        return true;
    }
}
