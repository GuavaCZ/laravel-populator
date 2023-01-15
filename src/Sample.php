<?php

namespace Guava\LaravelPopulator;

use Closure;
use Guava\LaravelPopulator\Concerns\HasName;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class Sample
{
    use HasName;

    protected string $model;

    protected array $mutators = [];

    /**
     * Parses all samples from the populators directory and attempts to insert them into the database.
     * @param Populator $populator
     * @return void
     */
    public function handle(Populator $populator): void
    {
        $path = database_path("populators/{$populator->getName()}/{$this->getName($this->model)}");

        collect(File::files($path))->each(function (\SplFileInfo $file) {
            $data = include $file->getPathname();
            $this->create($data);
        });


    }

    /**
     * Inserts the given data into the database table of the sample's model.
     *
     * Pipes the data through the mutators before inserting it.
     *
     * @param array $data
     * @return void
     */
    protected function create(array $data): void
    {
        $model = new $this->model;
        $table = $model->getTable();

        DB::table($table)
            ->insert(
                collect($data)
                    ->when($model->usesTimestamps(),
                        fn(Collection $collection) => $collection->merge([
                            'created_at' => now(),
                            'updated_at' => now(),
                        ])
                    )
                    ->when(fn() => !empty($this->mutators),
                        fn(Collection $collection) => $collection->map(function ($value, $key) {
                            return Arr::exists($this->mutators, $key) ? $this->mutators[$key]($value) : $value;
                        }))
                    ->toArray()
            );
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
     * Creates an instance of the class.
     */
    private final function __construct(string $model)
    {
        $this->model = $model;
    }


    /**
     * Static factory to create an instance of the class.
     *
     * @return static
     */
    public static function make(string $model): static
    {
        return new static($model);
    }
}
