#!/usr/bin/env php
<?php

// 1. Register The Composer Auto Loader
// THIS MUST BE THE FIRST THING THAT HAPPENS
require_once __DIR__.'/vendor/autoload.php';

// 2. Bootstrap The Application
$app = require_once __DIR__.'/bootstrap/app.php';

// --- Helper Functions ---
function abort(string $message) {
    fwrite(STDERR, "Error: {$message}\n");
    exit(1);
}

// --- Argument Parsing & Command Routing ---
if ($argc < 2) {
    echo "ApexPHP CLI. Usage: apex <command>\n";
    exit(0);
}
$command = $argv[1];

switch ($command) {
    case 'generate:module':
        handleGenerateModule($app, $argv);
        break;
    case 'migrate':
        handleMigrate($app);
        break;
    default:
        abort("Unknown command: $command");
}

// --- Command Handlers ---

function handleGenerateModule(App\Core\Application $app, array $argv) {
    if (count($argv) < 3) { abort("Missing module name."); }
    $moduleName = $argv[2];
    $safeModuleName = basename($moduleName);
    if ($safeModuleName !== $moduleName || !ctype_alnum($safeModuleName)) { abort("Module name must be a single alphanumeric word."); }

    $capitalized = ucfirst($safeModuleName);
    $lowerCase = strtolower($safeModuleName);
    $modulePath = $app->basePath . '/app/Modules/' . $capitalized;

    if (file_exists($modulePath)) { abort("Module '$capitalized' already exists."); }
    if (!mkdir($modulePath, 0777, true)) { abort("Failed to create directory at $modulePath"); }
    echo "Created module directory: $modulePath\n";

    $stubs = [
        'model.stub' => "{$capitalized}Model.php",
        'controller.stub' => "{$capitalized}Controller.php",
        'service.stub' => "{$capitalized}Service.php",
        'repository.interface.stub' => "{$capitalized}RepositoryInterface.php",
        'repository.stub' => "{$capitalized}Repository.php",
        'routes.stub' => "{$lowerCase}.routes.php",
        'policy.stub' => "{$capitalized}Policy.php",
    ];

    foreach ($stubs as $stubFile => $outputFile) {
        $content = file_get_contents(__DIR__ . '/stubs/' . $stubFile);
        $content = str_replace(['{{ModuleName}}', '{{moduleName}}'], [$capitalized, $lowerCase], $content);
        file_put_contents($modulePath . '/' . $outputFile, $content);
        echo "  -> Created $outputFile\n";
    }

    echo "\nModule '$capitalized' created successfully!\n";
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
