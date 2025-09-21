#!/usr/bin/env php
<?php

/**
 * --------------------------------------------------------------------------
 * Register The Auto Loader & Bootstrap The Application
 * --------------------------------------------------------------------------
 *
 * Composer provides a convenient, automatically generated class loader for
 * our application. We just need to utilize it! We'll simply require it
 * into the script here so that we don't have to worry about manual
 * loading any of our classes later on.
 *
 * The bootstrap file also loads environment variables and configuration.
 */

require_once __DIR__.'/bootstrap/app.php';


// Command-line argument check
if ($argc < 2) {
    echo "ApexPHP CLI. Usage: apex <command>\n";
    exit(0);
}

$command = $argv[1];

// --- Command Router ---
switch ($command) {
    case 'generate:module':
        handleGenerateModule($argv);
        break;
    case 'migrate':
        handleMigrate();
        break;
    default:
        echo "Unknown command: $command\n";
        exit(1);
}

// --- Command Handlers ---

function handleMigrate()
{
    echo "Starting migration...\n";
    try {
        $pdo = App\Core\Database::getInstance();
        $modulesPath = BASE_PATH . '/app/Modules';
        if (!is_dir($modulesPath)) {
            echo "Modules directory not found.\n";
            return;
        }
        $moduleIterator = new DirectoryIterator($modulesPath);
        foreach ($moduleIterator as $moduleDir) {
            if ($moduleDir->isDir() && !$moduleDir->isDot()) {
                $moduleName = $moduleDir->getFilename(); // e.g., "Payments"
                $modelFilePattern = $moduleDir->getPathname() . '/*Model.php';

                foreach (glob($modelFilePattern) as $modelFile) {
                    $className = basename($modelFile, '.php');
                    $fullClassName = 'App\\Modules\\' . $moduleName . '\\' . $className;

                    if (class_exists($fullClassName) && is_subclass_of($fullClassName, 'App\Core\ApexORM\Model')) {
                        $tableName = $fullClassName::getTableName();
                        $schema = $fullClassName::getSchema();
                        if (empty($schema)) {
                            echo "  - Skipping `{$tableName}`: Schema is empty.\n";
                            continue;
                        }
                        $columns = [];
                        foreach ($schema as $column => $type) {
                            $columns[] = "`{$column}` {$type}";
                        }
                        $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (" . implode(', ', $columns) . ");";
                        $pdo->exec($sql);
                        echo "  - Migrated table: `{$tableName}`\n";
                    }
                }
            }
        }
    } catch (Exception $e) {
        echo "An error occurred during migration: " . $e->getMessage() . "\n";
        exit(1);
    }
    echo "Migration finished.\n";
}

function handleGenerateModule($argv)
{
    if (count($argv) < 3) {
        echo "Error: Missing module name.\n";
        exit(1);
    }
    $moduleName = $argv[2];
    $safeModuleName = basename($moduleName);
    if ($safeModuleName !== $moduleName || !ctype_alnum($safeModuleName)) {
        echo "Error: Module name must be a single alphanumeric word.\n";
        exit(1);
    }

    $capitalizedModuleName = ucfirst($safeModuleName);
    $modulePath = BASE_PATH . '/app/Modules/' . $capitalizedModuleName;

    if (file_exists($modulePath)) {
        echo "Error: Module '$capitalizedModuleName' already exists.\n";
        exit(1);
    }
    if (!mkdir($modulePath, 0777, true)) {
        echo "Error: Failed to create directory at $modulePath\n";
        exit(1);
    }
    echo "Created module directory: $modulePath\n";

    $fileTypes = ['Model', 'Controller', 'Service', 'Routes', 'Policy'];

    foreach ($fileTypes as $fileType) {
        $className = $capitalizedModuleName . $fileType;
        $filename = $className . '.php';
        $filePath = $modulePath . '/' . $filename;

        $placeholderContent = '';
        if ($fileType === 'Model') {
            $placeholderContent = <<<EOD
<?php
namespace App\\Modules\\{$capitalizedModuleName};
use App\\Core\\ApexORM\\Model;
class {$className} extends Model
{
    protected static array \$schema = [
        'id'          => 'INTEGER PRIMARY KEY AUTOINCREMENT',
        'created_at'  => 'DATETIME DEFAULT CURRENT_TIMESTAMP'
    ];
}
EOD;
        } else {
            $placeholderContent = <<<EOD
<?php
namespace App\\Modules\\{$capitalizedModuleName};
class {$className}
{
    // TODO: Implement {$fileType} logic for the {$capitalizedModuleName} module.
}
EOD;
        }
        file_put_contents($filePath, $placeholderContent);
        echo "  -> Created $filename\n";
    }
    echo "\nModule '$capitalizedModuleName' created successfully!\n";
}
