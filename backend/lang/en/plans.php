<?php

/*
|--------------------------------------------------------------------------
| Plan Export Strings
|--------------------------------------------------------------------------
|
| Backend-only copy for the one surface the server renders itself: the PDF
| export. The frontend has its own react-i18next copy for the dashboard UI
| — these mirror it rather than share it, since a server-rendered PDF has
| no access to the frontend's translation resources.
|
*/

return [

    'day' => 'Day :day',

    'workout_summary' => ':level · :days days a week',

    // :protein/:fat/:carbs are passed in already formatted as "180g" (see
    // ExportPlanToPdfAction) -- the unit is baked into the value rather than
    // this template, so the placeholder can't accidentally swallow a
    // trailing "g" as part of its own name.
    'nutrition_summary' => ':calories kcal a day · :protein protein · :fat fat · :carbs carbs',

    'sets_reps' => ':sets × :reps',

    'duration_minutes' => ':minutes min',

    'calories_suffix' => ':calories kcal',

];
