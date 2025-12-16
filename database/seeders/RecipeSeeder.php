<?php

namespace Database\Seeders;

use App\Models\ProductProduct;
use App\Models\ProductTemplate;
use App\Models\Recipe;
use App\Models\Unit;
use Illuminate\Database\Seeder;

class RecipeSeeder extends Seeder
{
    /**
     * Seed sample recipes for menu dishes.
     */
    public function run(): void
    {
        // Get units
        $kg = Unit::where('name', 'Kilogramo')->first();
        $lt = Unit::where('name', 'Litro')->first();
        $und = Unit::where('name', 'Unidad')->first();
        $gr = Unit::firstOrCreate(['name' => 'Gramo'], ['abbreviation' => 'gr', 'is_active' => true]);

        if (! $kg || ! $lt || ! $und) {
            $this->command->error('Units not found! Run UnitSeeder first.');

            return;
        }

        // Get dishes (product_template con can_be_sold=true)
        $ceviche = ProductTemplate::where('name', 'Ceviche Clásico')->first();
        $lomoSaltado = ProductTemplate::where('name', 'Lomo Saltado')->first();
        $ajiGallina = ProductTemplate::where('name', 'Ají de Gallina')->first();
        $arrozChaufa = ProductTemplate::where('name', 'Arroz Chaufa')->first();

        // Get ingredients (product_product de inventario)
        $getIngredient = function ($name) {
            $template = ProductTemplate::where('name', $name)->first();

            return $template ? ProductProduct::where('template_id', $template->id)->first() : null;
        };

        $pescado = $getIngredient('Pescado Fresco');
        $limon = $getIngredient('Limón');
        $cebolla = $getIngredient('Cebolla Roja');
        $ajiAmarillo = $getIngredient('Ají Amarillo');
        $pollo = $getIngredient('Pollo Entero');
        $papa = $getIngredient('Papa Blanca');
        $arroz = $getIngredient('Arroz Granel');
        $aceite = $getIngredient('Aceite Vegetal 5L');
        $ajo = $getIngredient('Ajo Molido');

        $recipes = [];

        // CEVICHE CLÁSICO (basado en receta real para 1 porción)
        if ($ceviche && $pescado && $limon && $cebolla && $ajiAmarillo) {
            $recipes[] = [
                'dish' => $ceviche,
                'ingredients' => [
                    ['ingredient' => $pescado, 'quantity' => 0.188, 'unit' => $kg, 'waste' => 10, 'notes' => 'Corvina fresca en cubos de 2cm. PREP: Cortar el pescado previamente congelado en dados parejos, descartar piel y espinas.'],
                    ['ingredient' => $limon, 'quantity' => 0.150, 'unit' => $kg, 'waste' => 5, 'notes' => 'Limón recién exprimido. PREP: Extraer jugo de 8-10 limones, colar para eliminar semillas. Temperatura ambiente esencial.'],
                    ['ingredient' => $cebolla, 'quantity' => 0.075, 'unit' => $kg, 'waste' => 10, 'notes' => 'Cebolla morada en juliana. PREP: Cortar muy fino en juliana, lavar en agua fría para quitar acidez, escurrir bien.'],
                    ['ingredient' => $ajiAmarillo, 'quantity' => 0.015, 'unit' => $kg, 'waste' => 5, 'notes' => 'Ají limo fresco. PREP: Retirar semillas, picar finamente. Regular según tolerancia al picante del comensal.'],
                ],
            ];
        }

        // LOMO SALTADO (basado en receta real para 1 porción)
        if ($lomoSaltado && $pollo && $papa && $cebolla && $aceite && $ajo) {
            $recipes[] = [
                'dish' => $lomoSaltado,
                'ingredients' => [
                    ['ingredient' => $pollo, 'quantity' => 0.150, 'unit' => $kg, 'waste' => 12, 'notes' => 'Lomo fino de res. PREP: Cortar en tiras de 1cm contra la fibra. Sellar a fuego muy alto 1min por lado. No sobrecocinar.'],
                    ['ingredient' => $papa, 'quantity' => 0.125, 'unit' => $kg, 'waste' => 15, 'notes' => 'Papa amarilla. PREP: Cortar en bastones 1x6cm. Freír en aceite 180°C hasta dorar. Escurrir en papel. Reservar caliente.'],
                    ['ingredient' => $cebolla, 'quantity' => 0.060, 'unit' => $kg, 'waste' => 10, 'notes' => 'Cebolla roja. PREP: Cortar en julianas gruesas (1cm). Saltear 30seg a fuego alto, debe quedar crocante.'],
                    ['ingredient' => $ajiAmarillo, 'quantity' => 0.025, 'unit' => $kg, 'waste' => 5, 'notes' => 'Ají amarillo fresco. PREP: Cortar en tiras delgadas sin semillas. Agregar al wok con la cebolla.'],
                    ['ingredient' => $aceite, 'quantity' => 0.020, 'unit' => $lt, 'waste' => 0, 'notes' => 'Aceite vegetal. TÉCNICA: Calentar wok hasta humear. Usar aceite en 2 tandas: sellar carne y saltear verduras.'],
                    ['ingredient' => $ajo, 'quantity' => 0.005, 'unit' => $kg, 'waste' => 0, 'notes' => 'Ajo fresco. PREP: Picar 1 diente finamente. Marinar carne 10min con ajo, sal, pimienta y comino antes de sellar.'],
                ],
            ];
        }

        // AJÍ DE GALLINA (receta tradicional por porción)
        if ($ajiGallina && $pollo && $ajiAmarillo && $arroz && $ajo) {
            $recipes[] = [
                'dish' => $ajiGallina,
                'ingredients' => [
                    ['ingredient' => $pollo, 'quantity' => 0.180, 'unit' => $kg, 'waste' => 15, 'notes' => 'Pechuga de gallina. PREP: Hervir 30min con apio y zanahoria. Enfriar en caldo, deshilachar finamente. Reservar caldo.'],
                    ['ingredient' => $ajiAmarillo, 'quantity' => 0.040, 'unit' => $kg, 'waste' => 5, 'notes' => 'Ají amarillo fresco. PREP: Licuar con 200ml leche evaporada y pan remojado. Freír en aceite 5min removiendo constantemente.'],
                    ['ingredient' => $arroz, 'quantity' => 0.120, 'unit' => $kg, 'waste' => 0, 'notes' => 'Arroz blanco graneado. PREP: Cocinar arroz suelto. Servir como base del plato, verter el ají de gallina encima.'],
                    ['ingredient' => $papa, 'quantity' => 0.100, 'unit' => $kg, 'waste' => 10, 'notes' => 'Papa amarilla. PREP: Sancochar con cáscara hasta tierna 20min. Pelar, cortar en rodajas 1cm. Acompañamiento lateral.'],
                    ['ingredient' => $ajo, 'quantity' => 0.008, 'unit' => $kg, 'waste' => 0, 'notes' => 'Ajo molido. PREP: Preparar aderezo: sofreír 2 dientes picados con cebolla 5min. Agregar ají licuado y pollo deshilachado.'],
                ],
            ];
        }

        // ARROZ CHAUFA (receta exacta por porción)
        if ($arrozChaufa && $arroz && $pollo && $aceite && $ajo && $cebolla) {
            $recipes[] = [
                'dish' => $arrozChaufa,
                'ingredients' => [
                    ['ingredient' => $arroz, 'quantity' => 0.180, 'unit' => $kg, 'waste' => 0, 'notes' => 'Arroz cocido frío. CRÍTICO: Usar arroz del día anterior refrigerado. Granos deben estar sueltos y secos. Nunca arroz recién hecho.'],
                    ['ingredient' => $pollo, 'quantity' => 0.100, 'unit' => $kg, 'waste' => 10, 'notes' => 'Pollo en cubos. PREP: Cortar en dados 0.5cm. Marinar con sillao 10min. Saltear primero 2min fuego alto, retirar.'],
                    ['ingredient' => $cebolla, 'quantity' => 0.040, 'unit' => $kg, 'waste' => 10, 'notes' => 'Cebolla china. PREP: Separar blanco y verde. Picar parte blanca, saltear primero. Reservar verde picado para decorar al final.'],
                    ['ingredient' => $aceite, 'quantity' => 0.025, 'unit' => $lt, 'waste' => 0, 'notes' => 'Aceite de ajonjolí. TÉCNICA: Calentar wok hasta humear. Agregar aceite, saltear ingredientes sin parar 4-5min total.'],
                    ['ingredient' => $ajo, 'quantity' => 0.010, 'unit' => $kg, 'waste' => 0, 'notes' => 'Ajo chino picado. PREP: Picar 3 dientes muy fino. Saltear 30seg con jengibre antes de agregar arroz. Aroma debe perfumar wok.'],
                ],
            ];
        }

        // Insert recipes
        $count = 0;
        foreach ($recipes as $recipeData) {
            foreach ($recipeData['ingredients'] as $ing) {
                Recipe::create([
                    'product_template_id' => $recipeData['dish']->id,
                    'ingredient_id' => $ing['ingredient']->id,
                    'quantity' => $ing['quantity'],
                    'unit_id' => $ing['unit']->id,
                    'waste_percentage' => $ing['waste'],
                    'notes' => $ing['notes'],
                ]);
                $count++;
            }
        }

        $this->command->info("Recipes seeded: $count ingredient lines for ".count($recipes).' dishes');

        // Summary table
        $this->command->table(
            ['Dish', 'Ingredients'],
            collect($recipes)->map(fn ($r) => [
                $r['dish']->name,
                count($r['ingredients']).' ingredientes',
            ])->toArray()
        );
    }
}
