<?php

namespace Guava\LaravelPopulator;

use Closure;
use Exception;
use Guava\LaravelPopulator\Concerns\Bundle\HasDefaults;
use Guava\LaravelPopulator\Concerns\Bundle\HasGenerators;
use Guava\LaravelPopulator\Concerns\Bundle\HasMutators;
use Guava\LaravelPopulator\Concerns\Bundle\HasRecords;
use Guava\LaravelPopulator\Concerns\HasEnvironments;
use Guava\LaravelPopulator\Concerns\HasName;
use Guava\LaravelPopulator\Concerns\HasPipeline;
use Guava\LaravelPopulator\Contracts\InteractsWithBundleInsert;
use Guava\LaravelPopulator\Contracts\InteractsWithPipeline;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * The sample class serves as a blueprint for the model it creates.
 */
class Bundle
{
    use HasDefaults;
    use HasEnvironments;
    use HasGenerators;
    use HasMutators;
    use HasName;
    use HasPipeline;
    use HasRecords;

    public Model $model;

    public string $table;

    public Populator $populator;

    protected ?Closure $makeProcessorUsing = null;

    protected null | Closure | InteractsWithPipeline $performInsertUsing = null;

    /**
     * Parses all samples from the populators directory and attempts to insert them into the database.
     *
     * @throws Exception
     */
    public function handle(Populator $populator): void
    {
        if (! $this->checkEnvironment()) {
            return;
        }

        $this->populator = $populator;

        if (! empty($this->records)) {
            collect($this->records)
                ->each(function (array $record, $key) {
                    $this->makeProcessor()
                        ->pipeableUsing($this->populator->getPipeable() ?? $this->getPipeable())
                        ->process($record, $key)
                    ;
                })
            ;

            return;
        }

        $singular = $this->getName($this->model::class);
        $plural = Str::plural($singular);
        $paths = collect([
            database_path("populators/{$populator->getName()}/{$plural}"),
            database_path("populators/{$populator->getName()}/{$singular}"),
        ]);

        $found = $paths
            ->filter(fn ($path) => File::exists($path))
            ->first()
        ;
        if ($found) {
            collect(File::files($found))
                ->each(function (\SplFileInfo $file) {
                    $data = include $file->getPathname();
                    $name = str($file->getFilename())
                        ->beforeLast('.')
                        ->toString()
                    ;

                    $this->makeProcessor()
                        ->pipeableUsing($this->getPipeable() ?? $this->populator->getPipeable())
                        ->process($data, $name)
                    ;
                })
            ;
        } else {
            $modelName = $this->model::class;
            throw new Exception("A directory for the bundle of '$modelName' does not exist. Please make sure to create one of the following directories: \n" . $paths->map(fn ($path) => str($path)->prepend("\t - "))->implode("\n"));
        }
    }

    /**
     * Custom @Processor instantiation by closure
     *
     * @param  Closure():Processor|null  $closure
     * @return $this
     */
    public function makeProcessorUsing(?Closure $closure): static
    {
        $this->makeProcessorUsing = $closure;

        return $this;
    }

    /**
     * Instantiates @return Processor
     *
     * @see Processor to process the bundle
     */
    protected function makeProcessor(): Processor
    {
        if ($factory = $this->makeProcessorUsing) {
            $processor = $factory($this);
        } else {
            $processor = Processor::make($this);
        }

        return $processor->performInsertUsing($this->performInsertUsing);
    }

    /**
     * Custom @Model insertion by closure which needs to return the key for the model, typically by
     * `getKey()` operation on the model.
     *
     * @param  InteractsWithBundleInsert|Closure(array<string,scalar>,Bundle):(string|int)|null  $closure
     * @return $this
     */
    public function performInsertUsing(null | InteractsWithBundleInsert | Closure $closure): static
    {
        $this->performInsertUsing = $closure;

        return $this;
    }

    /**
     * Creates an instance of the class.
     */
    final private function __construct(string $model, ?string $name = null)
    {
        $this->model = new $model;
        $this->name = $name;
        $this->table = $this->model->getTable();
    }

    /**
     * Static factory to create an instance of the class.
     */
    public static function make(string $model, ?string $name = null): static
    {
        return new static($model, $name);
    }
}
