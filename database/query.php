<?php
require 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use App\Models\Category;

$categories = Category::all();
print_r($categories->toArray());