<?php

namespace Guava\LaravelPopulator;

use Exception;
use Guava\LaravelPopulator\Concerns\Bundle\HasDefaults;
use Guava\LaravelPopulator\Concerns\Bundle\HasGenerators;
use Guava\LaravelPopulator\Concerns\Bundle\HasMutators;
use Guava\LaravelPopulator\Concerns\Bundle\HasRecords;
use Guava\LaravelPopulator\Concerns\HasEnvironments;
use Guava\LaravelPopulator\Concerns\HasName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * The sample class serves as a blueprint for the model it creates.
 */
class Bundle
{
    use HasName;
    use HasEnvironments;

    use HasDefaults;
    use HasMutators;
    use HasGenerators;
    use HasRecords;

    public Model $model;
    public string $table;

    public Populator $populator;

    /**
     * Can be used to set up repeating samples.
     *
     * @return void
     */
    public function setup(): void
    {
    }

    /**
     * Parses all samples from the populators directory and attempts to insert them into the database.
     * @param Populator $populator
     * @return void
     * @throws Exception
     */
    public function handle(Populator $populator): void
    {
        if (!$this->checkEnvironment()) {
            return;
        }

        $this->populator = $populator;

        if (!empty($this->records)) {
            collect($this->records)
                ->each(function (array $record, $key) {
                    $modelName = $this->model::class;
                    Processor::make($this)
                        ->process($record, is_int($key) ? "{$modelName}-$key" : $key);
                });

            return;
        }

        $singular = $this->getName($this->model::class);
        $plural = Str::plural($singular);
        $paths = collect([
            database_path("populators/{$populator->getName()}/{$plural}"),
            database_path("populators/{$populator->getName()}/{$singular}"),
        ]);

        $found = false;

        $paths
            ->filter(fn($path) => File::exists($path))
            ->each(function ($path) use (&$found) {
                if ($found) return;
                $found = true;

                collect(File::files($path))
                    ->each(function (\SplFileInfo $file) {
                        $data = include $file->getPathname();
                        $name = str($file->getFilename())
                            ->beforeLast('.')
                            ->toString();

                        Processor::make($this)
                            ->process($data, $name);
                    });
        });

        if (!$found) {
            $modelName = $this->model::class;
            throw new Exception("A directory for the bundle of '$modelName' does not exist. Please make sure to create one of the following directories: \n" . $paths->map(fn($path) => str($path)->prepend("\t - "))->implode("\n"));
        }
    }

    /**
     * Creates an instance of the class.
     */
    private final function __construct(string $model, string $name = null)
    {
        $this->model = new $model;
        $this->name = $name;
        $this->table = $this->model->getTable();
    }


    /**
     * Static factory to create an instance of the class.
     *
     * @param string $model
     * @param string|null $name
     * @return static
     */
    public static function make(string $model, string $name = null): static
    {
        return new static($model, $name);
    }
}
