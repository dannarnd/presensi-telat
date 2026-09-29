<?php

/**
 * Entry point untuk Vercel PHP Runtime
 * File ini menjadi "jembatan" antara Vercel dengan Laravel
 */

define('LARAVEL_START', microtime(true));

// Arahkan ke public directory Laravel
$root = __DIR__ . '/..';

// Set document root ke folder public
$_SERVER['DOCUMENT_ROOT'] = $root . '/public';

// Bootstrap Laravel
chdir($root . '/public');

require $root . '/public/index.php';
