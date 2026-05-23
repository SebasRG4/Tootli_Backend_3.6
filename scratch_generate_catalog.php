<?php

/**
 * ════════════════════════════════════════════════════════════════════════════
 * TOOTLI — GENERADOR DINÁMICO DE CATÁLOGO DE GROCERY (MÉXICO)
 * ════════════════════════════════════════════════════════════════════════════
 * Este script genera un archivo Excel (.xlsx) precargado con más de 100
 * productos típicos de supermercado mexicano, listos para importación masiva.
 * 
 * Uso: Ejecutar 'php scratch_generate_catalog.php' desde tu terminal.
 */

// Cargamos únicamente las dependencias de composer
require __DIR__ . '/vendor/autoload.php';

use Rap2hpoutre\FastExcel\FastExcel;

// Configuración inicial (Reemplaza con tus IDs reales del panel admin)
$storeId  = 1;  // ID de la tienda de Grocery en tu BD
$moduleId = 2;  // ID del módulo de Grocery en tu BD (ej. 2)

// Categorías Simuladas (Deberás reasignar los IDs de categoría en el Excel según tu panel admin)
$categories = [
    'lacteos'     => ['cat' => 10, 'sub' => 101], // Lácteos y Huevos
    'panaderia'   => ['cat' => 11, 'sub' => 111], // Panadería y Tortillas
    'bebidas'     => ['cat' => 12, 'sub' => 121], // Bebidas y Refrescos
    'botanas'     => ['cat' => 13, 'sub' => 131], // Botanas y Galletas
    'despensa'    => ['cat' => 14, 'sub' => 141], // Despensa y Abarrotes
    'frutas'      => ['cat' => 15, 'sub' => 151], // Frutas y Verduras
    'limpieza'    => ['cat' => 16, 'sub' => 161], // Cuidado Personal y Limpieza
];

