<?php

namespace App\Models;

use Database\Factories\ExerciseFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Exercise extends Model
{
    /** @use HasFactory<ExerciseFactory> */
    use HasFactory;

    use HasTranslations;

    /**
     * @var list<string>
     */
    public array $translatable = ['name', 'instructions'];

    protected $fillable = [
        'type',
        'location',
        'difficulty',
        'muscle_group',
        'sets',
        'reps',
        'duration_minutes',
        'name',
        'instructions',
    ];
}
