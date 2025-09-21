#!/usr/bin/env php
<?php

require_once __DIR__.'/bootstrap/app.php';

function abort(string $message) { fwrite(STDERR, "Error: {$message}\n"); exit(1); }

if ($argc < 2) { echo "ApexPHP CLI. Usage: apex <command>\n"; exit(0); }

$command = $argv[1];

switch ($command) {
    case 'generate:module': handleGenerateModule($argv); break;
    case 'migrate': handleMigrate(); break;
    default: abort("Unknown command: $command");
}

function handleMigrate() {
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
    } catch (Exception $e) { abort("Migration failed: " . $e->getMessage()); }
    echo "Migration finished.\n";
}

function handleGenerateModule($argv) {
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

    $modelContent = "<?php\nnamespace App\\Modules\\{$capitalized};\nuse App\\Core\\ApexORM\\Model;\nclass {$capitalized}Model extends Model {\n    /**\n     * The attributes that are mass assignable for security.\n     * @var array\n     */\n    protected array \$fillable = ['amount', 'description'];\n\n    /**\n     * The database schema for the model.\n     * @var array\n     */\n    protected static array \$schema = ['id' => 'INTEGER PRIMARY KEY AUTOINCREMENT', 'amount' => 'DECIMAL(10, 2) NOT NULL', 'description' => 'TEXT', 'created_at' => 'DATETIME DEFAULT CURRENT_TIMESTAMP'];\n}";
    $controllerContent = "<?php\nnamespace App\\Modules\\{$capitalized};\nuse App\\Core\\Controller;\nuse App\\Core\\Request;\nclass {$capitalized}Controller extends Controller\n{\n    public function index() { return \$this->json({$capitalized}Model::all()); }\n    public function show(Request \$request, int \$id) { \$item = {$capitalized}Model::find(\$id); return \$item ? \$this->json(\$item) : \$this->json(['message' => 'Not Found'], 404); }\n    public function store(Request \$request) { \$model = new {$capitalized}Model(\$request->json()); \$model->save(); return \$this->json(\$model, 201); }\n    public function update(Request \$request, int \$id) { \$model = {$capitalized}Model::find(\$id); if (!\$model) { return \$this->json(['message' => 'Not Found'], 404); } \$model->fill(\$request->json()); \$model->save(); return \$this->json(\$model); }\n    public function destroy(Request \$request, int \$id) { \$model = {$capitalized}Model::find(\$id); if (!\$model) { return \$this->json(['message' => 'Not Found'], 404); } \$model->delete(); return \$this->json(null, 204); }\n}";
    $routesContent = "<?php\nuse App\\Modules\\{$capitalized}\\{$capitalized}Controller;\n\$router->get('/api/{$lowerCase}', [{$capitalized}Controller::class, 'index']);\n\$router->get('/api/{$lowerCase}/{id}', [{$capitalized}Controller::class, 'show']);\n\$router->post('/api/{$lowerCase}', [{$capitalized}Controller::class, 'store']);\n\$router->put('/api/{$lowerCase}/{id}', [{$capitalized}Controller::class, 'update']);\n\$router->delete('/api/{$lowerCase}/{id}', [{$capitalized}Controller::class, 'destroy']);";

    $files = [
        'Model' => $modelContent,
        'Controller' => $controllerContent,
        'Service' => "<?php\nnamespace App\\Modules\\{$capitalized};\nclass {$capitalized}Service { /* Business logic here */ }",
        'Routes' => $routesContent,
        'Policy' => "<?php\nnamespace App\\Modules\\{$capitalized};\nclass {$capitalized}Policy { /* Authorization logic here */ }",
    ];

    foreach ($files as $type => $content) {
        $filename = $capitalized . $type . '.php';
        if ($type === 'Routes') { $filename = strtolower($safeModuleName) . '.routes.php'; }
        file_put_contents($modulePath . '/' . $filename, $content);
        echo "  -> Created $filename\n";
    }
    echo "\nModule '$capitalized' created successfully!\n";
}