$rawProducts = [
    // ── LÁCTEOS Y HUEVOS ──────────────────────────────────────────────────────
    [
        'Name' => 'Leche Lala Entera 1L',
        'Description' => 'Leche entera pasteurizada, adicionada con vitaminas A y D. Rinde para toda la familia.',
        'Price' => 28.50, 'Category' => 'lacteos', 'Veg' => 'yes', 'Image' => 'leche_lala.png'
    ],
    [
        'Name' => 'Huevo Blanco San Juan 30 pzas',
        'Description' => 'Huevo blanco fresco seleccionado de la más alta calidad, rico en proteínas.',
        'Price' => 84.00, 'Category' => 'lacteos', 'Veg' => 'yes', 'Image' => 'huevo_sanjuan.png'
    ],
    [
        'Name' => 'Crema Alpura Acidificada 450ml',
        'Description' => 'Crema acidificada de leche de vaca premium. Ideal para acompañar tus platillos mexicanos.',
        'Price' => 36.00, 'Category' => 'lacteos', 'Veg' => 'yes', 'Image' => 'crema_alpura.png'
    ],
    [
        'Name' => 'Yoghurt Lala Fresa Batido 440g',
        'Description' => 'Yoghurt batido sabor fresa con trozos de fruta natural. Ideal para el desayuno.',
        'Price' => 24.50, 'Category' => 'lacteos', 'Veg' => 'yes', 'Image' => 'yoghurt_fresa.png'
    ],
    [
        'Name' => 'Queso Oaxaca Nochebuena 400g',
        'Description' => 'Queso tipo Oaxaca de hebra premium. Ideal para fundir y preparar quesadillas.',
        'Price' => 92.00, 'Category' => 'lacteos', 'Veg' => 'yes', 'Image' => 'queso_oaxaca.png'
    ],
    [
        'Name' => 'Mantequilla Lala Sin Sal 90g',
        'Description' => 'Mantequilla de leche de vaca pura, cremosa y deliciosa para cocinar o untar.',
        'Price' => 21.00, 'Category' => 'lacteos', 'Veg' => 'yes', 'Image' => 'mantequilla_lala.png'
    ],

    // ── PANADERÍA Y TORTILLAS ──────────────────────────────────────────────────
    [
        'Name' => 'Pan Blanco Bimbo Grande 680g',
        'Description' => 'Pan blanco tradicional Bimbo, esponjoso, con actileche y vitaminas para tus sándwiches.',
        'Price' => 48.00, 'Category' => 'panaderia', 'Veg' => 'yes', 'Image' => 'pan_bimbo.png'
    ],
    [
        'Name' => 'Pan Integral Bimbo Grande 620g',
        'Description' => 'Pan de caja integral, rico en fibra, ideal para una dieta balanceada.',
        'Price' => 52.00, 'Category' => 'panaderia', 'Veg' => 'yes', 'Image' => 'pan_integral.png'
    ],
    [
        'Name' => 'Tortillinas Tía Rosa 12 pzas',
        'Description' => 'Tortillas de harina de trigo tradicionales, súper suaves y listas para calentar.',
        'Price' => 29.50, 'Category' => 'panaderia', 'Veg' => 'yes', 'Image' => 'tortillinas.png'
    ],
    [
        'Name' => 'Medias Noches Bimbo 8 pzas',
        'Description' => 'Pan especial para hot dogs, súper suaves y con el tamaño ideal.',
        'Price' => 41.50, 'Category' => 'panaderia', 'Veg' => 'yes', 'Image' => 'medias_noches.png'
    ],

    // ── BEBIDAS Y REFRESCOS ────────────────────────────────────────────────────
    [
        'Name' => 'Coca-Cola Original 600ml',
        'Description' => 'Refresco de cola espumoso y refrescante. Sabor original inigualable.',
        'Price' => 18.00, 'Category' => 'bebidas', 'Veg' => 'yes', 'Image' => 'cocacola_600.png'
    ],
    [
        'Name' => 'Agua Mineral Topo Chico 355ml',
        'Description' => 'Agua mineral de manantial con una carbonatación intensa y burbujeante.',
        'Price' => 19.50, 'Category' => 'bebidas', 'Veg' => 'yes', 'Image' => 'topochico.png'
    ],
    [
        'Name' => 'Agua Purificada Ciel 1.5L',
        'Description' => 'Agua purificada y embotellada, ideal para mantenerte hidratado todo el día.',
        'Price' => 14.50, 'Category' => 'bebidas', 'Veg' => 'yes', 'Image' => 'agua_ciel.png'
    ],
    [
        'Name' => 'Jugo Del Valle Manzana 1L',
        'Description' => 'Bebida con jugo de manzana natural, adicionado con vitaminas para toda la familia.',
        'Price' => 26.00, 'Category' => 'bebidas', 'Veg' => 'yes', 'Image' => 'jugo_manzana.png'
    ],

    // ── BOTANAS Y GALLETAS ─────────────────────────────────────────────────────
    [
        'Name' => 'Papas Sabritas Sal 110g',
        'Description' => 'Papas fritas crujientes espolvoreadas con sal fina. Las clásicas e irresistibles.',
        'Price' => 44.00, 'Category' => 'botanas', 'Veg' => 'yes', 'Image' => 'sabritas_sal.png'
    ],
    [
        'Name' => 'Takis Fuego Barcel 120g',
        'Description' => 'Tortillas de maíz enrolladas y fritas sabor chile y limón extremo. ¡Súper picantes!',
        'Price' => 38.00, 'Category' => 'botanas', 'Veg' => 'no', 'Image' => 'takis_fuego.png'
    ],
    [
        'Name' => 'Galletas Marias Gamesa 170g',
        'Description' => 'Galletas de harina de trigo tradicionales sabor vainilla, ideales para acompañar con café.',
        'Price' => 17.50, 'Category' => 'botanas', 'Veg' => 'yes', 'Image' => 'marias_gamesa.png'
    ],
    [
        'Name' => 'Galletas Chokis Gamesa 143g',
        'Description' => 'Galletas con deliciosas chispas sabor chocolate. Crujientes por fuera y suaves por dentro.',
        'Price' => 22.00, 'Category' => 'botanas', 'Veg' => 'yes', 'Image' => 'chokis.png'
    ],

    // ── DESPENSA Y ABARROTES ───────────────────────────────────────────────────
    [
        'Name' => 'Arroz Súper Extra Verde Valle 1kg',
        'Description' => 'Arroz blanco grano largo seleccionado, calidad premium. Rinde el doble en tu cocina.',
        'Price' => 38.50, 'Category' => 'despensa', 'Veg' => 'yes', 'Image' => 'arroz_verdevalle.png'
    ],
    [
        'Name' => 'Frijoles Bayos Refritos Isadora 430g',
        'Description' => 'Frijoles bayos refritos cocinados con un toque de cebolla y aceite. Listos para calentar.',
        'Price' => 21.00, 'Category' => 'despensa', 'Veg' => 'yes', 'Image' => 'frijoles_isadora.png'
    ],
    [
        'Name' => 'Aceite Vegetal Nutrioli 850ml',
        'Description' => 'Aceite puro de soya, ideal para freír, cocinar y aderezar tus comidas favoritas.',
        'Price' => 42.00, 'Category' => 'despensa', 'Veg' => 'yes', 'Image' => 'aceite_nutrioli.png'
    ],
    [
        'Name' => 'Mayonesa McCormick con Limón 390g',
        'Description' => 'Mayonesa clásica adicionada con jugo de limón natural. Sabor y cremosidad únicos.',
        'Price' => 54.00, 'Category' => 'despensa', 'Veg' => 'no', 'Image' => 'mayonesa_mccormick.png'
    ],
    [
        'Name' => 'Atún Herdez en Agua 130g',
        'Description' => 'Lomo de atún en agua seleccionado de pesca sustentable, bajo en grasa y rico en Omega 3.',
        'Price' => 22.50, 'Category' => 'despensa', 'Veg' => 'no', 'Image' => 'atun_herdez.png'
    ],
    [
        'Name' => 'Salsa Catsup Del Monte 320g',
        'Description' => 'Salsa de tomate tipo Catsup, dulce y especiada, ideal para hamburguesas y papas.',
        'Price' => 19.00, 'Category' => 'despensa', 'Veg' => 'yes', 'Image' => 'catsup_delmonte.png'
    ],

    // ── FRUTAS Y VERDURAS ──────────────────────────────────────────────────────
    [
        'Name' => 'Plátano Chiapas (1kg)',
        'Description' => 'Plátano Tabasco o Chiapas maduro y dulce, ideal para el desayuno o licuados.',
        'Price' => 26.00, 'Category' => 'frutas', 'Veg' => 'yes', 'Image' => 'platano.png'
    ],
    [
        'Name' => 'Jitomate Saladet (1kg)',
        'Description' => 'Jitomate saladet fresco y firme de huerto. Esencial para ensaladas, sopas y guisados.',
        'Price' => 38.00, 'Category' => 'frutas', 'Veg' => 'yes', 'Image' => 'jitomate.png'
    ],
    [
        'Name' => 'Aguacate Hass Premium (1kg)',
        'Description' => 'Aguacate Hass de pulpa cremosa y sabor delicioso, ideal para guacamole o guarniciones.',
        'Price' => 78.00, 'Category' => 'frutas', 'Veg' => 'yes', 'Image' => 'aguacate.png'
    ],
    [
        'Name' => 'Limón con Semilla (1kg)',
        'Description' => 'Limón colima jugoso y fresco con semilla, ideal para acompañar tus tacos y caldos.',
        'Price' => 34.00, 'Category' => 'frutas', 'Veg' => 'yes', 'Image' => 'limon.png'
    ],

    // ── CUIDADO PERSONAL Y LIMPIEZA ────────────────────────────────────────────
    [
        'Name' => 'Jabón de Lavandería Zote Blanco 400g',
        'Description' => 'Jabón de lavandería tradicional mexicano, ideal para desmanchar y lavar ropa delicada.',
        'Price' => 26.50, 'Category' => 'limpieza', 'Veg' => 'yes', 'Image' => 'zote_blanco.png'
    ],
    [
        'Name' => 'Papel Higiénico Regio Aires de Frescura 4 rollos',
        'Description' => 'Papel higiénico de doble hoja, extra suave y resistente con un delicioso aroma fresco.',
        'Price' => 32.00, 'Category' => 'limpieza', 'Veg' => 'yes', 'Image' => 'papel_regio.png'
    ],
    [
        'Name' => 'Detergente Líquido Ariel Doble Poder 1.2L',
        'Description' => 'Detergente líquido concentrado que elimina manchas difíciles desde la primera lavada.',
        'Price' => 74.00, 'Category' => 'limpieza', 'Veg' => 'yes', 'Image' => 'ariel_liquido.png'
    ],
    [
        'Name' => 'Pasta Dental Colgate Triple Acción 150ml',
        'Description' => 'Pasta dental con protección anticaries, aliento fresco y dientes más blancos.',
        'Price' => 39.50, 'Category' => 'limpieza', 'Veg' => 'yes', 'Image' => 'colgate_triple.png'
    ],
];

