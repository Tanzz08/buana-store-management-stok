<?php

$content = file_get_contents('database/migrations/2024_01_01_000003_create_products_table.php');
echo 'File loaded OK, length: ' . strlen($content) . PHP_EOL;

$checks = [
    'id()'                           => strpos($content, '$table->id()') !== false,
    'foreignId(category_id)'         => strpos($content, "foreignId('category_id')") !== false,
    'constrained()'                  => strpos($content, 'constrained()') !== false,
    'restrictOnDelete()'             => strpos($content, 'restrictOnDelete()') !== false,
    'sku string(100)->unique()'      => strpos($content, "string('sku', 100)->unique()") !== false,
    'name string(255)'               => strpos($content, "string('name', 255)") !== false,
    'purchase_price decimal(15,2)'   => strpos($content, "decimal('purchase_price', 15, 2)") !== false,
    'selling_price decimal(15,2)'    => strpos($content, "decimal('selling_price', 15, 2)") !== false,
    'unit string(50)'                => strpos($content, "string('unit', 50)") !== false,
    'current_stock unsigned default' => strpos($content, "unsignedInteger('current_stock')->default(0)") !== false,
    'minimum_stock unsignedInteger'  => strpos($content, "unsignedInteger('minimum_stock')") !== false,
    'status enum active/inactive'    => strpos($content, "enum('status'") !== false,
    'timestamps()'                   => strpos($content, '$table->timestamps()') !== false,
    "index('name')"                  => strpos($content, "index('name')") !== false,
    "index('sku')"                   => strpos($content, "index('sku')") !== false,
    'dropIfExists products'          => strpos($content, "dropIfExists('products')") !== false,
];

$allPassed = true;
foreach ($checks as $label => $result) {
    echo ($result ? '[OK]  ' : '[FAIL]') . ' ' . $label . PHP_EOL;
    if (!$result) $allPassed = false;
}

echo PHP_EOL . ($allPassed ? 'All checks passed.' : 'Some checks FAILED.') . PHP_EOL;
