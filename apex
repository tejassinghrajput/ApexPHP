#!/usr/bin/env php
<?php

require_once __DIR__.'/bootstrap/app.php';

function abort(string $message) {
    fwrite(STDERR, "Error: {$message}\n");
    exit(1);
}

if ($argc < 2) {
    echo "ApexPHP CLI. Usage: apex <command>\n";
    exit(0);
}

$command = $argv[1];

switch ($command) {
    case 'generate:module':
        handleGenerateModule($argv);
        break;
    case 'migrate':
        handleMigrate();
        break;
    default:
        abort("Unknown command: $command");
}

function handleMigrate()
{
    echo "Starting migration...\n";
    try {
        $pdo = App\Core\Database::getInstance();
        $modulesPath = BASE_PATH . '/app/Modules';
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

function handleGenerateModule($argv)
{
    if (count($argv) < 3) { abort("Missing module name."); }
    $moduleName = $argv[2];
    $safeModuleName = basename($moduleName);
    if ($safeModuleName !== $moduleName || !ctype_alnum($safeModuleName)) { abort("Module name must be a single alphanumeric word."); }

    $capitalized = ucfirst($safeModuleName);
    $lowerCase = strtolower($safeModuleName);
    $modulePath = BASE_PATH . '/app/Modules/' . $capitalized;
    if (file_exists($modulePath)) { abort("Module '$capitalized' already exists."); }
    if (!mkdir($modulePath, 0777, true)) { abort("Failed to create directory at $modulePath"); }
    echo "Created module directory: $modulePath\n";

    $files = [
        'Model' => "<?php\nnamespace App\\Modules\\{$capitalized};\nuse App\\Core\\ApexORM\\Model;\nclass {$capitalized}Model extends Model { protected static array \$schema = ['id' => 'INTEGER PRIMARY KEY AUTOINCREMENT', 'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP']; }",
        'Controller' => "<?php\nnamespace App\\Modules\\{$capitalized};\nuse App\\Core\\Controller;\nuse App\\Core\\Request;\nclass {$capitalized}Controller extends Controller\n{\n    public function index(Request \$request)\n    {\n        return \$this->json({$capitalized}Model::all());\n    }\n\n    public function show(Request \$request, int \$id)\n    {\n        \$item = {$capitalized}Model::find(\$id);\n        if (!\$item) {\n            return \$this->json(['message' => 'Not Found'], 404);\n        }\n        return \$this->json(\$item);\n    }\n}",
        'Service' => "<?php\nnamespace App\\Modules\\{$capitalized};\nclass {$capitalized}Service { /* Business logic here */ }",
        'Routes' => "<?php\nuse App\\Modules\\{$capitalized}\\{$capitalized}Controller;\n\n\$router->get('/api/{$lowerCase}', [{$capitalized}Controller::class, 'index']);\n\$router->get('/api/{$lowerCase}/{id}', [{$capitalized}Controller::class, 'show']);",
        'Policy' => "<?php\nnamespace App\\Modules\\{$capitalized};\nclass {$capitalized}Policy { /* Authorization logic here */ }",
    ];

    foreach ($files as $type => $content) {
        $className = $capitalized . $type;
        $filename = $className . '.php';
        if ($type === 'Routes') { $filename = strtolower($safeModuleName) . '.routes.php'; }

        file_put_contents($modulePath . '/' . $filename, $content);
        echo "  -> Created $filename\n";
    }
    echo "\nModule '$capitalized' created successfully!\n";
}
