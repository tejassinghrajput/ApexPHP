#!/usr/bin/env php
<?php

// 1. Register The Composer Auto Loader
require_once __DIR__.'/vendor/autoload.php';

// 2. Bootstrap The Application
$app = require_once __DIR__.'/bootstrap/app.php';

use App\Core\CLI\Generator;

function abort(string $message) {
    fwrite(STDERR, "Error: {$message}\n");
    exit(1);
}

function getArgument(array $argv, string $command): string {
    if (!isset($argv[2])) {
        abort("Missing name argument. Usage: apex {$command} <Name>");
    }
    $name = $argv[2];
    if (!ctype_alnum(basename($name))) {
        abort("Name must be a single alphanumeric word.");
    }
    return $name;
}

if ($argc < 2) {
    echo "ApexPHP CLI. Usage: apex <command>\n";
    exit(0);
}
$command = $argv[1];

try {
    switch ($command) {
        case 'generate:module':
            (new Generator(getArgument($argv, $command)))->generateAll();
            break;
        case 'generate:model':
            (new Generator(getArgument($argv, $command)))->generateModel();
            break;
        case 'generate:controller':
            (new Generator(getArgument($argv, $command)))->generateController();
            break;
        case 'generate:service':
            (new Generator(getArgument($argv, $command)))->generateService();
            break;
        case 'generate:policy':
            (new Generator(getArgument($argv, $command)))->generatePolicy();
            break;
        case 'generate:seeder':
            handleGenerateSeeder($app, $argv);
            break;
        case 'migrate':
            handleMigrate($app);
            break;
        case 'seed':
            handleSeed($app);
            break;
        default:
            abort("Unknown command: $command");
    }
} catch (Exception $e) {
    abort($e->getMessage());
}

function handleGenerateSeeder(App\Core\Application $app, array $argv) {
    $seederName = getArgument($argv, 'generate:seeder');
    $capitalized = ucfirst($seederName);

    $stubPath = $app->basePath . '/stubs/seeder.stub';
    if (!file_exists($stubPath)) { abort("Stub file not found at {$stubPath}"); }

    $content = file_get_contents($stubPath);
    $content = str_replace('{{SeederName}}', $capitalized, $content);

    $seederPath = $app->basePath . '/database/seeders';
    if (!is_dir($seederPath)) { mkdir($seederPath, 0777, true); }

    $outputPath = $seederPath . '/' . $capitalized . '.php';
    if (file_exists($outputPath)) { abort("Seeder {$capitalized} already exists."); }

    file_put_contents($outputPath, $content);
    echo "  -> Created Seeder: {$capitalized}.php\n";
}

function handleMigrate(App\Core\Application $app) {
    echo "Starting migration...\n";
    try {
        $database = $app->resolve(App\Core\Database::class);
        $pdo = $database->pdo;
        $modulesPath = $app->basePath . '/app/Modules';
        if (!is_dir($modulesPath)) { return; }
        $moduleIterator = new DirectoryIterator($modulesPath);
        foreach ($moduleIterator as $moduleDir) {
            if ($moduleDir->isDir() && !$moduleDir->isDot()) {
                $moduleName = $moduleDir->getFilename();
                $modelFilePattern = $moduleDir->getPathname() . '/*Model.php';
                foreach (glob($modelFilePattern) as $modelFile) {
                    $className = basename($modelFile, '.php');
                    $fullClassName = 'App\\Modules\\' . $moduleName . '\\' . $className;
                    if (class_exists($fullClassName) && is_subclass_of($fullClassName, 'App\Core\ApexORM\Model')) {
                        $tableName = $fullClassName::getTableName();
                        $schema = $fullClassName::getSchema();
                        if (empty($schema)) { continue; }
                        $columns = [];
                        foreach ($schema as $column => $type) { $columns[] = "`{$column}` {$type}"; }
                        $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (" . implode(', ', $columns) . ");";
                        $pdo->exec($sql);
                        echo "  - Migrated table: `{$tableName}`\n";
                    }
                }
            }
        }
    } catch (Exception $e) {
        abort("Migration failed: " . $e->getMessage());
    }
    echo "Migration finished.\n";
}

function handleSeed(App\Core\Application $app) {
    echo "Seeding database...\n";
    $seederPath = $app->basePath . '/database/seeders';
    if (!is_dir($seederPath)) {
        echo "No seeders found.\n";
        return;
    }

    foreach (glob($seederPath . '/*.php') as $file) {
        $className = "Database\\Seeders\\" . basename($file, '.php');
        if (class_exists($className)) {
            $seeder = $app->resolve($className);
            $seeder->run();
        }
    }
    echo "Database seeding completed.\n";
}