// Generar una base más amplia clonando y variando marcas para tener 100+ productos
$finalProducts = [];
$idCounter = 1;

// Mapeamos los productos al formato de exportación del bulk-import de Tootli
foreach ($rawProducts as $p) {
    $catInfo = $categories[$p['Category']];
    
    $finalProducts[] = [
        'Id'                  => $idCounter++,
        'Name'                => $p['Name'],
        'Description'         => $p['Description'],
        'Image'               => $p['Image'],
        'Images'              => json_encode([]),
        'CategoryId'          => $catInfo['cat'],
        'SubCategoryId'       => $catInfo['sub'],
        'UnitId'              => 1, // Unidad genérica (pza/kg)
        'Stock'               => 150,
        'Price'               => $p['Price'],
        'Discount'            => 0,
        'DiscountType'        => 'amount',
        'AvailableTimeStarts' => '00:00:00',
        'AvailableTimeEnds'   => '23:59:59',
        'Variations'          => json_encode([]),
        'ChoiceOptions'       => json_encode([]),
        'AddOns'              => json_encode([]),
        'Attributes'          => json_encode([]),
        'StoreId'             => $storeId,
        'ModuleId'            => $moduleId,
        'Status'              => 'active',
        'Veg'                 => $p['Veg'],
        'Recommended'         => (rand(1, 10) > 8) ? 'yes' : 'no', // Recomendados al azar
    ];
}

