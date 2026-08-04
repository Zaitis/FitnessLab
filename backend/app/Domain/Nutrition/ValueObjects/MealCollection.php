<?php

namespace App\Domain\Nutrition\ValueObjects;

use ArrayIterator;
use Countable;
use IteratorAggregate;

/**
 * @implements IteratorAggregate<int, CatalogueMeal>
 */
final readonly class MealCollection implements Countable, IteratorAggregate
{
    /**
     * @param  list<CatalogueMeal>  $items
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
     * @return ArrayIterator<int, CatalogueMeal>
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * The item at $index, cycling around the collection — deterministic,
     * no randomness, the same reasoning as ExerciseCollection::nth().
     */
    public function nth(int $index): ?CatalogueMeal
    {
        if ($this->isEmpty()) {
            return null;
        }

        return $this->items[$index % count($this->items)];
    }

    /**
     * A new collection with the same items ordered by how close their
     * calories are to $targetCalories — closest first. Combined with
     * nth(), this is what lets a strategy pick a meal that's both
     * deterministic (cycles through candidates day by day) and
     * calorie-appropriate (the candidates are the closest matches), without
     * needing an exact-match optimiser.
     */
    public function sortedByProximityTo(int $targetCalories): self
    {
        $items = $this->items;

        usort($items, fn (CatalogueMeal $a, CatalogueMeal $b) => abs($a->calories - $targetCalories) <=> abs($b->calories - $targetCalories));

        return new self($items);
    }
}
