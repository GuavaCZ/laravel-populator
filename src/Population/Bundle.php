<?php

namespace Guava\LaravelPopulator\Population;

use Closure;
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

    public Model $model;
    public string $table;

    public array $records = [];
    public array $mutators = [];
    public array $defaults = [];
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
     * Mutates the specified attribute using the given callback.
     *
     * @param string $attribute Attribute to mutate.
     * @param Closure $closure Callback to run on the attribute.
     * @return $this
     */
    public function mutate(string $attribute, Closure $closure): static
    {
        $this->mutators[$attribute] = $closure;

        return $this;
    }

    /**
     * Adds default data to the specified attribute.
     *
     * @param string $attribute Attribute for default data.
     * @param mixed $closure Callback to run on the attribute.
     * @return $this
     */
    public function default(string $attribute, mixed $closure): static
    {
        $this->defaults[$attribute] = $closure;

        return $this;
    }

    /**
     * Adds a record to the bundle for population.
     *
     * @param string $key Key to access the record by from other records.
     * @param array|Closure $record Data to populate the record with or closure returning the data.
     * @return static
     */
    public function record(string $key, array|Closure $record): static
    {
        $this->records[$key] = is_callable($record) ? $record() : $record;

        return $this;
    }

    /**
     * Adds an array of records to the bundle for population.
     *
     * @param array|Closure $records Records to populate the bundle with or closure returning the records.
     * @return static
     */
    public function records(array|Closure $records): static
    {
        $this->records = is_callable($records) ? $records() : $records;

        return $this;
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
