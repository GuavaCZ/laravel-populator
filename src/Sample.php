<?php

namespace Guava\LaravelPopulator;

use Closure;
use Guava\LaravelPopulator\Concerns\HasName;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class Sample
{
    use HasName;

    protected Model $model;
    protected string $table;

    protected array $mutators = [];

    private Populator $populator;

    /**
     * Parses all samples from the populators directory and attempts to insert them into the database.
     * @param Populator $populator
     * @return void
     */
    public function handle(Populator $populator): void
    {
        $this->populator = $populator;

        $path = database_path("populators/{$populator->getName()}/{$this->getName($this->model::class)}");

        collect(File::files($path))->each(function (\SplFileInfo $file) {
            $data = include $file->getPathname();
            $this->process($file, collect($data));
        });
    }

    protected function process(\SplFileInfo $file, Collection $data): void
    {
        $name = str($file->getFilename())
            ->beforeLast('.')
            ->toString();

        $data = $this->relationships($name, $data);

        $this->create($name, $data);
    }

    /**
     * Inserts the given data into the database table of the sample's model.
     *
     * Pipes the data through the mutators before inserting it.
     *
     * @param string $name
     * @param Collection $data
     * @return void
     */
    protected function create(string $name, Collection $data): void
    {
        $id = DB::table($this->table)
            ->insertGetId(
                $data
                    ->except(['relationships'])
                    ->when($this->model->usesTimestamps(),
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

        $this->populator->memory->set($this->model::class, $name, $id);
    }

    // TODO: currently this only works for belongsTo relationships
    // TODO: add support for hasMany and hasOne relationships
    // TODO: add support for polymorphic relationships
    // TODO: currently this only works for relationships that have been created before within the same populator
    // TODO: add multiple "steps" - 1) check for existing inside database, 2) check older populators, 3) check current populator
    // TODO: OPTIONAL 4) if nothing else, attempt to create it. For example, if an array is given, then use it to create it. similar to createMany or sometihng like that
    protected function relationships(string $name, Collection $data): Collection
    {
        return $data
            ->except(['relationships'])
            ->when(
                fn() => Arr::exists($data, 'relationships') && !empty($data['relationships']),
                fn(Collection $collection) => $collection->merge(
                    collect($data->get('relationships', []))
                        ->mapWithKeys(function ($primaryKey, $relationshipName) {
                            /** @var BelongsTo $relationship */
                            $relationship = $this->model->$relationshipName();

                            $related = $relationship->getRelated()::class;

                            if ($this->populator->memory->has($related, $primaryKey)) {
                                $value = $this->populator->memory->get($related, $primaryKey);
                            } else {
                                // TODO: Transform $primaryKey using a closure in Populator::samples()
                                // TODO: in order to support for example entering the e-mail of
                                // TODO: a user and getting the id of that user
                                $value = $primaryKey;
                            }

                            return [$relationship->getForeignKeyName() => $value];
                        })
                        ->toArray()
                )
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
        $this->model = new $model;
        $this->table = $this->model->getTable();
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