// Duplicar agregando variaciones típicas para llegar a más de 100 productos
$marcasAdicionales = [
    'lacteos' => [
        ['Name' => 'Leche Santa Clara Entera 1L', 'Price' => 31.00, 'Image' => 'santa_clara_1l.png'],
        ['Name' => 'Leche Alpura Clásica 1L', 'Price' => 29.00, 'Image' => 'alpura_1l.png'],
        ['Name' => 'Huevo Rojo Bachoco 30 pzas', 'Price' => 88.00, 'Image' => 'huevo_bachoco.png'],
        ['Name' => 'Queso Panela Lala 400g', 'Price' => 64.00, 'Image' => 'panela_lala.png'],
        ['Name' => 'Yoghurt Danone Fresa 220g', 'Price' => 14.50, 'Image' => 'danone_220g.png']
    ],
    'panaderia' => [
        ['Name' => 'Pan Integral Oroweat 680g', 'Price' => 74.00, 'Image' => 'oroweat.png'],
        ['Name' => 'Bollos Bimbo con Ajonjolí 8 pzas', 'Price' => 45.00, 'Image' => 'bollos_bimbo.png'],
        ['Name' => 'Tortillas de Maíz Milpa Real 1kg', 'Price' => 24.00, 'Image' => 'tortillas_milpa.png'],
        ['Name' => 'Donas Bimbo Espolvoreadas 4 pzas', 'Price' => 23.50, 'Image' => 'donas_bimbo.png']
    ],
    'bebidas' => [
        ['Name' => 'Coca-Cola Sin Azúcar 600ml', 'Price' => 18.00, 'Image' => 'cocacola_light.png'],
        ['Name' => 'Refresco Peñafiel Limón 2L', 'Price' => 27.00, 'Image' => 'penafiel_2l.png'],
        ['Name' => 'Jugo Jumex Durazno 1L', 'Price' => 25.00, 'Image' => 'jumex_1l.png'],
        ['Name' => 'Agua Purificada Bonafont 1.5L', 'Price' => 15.00, 'Image' => 'agua_bonafont.png'],
        ['Name' => 'Bebida Energética Monster Energy 473ml', 'Price' => 44.50, 'Image' => 'monster.png']
    ],
    'botanas' => [
        ['Name' => 'Pringles Original 124g', 'Price' => 52.00, 'Image' => 'pringles_sal.png'],
        ['Name' => 'Cheetos Torciditos 150g', 'Price' => 36.00, 'Image' => 'cheetos.png'],
        ['Name' => 'Ruffles Queso Barcel 120g', 'Price' => 42.00, 'Image' => 'ruffles_queso.png'],
        ['Name' => 'Galletas Emperador Chocolate 150g', 'Price' => 21.00, 'Image' => 'emperador.png']
    ],
    'despensa' => [
        ['Name' => 'Frijoles Negros Refritos La Sierra 400g', 'Price' => 19.50, 'Image' => 'la_sierra_negros.png'],
        ['Name' => 'Aceite de Maíz Mazola 800ml', 'Price' => 49.00, 'Image' => 'aceite_mazola.png'],
        ['Name' => 'Pasta Spaguetti La Moderna 200g', 'Price' => 9.50, 'Image' => 'spaghetti.png'],
        ['Name' => 'Salsa de Tomate Hunt\'s Tradicional 360g', 'Price' => 22.00, 'Image' => 'hunts_salsa.png'],
        ['Name' => 'Atún Dolores en Aceite 140g', 'Price' => 23.50, 'Image' => 'atun_dolores.png'],
        ['Name' => 'Café Soluble Nescafé Clásico 120g', 'Price' => 84.00, 'Image' => 'nescafe_120.png'],
        ['Name' => 'Consomé de Pollo Knorr Suiza 10 cubos', 'Price' => 18.00, 'Image' => 'knorr_cubos.png']
    ],
    'frutas' => [
        ['Name' => 'Manzana Roja Delicia (1kg)', 'Price' => 49.00, 'Image' => 'manzana_roja.png'],
        ['Name' => 'Cebolla Blanca Premium (1kg)', 'Price' => 29.00, 'Image' => 'cebolla.png'],
        ['Name' => 'Papa Blanca Beta (1kg)', 'Price' => 36.00, 'Image' => 'papa_blanca.png'],
        ['Name' => 'Plátano Macho (1kg)', 'Price' => 32.00, 'Image' => 'platano_macho.png']
    ],
    'limpieza' => [
        ['Name' => 'Shampoo Pantene Restauración 400ml', 'Price' => 68.00, 'Image' => 'shampoo_pantene.png'],
        ['Name' => 'Jabón de Tocador Escudo Antibacterial 110g', 'Price' => 18.50, 'Image' => 'escudo.png'],
        ['Name' => 'Desodorante Axe Spray Apollo 150ml', 'Price' => 59.00, 'Image' => 'axe.png'],
        ['Name' => 'Limpiador Líquido Fabuloso Lavanda 1L', 'Price' => 27.50, 'Image' => 'fabuloso_1l.png']
    ]
];

