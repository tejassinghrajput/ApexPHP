<?php

namespace App\Core\CLI;

use App\Core\Application;

class Generator
{
    protected string $moduleName;
    protected string $modulePath;
    protected string $lowerCaseName;
    protected Application $app;

    public function __construct(string $moduleName)
    {
        $this->app = Application::getInstance();
        $this->moduleName = ucfirst($moduleName);
        $this->lowerCaseName = strtolower($moduleName);
        $this->modulePath = $this->app->basePath . '/app/Modules/' . $this->moduleName;

        if (!is_dir($this->modulePath)) {
            if (!mkdir($this->modulePath, 0777, true)) {
                throw new \Exception("Failed to create module directory at {$this->modulePath}");
            }
            echo "Created module directory: {$this->modulePath}\n";
        }
    }

    public function generateAll(): void
    {
        $this->generateModel();
        $this->generateRepositoryInterface();
        $this->generateRepository();
        $this->generateService();
        $this->generateController();
        $this->generateRoutes();
        $this->generatePolicy();
        echo "\nModule '{$this->moduleName}' created successfully!\n";
    }

    public function generateModel(): void { $this->generateFromStub('model.stub', "{$this->moduleName}Model.php"); }
    public function generateController(): void { $this->generateFromStub('controller.stub', "{$this->moduleName}Controller.php"); }
    public function generateService(): void { $this->generateFromStub('service.stub', "{$this->moduleName}Service.php"); }
    public function generateRepositoryInterface(): void { $this->generateFromStub('repository.interface.stub', "{$this->moduleName}RepositoryInterface.php"); }
    public function generateRepository(): void { $this->generateFromStub('repository.stub', "{$this->moduleName}Repository.php"); }
    public function generateRoutes(): void { $this->generateFromStub('routes.stub', "{$this->lowerCaseName}.routes.php"); }
    public function generatePolicy(): void { $this->generateFromStub('policy.stub', "{$this->moduleName}Policy.php"); }

    protected function generateFromStub(string $stubFile, string $outputFile): void
    {
        $stubPath = $this->app->basePath . '/stubs/' . $stubFile;
        if (!file_exists($stubPath)) {
            throw new \Exception("Stub file not found at {$stubPath}");
        }
        $content = file_get_contents($stubPath);
        $content = str_replace(
            ['{{ModuleName}}', '{{moduleName}}'],
            [$this->moduleName, $this->lowerCaseName],
            $content
        );

        $outputPath = $this->modulePath . '/' . $outputFile;
        file_put_contents($outputPath, $content);
        echo "  -> Created $outputFile\n";
    }
}
