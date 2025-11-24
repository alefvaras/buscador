<?php
/**
 * Script de prueba para verificar la normalización de búsqueda
 * Simula cómo funciona la búsqueda sin necesidad de base de datos
 */

echo "=== TEST DE NORMALIZACIÓN DE BÚSQUEDA ===\n\n";

// Función de normalización (igual que en functions.php línea 85)
function normalize_search_term($term) {
    return strtolower(str_replace([' ', '-', '_', '.', ','], '', $term));
}

// Función para simular el REPLACE de MySQL
function mysql_normalize($text) {
    // Simula: LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(p.post_title, ' ', ''), '-', ''), '_', ''), '.', ''), ',', ''))
    return strtolower(str_replace([' ', '-', '_', '.', ','], '', $text));
}

// Datos de prueba - Productos que existen en tu tienda
$products = [
    ['id' => 1, 'title' => 'Dan Da Dan 20'],
    ['id' => 2, 'title' => 'Dan Da Dan 19'],
    ['id' => 3, 'title' => 'Dan Da Dan 21'],
    ['id' => 4, 'title' => 'Jujutsu Kaisen 15'],
    ['id' => 5, 'title' => 'One-Piece 105'],
    ['id' => 6, 'title' => 'Dandadan Box Set'],
];

// Queries de prueba
$test_queries = [
    'DANDADAN 20',
    'dandadan 20',
    'dan da dan 20',
    'DanDaDan20',
    'dan-da-dan 20',
    'jujutsu kaisen',
    'JUJUTSUKAISEN',
    'one piece',
    'onepiece',
];

foreach ($test_queries as $query) {
    echo "─────────────────────────────────────────\n";
    echo "🔍 Buscando: '$query'\n";
    $normalized_query = normalize_search_term($query);
    echo "📝 Normalizado: '$normalized_query'\n\n";

    $found = [];

    foreach ($products as $product) {
        $normalized_title = mysql_normalize($product['title']);

        // Simula la búsqueda SQL: WHERE ... LIKE '%normalized_query%'
        if (strpos($normalized_title, $normalized_query) !== false) {
            $found[] = $product;
            echo "✅ ENCONTRADO: ID={$product['id']}, Title='{$product['title']}'\n";
            echo "   → Título normalizado: '$normalized_title'\n";
        }
    }

    if (empty($found)) {
        echo "❌ No se encontraron resultados\n";
    }

    echo "\n";
}

echo "=== FIN DEL TEST ===\n\n";

// Test adicional: Verificar que la SQL funcionaría correctamente
echo "=== VERIFICACIÓN DE LÓGICA SQL ===\n\n";

$search_term = "DANDADAN 20";
$normalized = normalize_search_term($search_term);

echo "Query original: '$search_term'\n";
echo "Query normalizado: '$normalized'\n\n";

echo "Condición SQL que se generaría:\n";
echo "WHERE p.post_type = 'product'\n";
echo "AND p.post_status = 'publish'\n";
echo "AND (\n";
echo "    LOWER(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(p.post_title, ' ', ''), '-', ''), '_', ''), '.', ''), ',', '')) LIKE '%$normalized%'\n";
echo "    OR LOWER(p.post_title) LIKE '%" . strtolower($search_term) . "%'\n";
echo "    OR LOWER(pm_sku.meta_value) LIKE '%$search_term%'\n";
echo ")\n\n";

// Simular con un producto real
$real_product_title = "Dan Da Dan 20";
$real_normalized = mysql_normalize($real_product_title);

echo "Producto en BD: '$real_product_title'\n";
echo "Producto normalizado: '$real_normalized'\n\n";

echo "¿Coincide?\n";
if (strpos($real_normalized, $normalized) !== false) {
    echo "✅ SÍ - '$normalized' está en '$real_normalized'\n";
    echo "✅ La búsqueda debería funcionar correctamente\n";
} else {
    echo "❌ NO - Hay un problema\n";
}

echo "\n=== TEST COMPLETADO ===\n";