// Llenamos hasta tener más de 100 productos (duplicaremos de forma inteligente variando precios)
for ($i = 0; $i < 3; $i++) {
    foreach ($marcasAdicionales as $catKey => $items) {
        $catInfo = $categories[$catKey];
        foreach ($items as $item) {
            // Variamos un poco el precio según la iteración para simular tamaños o marcas distintas
            $priceModifier = 1 + ($i * 0.15); // +0%, +15%, +30%
            $finalPrice = round($item['Price'] * $priceModifier, 2);
            
            $nameSuffix = "";
            if ($i == 1) $nameSuffix = " Ahorro";
            if ($i == 2) $nameSuffix = " Duopack";

            $finalProducts[] = [
                'Id'                  => $idCounter++,
                'Name'                => $item['Name'] . $nameSuffix,
                'Description'         => "Producto seleccionado de la categoría, ideal para el surtido familiar. Calidad garantizada en Tootli.",
                'Image'               => $item['Image'],
                'Images'              => json_encode([]),
                'CategoryId'          => $catInfo['cat'],
                'SubCategoryId'       => $catInfo['sub'],
                'UnitId'              => 1,
                'Stock'               => rand(50, 300),
                'Price'               => $finalPrice,
                'Discount'            => 0,
                'DiscountType'        => 'amount',
                'AvailableTimeStarts' => '00:00:00',
                'AvailableTimeEnds'   => '23:59:59',
                'Variations'          => json_encode([]),
                'ChoiceOptions'       => json_encode([]),
                'AddOns'              => json_encode([]),
                'Attributes'          => json_encode([]),
                'StoreId'             => $storeId,
                'ModuleId'            => $moduleId,
                'Status'              => 'active',
                'Veg'                 => ($catKey == 'despensa' || $catKey == 'bebidas') ? 'yes' : 'no',
                'Recommended'         => (rand(1, 10) > 8) ? 'yes' : 'no',
            ];
        }
    }
}

// Exportamos usando FastExcel (nativo en Tootli)
$outputFile = 'grocery_catalog_mexico.xlsx';
(new FastExcel($finalProducts))->export($outputFile);

echo "\n========================================================";
echo "\n✨ ¡CATÁLOGO DE GROCERY GENERADO CON ÉXITO!";
echo "\n========================================================";
echo "\n📂 Archivo: " . __DIR__ . '/' . $outputFile;
echo "\n📦 Total de productos generados: " . count($finalProducts);
echo "\n🏪 Asociado a Store ID: " . $storeId;
echo "\n🧩 Asociado a Module ID: " . $moduleId;
echo "\n\n💡 Consejos de Uso:";
echo "\n1. Abre este archivo en Excel / Google Sheets.";
echo "\n2. Mapea la columna 'CategoryId' y 'SubCategoryId' con los IDs reales de tus categorías del Panel Admin.";
echo "\n3. Cámbiale el 'StoreId' al ID real de tu supermercado.";
echo "\n4. Súbelo directo desde la sección 'Bulk Import' en el Panel Administrativo.";
echo "\n========================================================\n\n";
