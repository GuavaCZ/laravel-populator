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

    public array $mutators = [];
    public Populator $populator;

//    protected Processor $processor;

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

        $path = database_path("populators/{$populator->getName($populator->name)}/{$this->getName($this->model::class)}");

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
