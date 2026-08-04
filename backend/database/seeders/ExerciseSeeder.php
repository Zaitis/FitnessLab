<?php

namespace Database\Seeders;

use App\Models\Exercise;
use Illuminate\Database\Seeder;

/**
 * Rules as data, not code (docs/DESIGN-PATTERNS.md §7) — the training plan
 * strategies never hard-code exercises, they filter this catalogue.
 *
 * Every muscle group carries at least two beginner-difficulty exercises per
 * location (gym, home), which is what guarantees the plan strategies always
 * have distinct exercises to pick from regardless of the requested
 * experience level. A handful of intermediate/advanced entries exist purely
 * to exercise (pun intended) the difficulty filter.
 */
class ExerciseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->exercises() as $exercise) {
            Exercise::create($exercise);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function exercises(): array
    {
        return [
            // --- Gym strength ---
            $this->strength('gym', 'beginner', 'chest', 4, 8,
                'Bench Press', 'Wyciskanie sztangi leżąc',
                'Lie on a flat bench and press the bar up from chest height.',
                'Połóż się na płaskiej ławce i wyciśnij sztangę znad klatki piersiowej.'),
            $this->strength('gym', 'beginner', 'chest', 3, 10,
                'Incline Dumbbell Press', 'Wyciskanie hantli na ławce skośnej',
                'Press dumbbells upward from shoulder height on an inclined bench.',
                'Wyciskaj hantle znad barków, siedząc na ławce ustawionej pod kątem.'),
            $this->strength('gym', 'beginner', 'back', 3, 12,
                'Lat Pulldown', 'Ściąganie drążka wyciągu górnego',
                'Pull the bar down to chest height, squeezing your shoulder blades together.',
                'Ściągnij drążek do wysokości klatki piersiowej, ściskając łopatki.'),
            $this->strength('gym', 'beginner', 'back', 3, 12,
                'Seated Cable Row', 'Wiosłowanie wyciągiem dolnym',
                'Pull the handle toward your torso while keeping your back straight.',
                'Przyciągaj uchwyt do tułowia, utrzymując proste plecy.'),
            $this->strength('gym', 'advanced', 'back', 4, 5,
                'Deadlift', 'Martwy ciąg',
                'Lift the bar from the floor to hip level, keeping your back neutral throughout.',
                'Podnieś sztangę z podłogi do wysokości bioder, utrzymując neutralny odcinek pleców.'),
            $this->strength('gym', 'beginner', 'legs', 3, 12,
                'Leg Press', 'Wyciskanie nóg na suwnicy',
                'Push the platform away by extending your knees, without locking them out.',
                'Wypchnij platformę, prostując kolana, bez ich blokowania.'),
            $this->strength('gym', 'beginner', 'legs', 3, 12,
                'Goblet Squat', 'Przysiad z hantlą (goblet)',
                'Hold a dumbbell at chest height and squat down until your thighs are parallel to the floor.',
                'Trzymaj hantlę przy klatce piersiowej i zejdź w przysiad do momentu, gdy uda są równoległe do podłogi.'),
            $this->strength('gym', 'intermediate', 'legs', 4, 8,
                'Barbell Back Squat', 'Przysiad ze sztangą',
                'Rest the bar across your upper back and squat down, keeping your chest up.',
                'Oprzyj sztangę na górnej części pleców i zejdź w przysiad, utrzymując wyprostowaną klatkę piersiową.'),
            $this->strength('gym', 'beginner', 'shoulders', 3, 8,
                'Overhead Press', 'Wyciskanie żołnierskie',
                'Press the bar overhead from shoulder height until your arms are fully extended.',
                'Wyciśnij sztangę znad barków do pełnego wyprostu ramion.'),
            $this->strength('gym', 'beginner', 'shoulders', 3, 12,
                'Lateral Raise', 'Unoszenie hantli bokiem',
                'Raise dumbbells out to your sides until your arms are level with your shoulders.',
                'Unieś hantle bokiem do wysokości barków.'),
            $this->strength('gym', 'beginner', 'arms', 3, 12,
                'Dumbbell Bicep Curl', 'Uginanie ramion z hantlami',
                'Curl the dumbbells toward your shoulders, keeping your elbows still.',
                'Uginaj ramiona z hantlami w kierunku barków, trzymając łokcie nieruchomo.'),
            $this->strength('gym', 'beginner', 'arms', 3, 12,
                'Cable Tricep Pushdown', 'Prostowanie ramion na wyciągu górnym',
                'Push the bar down until your arms are fully extended, keeping elbows close to your body.',
                'Prostuj ramiona w dół do pełnego wyprostu, trzymając łokcie blisko tułowia.'),
            $this->strength('gym', 'beginner', 'core', 3, 15,
                'Cable Crunch', 'Brzuszki na wyciągu górnym',
                'Kneel facing the cable machine and curl your torso down toward your knees.',
                'Klęcząc przodem do wyciągu, zegnij tułów w kierunku kolan.'),
            $this->strength('gym', 'intermediate', 'core', 3, 12,
                'Hanging Leg Raise', 'Unoszenie nóg w zwisie',
                'Hang from a bar and raise your legs until they are parallel to the floor.',
                'Zwisając na drążku, unieś nogi do poziomu równoległego z podłogą.'),

            // --- Home strength ---
            $this->strength('home', 'beginner', 'chest', 3, 12,
                'Push-up', 'Pompka',
                'Lower your chest to the floor and push back up, keeping your body in a straight line.',
                'Opuść klatkę piersiową w kierunku podłogi i wypchnij się z powrotem, utrzymując proste ciało.'),
            $this->strength('home', 'beginner', 'chest', 3, 15,
                'Incline Push-up', 'Pompka z rękami na podwyższeniu',
                'Perform a push-up with your hands elevated on a sturdy surface for an easier variation.',
                'Wykonaj pompkę z rękami opartymi na podwyższeniu — łatwiejszy wariant ćwiczenia.'),
            $this->strength('home', 'intermediate', 'chest', 3, 10,
                'Diamond Push-up', 'Pompka diamentowa',
                'Perform a push-up with your hands close together, forming a diamond shape.',
                'Wykonaj pompkę z dłońmi blisko siebie, ułożonymi w kształt diamentu.'),
            $this->strength('home', 'beginner', 'back', 3, 15,
                'Superman', 'Superman (unoszenie tułowia leżąc)',
                'Lie face down and simultaneously raise your arms, chest, and legs off the floor.',
                'Leżąc na brzuchu, jednocześnie unieś ręce, klatkę piersiową i nogi nad podłogę.'),
            $this->strength('home', 'beginner', 'back', 3, 12,
                'Bird Dog', 'Bird dog (naprzemienne wyprosty)',
                'From all fours, extend one arm and the opposite leg, then switch sides.',
                'W podporze klęcznym wyprostuj jedną rękę i przeciwną nogę, następnie zmień stronę.'),
            $this->strength('home', 'beginner', 'legs', 3, 15,
                'Bodyweight Squat', 'Przysiad z masą własnego ciała',
                'Squat down until your thighs are parallel to the floor, then stand back up.',
                'Zejdź w przysiad do momentu, gdy uda są równoległe do podłogi, następnie wstań.'),
            $this->strength('home', 'beginner', 'legs', 3, 12,
                'Walking Lunge', 'Wykrok chodzony',
                'Step forward into a lunge, alternating legs as you move across the room.',
                'Wykonuj wykroki naprzemiennie, przemieszczając się do przodu.'),
            $this->strength('home', 'advanced', 'legs', 3, 6,
                'Pistol Squat', 'Przysiad pistoletowy (na jednej nodze)',
                'Squat down on one leg while keeping the other leg extended in front of you.',
                'Wykonaj przysiad na jednej nodze, trzymając drugą wyprostowaną przed sobą.'),
            $this->strength('home', 'beginner', 'shoulders', 3, 10,
                'Pike Push-up', 'Pompka w podporze na rękach (pike)',
                'From a downward-dog position, bend your elbows to lower your head toward the floor.',
                'Z pozycji "psa z głową w dół" ugnij łokcie, opuszczając głowę w kierunku podłogi.'),
            $this->strength('home', 'beginner', 'shoulders', 3, 15,
                'Y-Raise', 'Unoszenie ramion w literę Y',
                'Lying face down, raise your arms overhead in a Y shape.',
                'Leżąc na brzuchu, unieś ręce nad głową, układając ciało w literę Y.'),
            $this->strength('home', 'beginner', 'arms', 3, 12,
                'Tricep Dip', 'Pompki na triceps (dipy na krześle)',
                'Using a sturdy chair, lower your body by bending your elbows, then push back up.',
                'Opierając się o stabilne krzesło, opuść ciało uginając łokcie, następnie wypchnij się z powrotem.'),
            $this->strength('home', 'beginner', 'arms', 3, 15,
                'Resistance Band Bicep Curl', 'Uginanie ramion z gumą oporową',
                'Stand on a resistance band and curl the handles toward your shoulders.',
                'Stań na gumie oporowej i uginaj uchwyty w kierunku barków.'),
            $this->strength('home', 'beginner', 'core', 3, 20,
                'Bicycle Crunch', 'Rowerek (brzuszki naprzemienne)',
                'Lying on your back, alternate bringing each elbow to the opposite knee.',
                'Leżąc na plecach, naprzemiennie przyciągaj łokieć do przeciwnego kolana.'),
            $this->strength('home', 'beginner', 'core', 3, 20,
                'Mountain Climbers', 'Wspinacz górski',
                'From a plank position, quickly alternate driving your knees toward your chest.',
                'W podporze przodem szybko naprzemiennie przyciągaj kolana do klatki piersiowej.'),

            // --- Cardio ---
            $this->cardio('outdoor', 'beginner', 30,
                'Walking', 'Spacer',
                'A brisk walk outdoors at a pace that raises your heart rate.',
                'Energiczny spacer na świeżym powietrzu w tempie podnoszącym tętno.'),
            $this->cardio('outdoor', 'intermediate', 25,
                'Jogging', 'Bieganie',
                'A steady outdoor run at a conversational pace.',
                'Stałe bieganie na świeżym powietrzu w tempie pozwalającym swobodnie rozmawiać.'),
            $this->cardio('gym', 'intermediate', 30,
                'Swimming', 'Pływanie',
                'Continuous laps in the pool at a comfortable, sustained pace.',
                'Ciągłe pływanie długości basenu w komfortowym, utrzymanym tempie.'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function strength(
        string $location,
        string $difficulty,
        string $muscleGroup,
        int $sets,
        int $reps,
        string $nameEn,
        string $namePl,
        string $instructionsEn,
        string $instructionsPl,
    ): array {
        return [
            'type' => 'strength',
            'location' => $location,
            'difficulty' => $difficulty,
            'muscle_group' => $muscleGroup,
            'sets' => $sets,
            'reps' => $reps,
            'duration_minutes' => null,
            'name' => ['en' => $nameEn, 'pl' => $namePl],
            'instructions' => ['en' => $instructionsEn, 'pl' => $instructionsPl],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function cardio(
        string $location,
        string $difficulty,
        int $durationMinutes,
        string $nameEn,
        string $namePl,
        string $instructionsEn,
        string $instructionsPl,
    ): array {
        return [
            'type' => 'cardio',
            'location' => $location,
            'difficulty' => $difficulty,
            'muscle_group' => null,
            'sets' => null,
            'reps' => null,
            'duration_minutes' => $durationMinutes,
            'name' => ['en' => $nameEn, 'pl' => $namePl],
            'instructions' => ['en' => $instructionsEn, 'pl' => $instructionsPl],
        ];
    }
}
