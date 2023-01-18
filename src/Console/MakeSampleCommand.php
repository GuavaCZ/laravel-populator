<?php

namespace Guava\LaravelPopulator\Console;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Stringable;
use function str;

class MakeSampleCommand extends GeneratorCommand
{

    protected $name = 'make:sample';

    protected $description = 'Creates a new sample definition class';

    protected $type = 'Sample';

    protected function getStub(): string
    {
        return __DIR__ . '/../../stubs/sample.stub.php';
    }

    protected function getSampleStub(): string {
        return __DIR__ . '/../../stubs/sample.stub.json';
    }

    protected function qualifyClass($name): string
    {
        return parent::qualifyClass(ucwords(str($name)
            ->whenEndsWith('Sample',
                fn(Stringable $str) => $str,
                fn(Stringable $str) => $str->append('Sample')
            ), "\t\r\n\f\v/")
        );
    }

    protected function getDefaultNamespace($rootNamespace): string
    {
        return $rootNamespace . '\Samples';
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

//        $name = str($this->getNameInput())
//            ->beforeLast('Sample')
////            ->append('/user/')
//            ->append('/')
//            ->append(str($this->getSampleStub())
//                ->afterLast('/')
//                ->replace('.stub', '')
//            )
//            ->lower();
//        $path = database_path('populators/' . $name);
//
//        $this->makeDirectory($path);

//        $this->files->put($path, file_get_contents($this->getSampleStub()));

        return true;
    }
}
