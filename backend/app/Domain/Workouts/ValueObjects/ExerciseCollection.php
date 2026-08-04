<?php

namespace App\Domain\Workouts\ValueObjects;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, CatalogueExercise>
 */
final readonly class ExerciseCollection implements Countable, IteratorAggregate
{
    /**
     * @param  list<CatalogueExercise>  $items
     */
    public function __construct(private array $items) {}

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return list<CatalogueExercise>
     */
    public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @return ArrayIterator<int, CatalogueExercise>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * The item at $index, cycling around the collection — this is what lets
     * a strategy assign a different exercise to each day without repeating
     * the same one every time, deterministically (no randomness).
     */
    public function nth(int $index): ?CatalogueExercise
    {
        if ($this->isEmpty()) {
            return null;
        }

        return $this->items[$index % count($this->items)];
    }
}
