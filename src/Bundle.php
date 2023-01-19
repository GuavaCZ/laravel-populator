<?php

namespace Guava\LaravelPopulator;

use Guava\LaravelPopulator\Concerns\Bundle\HasDefaults;
use Guava\LaravelPopulator\Concerns\Bundle\HasMutators;
use Guava\LaravelPopulator\Concerns\Bundle\HasRecords;
use Guava\LaravelPopulator\Concerns\HasEnvironments;
use Guava\LaravelPopulator\Concerns\HasName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

/**
 * The sample class serves as a blueprint for the model it creates.
 */
class Bundle
{
    use HasName;
    use HasEnvironments;

    use HasDefaults;
    use HasMutators;
    use HasRecords;

    public Model $model;
    public string $table;

    public Populator $populator;

    /**
     * Can be used to set up repeating samples.
     *
     * @return void
     */
    public function setup(): void {}

    /**
     * Parses all samples from the populators directory and attempts to insert them into the database.
     * @param Populator $populator
     * @return void
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

        $path = database_path("populators/{$populator->getName($populator->name)}/{$this->getName($this->model::class)}");

        if (!File::exists($path)) {
            // TODO: write to CLI
            return;
        }

        collect(File::files($path))
            ->each(function (\SplFileInfo $file) {
                $data = include $file->getPathname();
                $name = str($file->getFilename())
                    ->beforeLast('.')
                    ->toString();

                Processor::make($this)
                    ->process($data, $name);
            });
    }

    /**
     * Creates an instance of the class.
     */
    private final function __construct(string $model)
    {
        $this->model = new $model;
        $this->table = $this->model->getTable();
    }


    /**
     * Static factory to create an instance of the class.
     *
     * @param string $model
     * @return static
     */
    public static function make(string $model): static
    {
        return new static($model);
    }
}
