<?php

namespace Guava\LaravelPopulator;

use Guava\LaravelPopulator\Concerns\HasData;
use Guava\LaravelPopulator\Concerns\HasPipeline;
use Guava\LaravelPopulator\Concerns\Pipe\DefaultsPipe;
use Guava\LaravelPopulator\Concerns\Pipe\GeneratorsPipe;
use Guava\LaravelPopulator\Concerns\Pipe\InsertPipe;
use Guava\LaravelPopulator\Concerns\Pipe\MutatorsPipe;
use Guava\LaravelPopulator\Concerns\Pipe\RelatedPipe;
use Guava\LaravelPopulator\Concerns\Pipe\RelationsPipe;
use Guava\LaravelPopulator\Contracts\InteractsWithPipeline;
use Guava\LaravelPopulator\Storage\Memory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * The processor is responsible for processing the samples.
 */
class Processor
{
    use RelationsPipe;
    use DefaultsPipe;
    use MutatorsPipe;
    use GeneratorsPipe;
    use InsertPipe;
    use RelatedPipe;
    use HasData;
    use HasPipeline;

    protected Bundle $bundle;

    protected \SplFileInfo $file;
    protected string $name;

    protected Memory $memory;

    /**
     * Processes the passed data through a set of pipelines.
     *
     * @param array|Collection $data The data to process.
     * @param string $name Name of the current 'process'.
     * @return void
     */
    public function process(array|Collection $data, string $name): void
    {
        $this->data = is_array($data) ? collect($data) : $data;
        $this->name = $name;
        $this->pipeable->processPipeline($this, $this->data);

    }

    /**
     * Attempts to find the primary ID of the specified model's record with the given identifier.
     *
     * @param Model $model
     * @param string $identifier
     * @return int|string|null
     */
    protected function getPrimaryId(Model $model, string $identifier): int|string|null
    {
        $id = $this->bundle->populator->memory->get($model::class, $identifier);

        // Load from memory
        if ($id) {
            return $id;
        }

        // Load from database via primary key
        if (DB::table($model->getTable())->where($model->getKeyName(), $identifier)->first()) {
            return $identifier;
        }

        // Load from database via unique key
        if (str_contains($identifier, ':')) {
            [$key, $value] = explode(':', $identifier, 2);

            // TODO: might return null
            return DB::table($model->getTable())->where($key, $value)->first()->id;
        }

        return null;
    }

    /**
     * Creates an instance of the class.
     */
    public function __construct(Bundle $bundle, ?InteractsWithPipeline $invoker = null)
    {
        $this->bundle = $bundle;
        $this->pipeable = $invoker ?? app(InteractsWithPipeline::class);
        $this->memory = new Memory();
    }

    /**
     * Static factory to create an instance of the class.
     *
     * @param Bundle $bundle
     * @return static
     */
    public static function make(Bundle $bundle): static
    {
        return new static($bundle);
    }

}
