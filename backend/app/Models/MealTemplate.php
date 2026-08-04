<?php

namespace App\Models;

use Database\Factories\MealTemplateFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class MealTemplate extends Model
{
    /** @use HasFactory<MealTemplateFactory> */
    use HasFactory;

    use HasTranslations;

    /**
     * @var list<string>
     */
    public array $translatable = ['name', 'description'];

    protected $fillable = [
        'meal_time',
        'calories',
        'protein_g',
        'fat_g',
        'carbs_g',
        'name',
        'description',
    ];
}
