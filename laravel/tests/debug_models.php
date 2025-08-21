<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Product\Product;
use App\Models\Auth\User;
use ReflectionClass;

// Debug Product model
$product = new Product();
echo "=== Product Model Debug ===\n";
echo "Casts: " . json_encode($product->getCasts()) . "\n";
$reflection = new ReflectionClass($product);
echo "Traits: " . json_encode($reflection->getTraitNames()) . "\n";
echo "Parent class: " . $reflection->getParentClass()->getName() . "\n\n";

// Debug User model  
$user = new User();
echo "=== User Model Debug ===\n";
echo "Casts: " . json_encode($user->getCasts()) . "\n";
$reflection = new ReflectionClass($user);
echo "Traits: " . json_encode($reflection->getTraitNames()) . "\n";
echo "Parent class: " . $reflection->getParentClass()->getName() . "\n\n";