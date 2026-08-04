<?php

namespace Database\Seeders;

use App\Models\MealTemplate;
use Illuminate\Database\Seeder;

/**
 * Rules as data, not code (docs/DESIGN-PATTERNS.md §7) — the nutrition plan
 * strategies never hard-code meals, they filter this catalogue by meal_time
 * and pick whatever is closest to that slot's share of the daily calorie
 * target.
 *
 * Macros are approximate, not a precise 1:1 breakdown of the stated
 * calories — good enough to show a rough protein/fat/carb split, not meant
 * to be relied on the way the calorie figure is.
 */
class MealTemplateSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->meals() as $meal) {
            MealTemplate::create($meal);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function meals(): array
    {
        return [
            ...$this->breakfast(),
            ...$this->secondBreakfast(),
            ...$this->lunch(),
            ...$this->afternoonSnack(),
            ...$this->dinner(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function breakfast(): array
    {
        return [
            $this->meal('breakfast', 420, 20, 14, 55, 'Oatmeal with Banana and Honey', 'Owsianka z bananem i miodem', 'Rolled oats cooked with milk, topped with sliced banana and a drizzle of honey.', 'Płatki owsiane gotowane na mleku, podane z pokrojonym bananem i odrobiną miodu.'),
            $this->meal('breakfast', 380, 24, 22, 22, 'Scrambled Eggs with Toast', 'Jajecznica z tostem', 'Three eggs scrambled with butter, served with a slice of whole grain toast.', 'Trzy jajka usmażone na maśle, podane z kromką pełnoziarnistego tosta.'),
            $this->meal('breakfast', 350, 22, 10, 45, 'Greek Yogurt with Granola and Berries', 'Jogurt grecki z granolą i owocami', 'Greek yogurt layered with crunchy granola and a handful of mixed berries.', 'Jogurt grecki przełożony chrupiącą granolą i garścią owoców jagodowych.'),
            $this->meal('breakfast', 340, 10, 20, 32, 'Whole Grain Toast with Avocado', 'Pełnoziarnisty tost z awokado', 'Mashed avocado on whole grain toast with a pinch of salt and chili flakes.', 'Rozgnieciony awokado na pełnoziarnistym toście, z odrobiną soli i chili.'),
            $this->meal('breakfast', 300, 26, 8, 20, 'Cottage Cheese with Tomato and Chives', 'Twarożek z pomidorem i szczypiorkiem', 'Cottage cheese mixed with diced tomato and fresh chives.', 'Twarożek wymieszany z pokrojonym pomidorem i świeżym szczypiorkiem.'),
            $this->meal('breakfast', 360, 24, 24, 8, 'Omelette with Spinach and Feta', 'Omlet ze szpinakiem i fetą', 'A three-egg omelette folded with wilted spinach and crumbled feta cheese.', 'Omlet z trzech jajek ze szpinakiem i pokruszoną fetą.'),
            $this->meal('breakfast', 400, 22, 12, 50, 'Peanut Butter Banana Smoothie', 'Smoothie z masłem orzechowym i bananem', 'Blended banana, milk, oats, and a spoonful of peanut butter.', 'Zmiksowany banan, mleko, płatki owsiane i łyżka masła orzechowego.'),
            $this->meal('breakfast', 380, 12, 8, 65, 'Buckwheat Porridge with Fruit', 'Kasza gryczana na słodko z owocami', 'Sweet buckwheat porridge cooked in milk, topped with seasonal fruit.', 'Słodka kasza gryczana ugotowana na mleku, podana z sezonowymi owocami.'),
            $this->meal('breakfast', 340, 18, 14, 38, 'Egg and Vegetable Wrap', 'Tortilla z jajkiem i warzywami', 'A tortilla filled with scrambled eggs, peppers, and onion.', 'Tortilla z jajecznicą, papryką i cebulą.'),
            $this->meal('breakfast', 310, 20, 10, 35, 'Rye Bread with Cottage Cheese and Radish', 'Chleb żytni z twarogiem i rzodkiewką', 'Two slices of rye bread topped with cottage cheese and sliced radish.', 'Dwie kromki chleba żytniego z twarogiem i plastrami rzodkiewki.'),
            $this->meal('breakfast', 400, 30, 10, 42, 'Protein Pancakes with Berries', 'Placki proteinowe z owocami', 'Fluffy protein pancakes topped with a handful of mixed berries.', 'Puszyste placki proteinowe podane z garścią owoców jagodowych.'),
            $this->meal('breakfast', 320, 12, 9, 50, 'Muesli with Milk', 'Musli z mlekiem', 'A bowl of muesli with dried fruit and nuts, served with milk.', 'Musli z suszonymi owocami i orzechami, podane z mlekiem.'),
            $this->meal('breakfast', 330, 20, 16, 24, 'Poached Eggs on Toast', 'Jajka w koszulkach na tości', 'Two poached eggs served over a slice of toasted whole grain bread.', 'Dwa jajka w koszulkach podane na kromce tostowanego pełnoziarnistego chleba.'),
            $this->meal('breakfast', 350, 12, 22, 28, 'Chia Pudding with Almond Milk', 'Pudding chia z mlekiem migdałowym', 'Chia seeds soaked overnight in almond milk, topped with fruit.', 'Nasiona chia namoczone przez noc w mleku migdałowym, podane z owocami.'),
            $this->meal('breakfast', 370, 22, 18, 32, 'Cheese and Ham Sandwich', 'Kanapka z serem i szynką', 'A sandwich with cheese, lean ham, and lettuce on whole grain bread.', 'Kanapka z serem, chudą szynką i sałatą na pełnoziarnistym chlebie.'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function secondBreakfast(): array
    {
        return [
            $this->meal('second_breakfast', 160, 2, 8, 22, 'Apple and a Handful of Almonds', 'Jabłko i garść migdałów', 'A fresh apple with a small handful of raw almonds.', 'Świeże jabłko z małą garścią surowych migdałów.'),
            $this->meal('second_breakfast', 200, 6, 10, 22, 'Rice Cakes with Peanut Butter', 'Ryżowe wafle z masłem orzechowym', 'Two rice cakes spread with natural peanut butter.', 'Dwa ryżowe wafle posmarowane naturalnym masłem orzechowym.'),
            $this->meal('second_breakfast', 140, 2, 1, 32, 'Fruit Salad', 'Sałatka owocowa', 'A mix of seasonal fruit cut into bite-sized pieces.', 'Mieszanka sezonowych owoców pokrojonych w kawałki.'),
            $this->meal('second_breakfast', 210, 15, 8, 22, 'Protein Bar', 'Baton proteinowy', 'A store-bought protein bar, roughly 20g of protein.', 'Gotowy baton proteinowy, około 20g białka.'),
            $this->meal('second_breakfast', 150, 5, 8, 15, 'Carrot Sticks with Hummus', 'Marchewka z hummusem', 'Fresh carrot sticks served with a portion of hummus.', 'Świeże słupki marchewki podane z porcją hummusu.'),
            $this->meal('second_breakfast', 105, 1, 0, 27, 'Banana', 'Banan', 'One medium banana.', 'Jeden średni banan.'),
            $this->meal('second_breakfast', 170, 16, 5, 18, 'Cottage Cheese with Pineapple', 'Twarożek z ananasem', 'Cottage cheese mixed with chunks of fresh pineapple.', 'Twarożek wymieszany z kawałkami świeżego ananasa.'),
            $this->meal('second_breakfast', 220, 6, 14, 18, 'Trail Mix', 'Mieszanka orzechów i suszonych owoców', 'A small handful of mixed nuts, seeds, and dried fruit.', 'Mała garść orzechów, nasion i suszonych owoców.'),
            $this->meal('second_breakfast', 78, 6, 5, 1, 'Boiled Egg', 'Jajko na twardo', 'One hard-boiled egg.', 'Jedno jajko ugotowane na twardo.'),
            $this->meal('second_breakfast', 150, 8, 3, 22, 'Yogurt Drink', 'Jogurt pitny', 'A bottle of drinkable natural yogurt.', 'Butelka pitnego jogurtu naturalnego.'),
            $this->meal('second_breakfast', 190, 8, 9, 20, 'Whole Grain Crackers with Cheese', 'Pełnoziarniste krakersy z serem', 'Whole grain crackers topped with slices of cheese.', 'Pełnoziarniste krakersy z plastrami sera.'),
            $this->meal('second_breakfast', 62, 1, 0, 15, 'Orange', 'Pomarańcza', 'One medium orange.', 'Jedna średnia pomarańcza.'),
            $this->meal('second_breakfast', 160, 11, 10, 6, 'Cherry Tomatoes with Mozzarella', 'Pomidorki koktajlowe z mozzarellą', 'Cherry tomatoes with bite-sized mozzarella balls and basil.', 'Pomidorki koktajlowe z kulkami mozzarelli i bazylią.'),
            $this->meal('second_breakfast', 210, 5, 13, 20, 'Dried Apricots and Walnuts', 'Suszone morele i orzechy włoskie', 'A small handful of dried apricots with walnut halves.', 'Mała garść suszonych moreli z połówkami orzechów włoskich.'),
            $this->meal('second_breakfast', 110, 6, 3, 15, 'Cucumber and Yogurt Dip', 'Ogórek z dipem jogurtowym', 'Sliced cucumber served with a herbed yogurt dip.', 'Pokrojony ogórek podany z dipem jogurtowym z ziołami.'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function lunch(): array
    {
        return [
            $this->meal('lunch', 620, 48, 14, 70, 'Grilled Chicken Breast with Rice and Broccoli', 'Grillowana pierś z kurczaka z ryżem i brokułami', 'Grilled chicken breast served with steamed rice and broccoli.', 'Grillowana pierś z kurczaka podana z gotowanym na parze ryżem i brokułami.'),
            $this->meal('lunch', 650, 42, 24, 60, 'Beef Stir-Fry with Vegetables', 'Wołowina stir-fry z warzywami', 'Sliced beef stir-fried with peppers, broccoli, and onion.', 'Pokrojona wołowina smażona z papryką, brokułami i cebulą.'),
            $this->meal('lunch', 600, 40, 22, 55, 'Salmon with Quinoa and Asparagus', 'Łosoś z quinoa i szparagami', 'Baked salmon fillet with quinoa and roasted asparagus.', 'Pieczony filet z łososia z quinoa i pieczonymi szparagami.'),
            $this->meal('lunch', 680, 30, 24, 78, 'Spaghetti Bolognese', 'Spaghetti bolognese', 'Spaghetti with a rich beef and tomato sauce.', 'Spaghetti z sosem z mięsa wołowego i pomidorów.'),
            $this->meal('lunch', 600, 36, 18, 65, 'Turkey Meatballs with Mashed Potatoes', 'Klopsiki z indyka z puree ziemniaczanym', 'Turkey meatballs in tomato sauce with mashed potatoes.', 'Klopsiki z indyka w sosie pomidorowym z puree ziemniaczanym.'),
            $this->meal('lunch', 550, 42, 12, 60, 'Baked Cod with Roasted Vegetables', 'Pieczony dorsz z pieczonymi warzywami', 'Baked cod fillet with a tray of roasted seasonal vegetables.', 'Pieczony filet z dorsza z pieczonymi sezonowymi warzywami.'),
            $this->meal('lunch', 650, 38, 20, 72, 'Chicken Curry with Rice', 'Curry z kurczaka z ryżem', 'Chicken pieces in a mild curry sauce served over rice.', 'Kawałki kurczaka w łagodnym sosie curry podane z ryżem.'),
            $this->meal('lunch', 620, 40, 18, 65, 'Pork Tenderloin with Buckwheat and Salad', 'Polędwiczki wieprzowe z kaszą gryczaną i surówką', 'Grilled pork tenderloin with buckwheat and a side salad.', 'Grillowane polędwiczki wieprzowe z kaszą gryczaną i surówką.'),
            $this->meal('lunch', 500, 24, 12, 68, 'Vegetable and Lentil Stew', 'Gulasz warzywno-soczewicowy', 'A hearty stew of lentils, carrots, and tomatoes.', 'Sycący gulasz z soczewicy, marchwi i pomidorów.'),
            $this->meal('lunch', 560, 32, 20, 58, 'Tuna Pasta Salad', 'Sałatka makaronowa z tuńczykiem', 'Pasta salad with tuna, cherry tomatoes, and olive oil.', 'Sałatka makaronowa z tuńczykiem, pomidorkami i oliwą.'),
            $this->meal('lunch', 700, 36, 30, 68, 'Beef Burger with Sweet Potato Fries', 'Burger wołowy z frytkami z batatów', 'A beef burger on a bun with a side of baked sweet potato fries.', 'Burger wołowy w bułce z pieczonymi frytkami z batatów.'),
            $this->meal('lunch', 520, 38, 14, 55, 'Shrimp and Vegetable Stir-Fry', 'Krewetki stir-fry z warzywami', 'Shrimp stir-fried with mixed vegetables and a light soy sauce.', 'Krewetki smażone z warzywami w lekkim sosie sojowym.'),
            $this->meal('lunch', 600, 34, 22, 62, 'Chicken Fajitas', 'Fajitas z kurczakiem', 'Sliced chicken with peppers and onion, served with tortillas.', 'Kurczak w plastrach z papryką i cebulą, podany z tortillami.'),
            $this->meal('lunch', 610, 40, 20, 58, 'Baked Chicken Thighs with Rice', 'Pieczone udka z kurczaka z ryżem', 'Baked chicken thighs served with steamed rice.', 'Pieczone udka z kurczaka podane z gotowanym na parze ryżem.'),
            $this->meal('lunch', 540, 22, 14, 78, 'Vegetable and Chickpea Curry', 'Curry z warzyw i ciecierzycy', 'A vegetable curry with chickpeas served over rice.', 'Curry warzywne z ciecierzycą podane z ryżem.'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function afternoonSnack(): array
    {
        return [
            $this->meal('afternoon_snack', 180, 25, 3, 15, 'Protein Shake', 'Koktajl proteinowy', 'A protein shake mixed with water or milk.', 'Koktajl proteinowy zmiksowany z wodą lub mlekiem.'),
            $this->meal('afternoon_snack', 120, 16, 3, 8, 'Cottage Cheese with Cucumber', 'Twarożek z ogórkiem', 'Cottage cheese served with sliced cucumber.', 'Twarożek podany z plastrami ogórka.'),
            $this->meal('afternoon_snack', 190, 4, 18, 4, 'Handful of Walnuts', 'Garść orzechów włoskich', 'A small handful of raw walnuts.', 'Mała garść surowych orzechów włoskich.'),
            $this->meal('afternoon_snack', 110, 10, 2, 14, 'Rice Cake with Turkey Slice', 'Ryżowy wafel z plastrem indyka', 'A rice cake topped with a slice of lean turkey.', 'Ryżowy wafel z plastrem chudego indyka.'),
            $this->meal('afternoon_snack', 160, 3, 9, 18, 'Berries with Whipped Cream', 'Owoce jagodowe z bitą śmietaną', 'Mixed berries with a small dollop of whipped cream.', 'Owoce jagodowe z niewielką porcją bitej śmietany.'),
            $this->meal('afternoon_snack', 100, 6, 4, 10, 'Vegetable Sticks with Cottage Cheese Dip', 'Warzywa z dipem twarogowym', 'Carrot and pepper sticks with a cottage cheese dip.', 'Słupki marchewki i papryki z dipem twarogowym.'),
            $this->meal('afternoon_snack', 150, 3, 10, 12, 'Dark Chocolate Square and Almonds', 'Kostka gorzkiej czekolady z migdałami', 'A square of dark chocolate with a few almonds.', 'Kostka gorzkiej czekolady z kilkoma migdałami.'),
            $this->meal('afternoon_snack', 120, 8, 4, 12, 'Kefir', 'Kefir', 'A glass of natural kefir.', 'Szklanka naturalnego kefiru.'),
            $this->meal('afternoon_snack', 140, 14, 5, 10, 'Tuna on Crackers', 'Tuńczyk na krakersach', 'Tuna spread over a few whole grain crackers.', 'Tuńczyk rozsmarowany na kilku pełnoziarnistych krakersach.'),
            $this->meal('afternoon_snack', 170, 4, 2, 35, 'Fruit Smoothie', 'Smoothie owocowe', 'A blended smoothie of mixed fruit and water.', 'Zmiksowane smoothie z owoców i wody.'),
            $this->meal('afternoon_snack', 100, 8, 5, 5, 'Boiled Egg with Cherry Tomatoes', 'Jajko na twardo z pomidorkami', 'A hard-boiled egg with a handful of cherry tomatoes.', 'Jajko na twardo z garścią pomidorków koktajlowych.'),
            $this->meal('afternoon_snack', 200, 7, 9, 24, 'Hummus with Pita Bread', 'Hummus z pitą', 'Hummus served with a small pita bread for dipping.', 'Hummus podany z małym chlebkiem pita do maczania.'),
            $this->meal('afternoon_snack', 150, 12, 4, 16, 'Greek Yogurt with Honey', 'Jogurt grecki z miodem', 'Greek yogurt drizzled with a spoonful of honey.', 'Jogurt grecki polany łyżką miodu.'),
            $this->meal('afternoon_snack', 90, 3, 3, 15, 'Popcorn (Air-Popped)', 'Popcorn (bez tłuszczu)', 'A bowl of air-popped popcorn with no added fat.', 'Miska popcornu przygotowanego bez dodatku tłuszczu.'),
            $this->meal('afternoon_snack', 160, 4, 9, 16, 'Apple Slices with Peanut Butter', 'Plastry jabłka z masłem orzechowym', 'Sliced apple served with a spoonful of peanut butter.', 'Plastry jabłka podane z łyżką masła orzechowego.'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function dinner(): array
    {
        return [
            $this->meal('dinner', 480, 36, 20, 38, 'Grilled Salmon with Steamed Vegetables', 'Grillowany łosoś z gotowanymi na parze warzywami', 'Grilled salmon fillet with a mix of steamed vegetables.', 'Grillowany filet z łososia z mieszanką warzyw gotowanych na parze.'),
            $this->meal('dinner', 400, 26, 26, 12, 'Vegetable Omelette', 'Omlet warzywny', 'A three-egg omelette with peppers, onion, and mushrooms.', 'Omlet z trzech jajek z papryką, cebulą i pieczarkami.'),
            $this->meal('dinner', 420, 32, 22, 22, 'Chicken Salad with Olive Oil', 'Sałatka z kurczakiem i oliwą', 'Mixed greens with grilled chicken and an olive oil dressing.', 'Mieszanka sałat z grillowanym kurczakiem i dressingiem oliwnym.'),
            $this->meal('dinner', 380, 12, 22, 32, 'Zucchini Noodles with Pesto', 'Makaron z cukinii z pesto', 'Spiralized zucchini tossed with basil pesto.', 'Makaron z cukinii wymieszany z pesto bazyliowym.'),
            $this->meal('dinner', 350, 28, 14, 24, 'Cottage Cheese Salad with Vegetables', 'Sałatka z twarogiem i warzywami', 'Cottage cheese with cucumber, tomato, and radish.', 'Twarożek z ogórkiem, pomidorem i rzodkiewką.'),
            $this->meal('dinner', 420, 40, 10, 35, 'Baked Tilapia with Green Beans', 'Pieczona tilapia z fasolką szparagową', 'Baked tilapia fillet with a side of steamed green beans.', 'Pieczony filet z tilapii z fasolką szparagową gotowaną na parze.'),
            $this->meal('dinner', 340, 24, 10, 38, 'Turkey and Vegetable Soup', 'Zupa z indyka i warzyw', 'A warming soup with turkey, carrots, and celery.', 'Rozgrzewająca zupa z indykiem, marchwią i selerem.'),
            $this->meal('dinner', 400, 22, 20, 32, 'Egg Salad on Rye Bread', 'Sałatka jajeczna na chlebie żytnim', 'Chopped egg salad served on a slice of rye bread.', 'Sałatka z posiekanych jajek podana na kromce chleba żytniego.'),
            $this->meal('dinner', 380, 34, 14, 24, 'Grilled Shrimp Skewers with Salad', 'Grillowane szaszłyki z krewetek z sałatką', 'Grilled shrimp skewers served over a bed of mixed greens.', 'Grillowane szaszłyki z krewetek podane na mieszance sałat.'),
            $this->meal('dinner', 450, 26, 12, 58, 'Stuffed Peppers with Rice', 'Papryka faszerowana ryżem', 'Bell peppers stuffed with rice, ground meat, and tomato sauce.', 'Papryka faszerowana ryżem, mięsem mielonym i sosem pomidorowym.'),
            $this->meal('dinner', 360, 38, 14, 16, 'Cauliflower Rice with Chicken', 'Ryż kalafiorowy z kurczakiem', 'Riced cauliflower stir-fried with diced chicken breast.', 'Ryż kalafiorowy smażony z pokrojoną piersią z kurczaka.'),
            $this->meal('dinner', 380, 24, 26, 14, 'Vegetable Frittata', 'Frittata warzywna', 'A baked egg frittata with spinach, tomato, and onion.', 'Zapiekana frittata jajeczna ze szpinakiem, pomidorem i cebulą.'),
            $this->meal('dinner', 350, 26, 16, 24, 'Smoked Salmon with Cucumber and Rye Bread', 'Wędzony łosoś z ogórkiem na chlebie żytnim', 'Smoked salmon and cucumber on a slice of rye bread.', 'Wędzony łosoś z ogórkiem na kromce chleba żytniego.'),
            $this->meal('dinner', 340, 20, 8, 48, 'Lentil Soup', 'Zupa z soczewicy', 'A hearty lentil soup with carrots and celery.', 'Sycąca zupa z soczewicy z marchwią i selerem.'),
            $this->meal('dinner', 400, 18, 26, 24, 'Greek Salad with Feta', 'Sałatka grecka z fetą', 'Tomato, cucumber, olives, and feta with an olive oil dressing.', 'Pomidor, ogórek, oliwki i feta z dressingiem oliwnym.'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function meal(
        string $mealTime,
        int $calories,
        int $proteinG,
        int $fatG,
        int $carbsG,
        string $nameEn,
        string $namePl,
        string $descriptionEn,
        string $descriptionPl,
    ): array {
        return [
            'meal_time' => $mealTime,
            'calories' => $calories,
            'protein_g' => $proteinG,
            'fat_g' => $fatG,
            'carbs_g' => $carbsG,
            'name' => ['en' => $nameEn, 'pl' => $namePl],
            'description' => ['en' => $descriptionEn, 'pl' => $descriptionPl],
        ];
    }
}
