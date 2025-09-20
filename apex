#!/usr/bin/env php
<?php

// Check for command-line arguments
if ($argc < 2) {
    echo "ApexPHP CLI is ready. Use 'apex help' for a list of commands.\n";
    exit(0);
}

$command = $argv[1];

// --- Command Router ---
switch ($command) {
    case 'generate:module':
        handleGenerateModule($argv);
        break;
    default:
        echo "Unknown command: $command\n";
        echo "Usage: apex generate:module <ModuleName>\n";
        exit(1);
}

// --- Command Handlers ---

/**
 * Handles the 'generate:module' command.
 */
function handleGenerateModule($argv)
{
    if (count($argv) < 3) {
        echo "Error: Missing module name.\n";
        echo "Usage: apex generate:module <ModuleName>\n";
        exit(1);
    }
    $moduleName = $argv[2];

    // Sanitize module name to prevent directory traversal attacks
    $safeModuleName = basename($moduleName);
    if ($safeModuleName !== $moduleName) {
        echo "Error: Invalid characters in module name.\n";
        exit(1);
    }

    if (!ctype_alnum($safeModuleName)) {
        echo "Error: Module name must be alphanumeric.\n";
        exit(1);
    }

    $modulePath = 'app/modules/' . strtolower($safeModuleName);
    $capitalizedModuleName = ucfirst($safeModuleName);

    if (file_exists($modulePath)) {
        echo "Error: Module '$safeModuleName' already exists.\n";
        exit(1);
    }

    // Create the module directory
    if (!mkdir($modulePath, 0777, true)) {
        echo "Error: Failed to create directory at $modulePath\n";
        exit(1);
    }

    echo "Created module directory: $modulePath\n";

    // Define the files to be created
    $filesToCreate = [
        $capitalizedModuleName . '.model.php',
        $capitalizedModuleName . '.controller.php',
        $capitalizedModuleName . '.service.php',
        $capitalizedModuleName . '.routes.php',
        $capitalizedModuleName . '.policy.php',
    ];

    // Create each file with placeholder content
    foreach ($filesToCreate as $filename) {
        $filePath = $modulePath . '/' . $filename;
        $fileType = str_replace('.php', '', explode('.', $filename)[1]);
        $placeholderContent = "<?php\n\nnamespace App\\Modules\\" . $capitalizedModuleName . ";\n\nclass " . $capitalizedModuleName . ucfirst($fileType) . "\n{\n    // TODO: Implement " . $fileType . " logic\n}\n";

        if (file_put_contents($filePath, $placeholderContent) === false) {
            echo "Error: Failed to create file: $filePath\n";
            // Optional: Add cleanup logic here to remove created directory
            exit(1);
        }
        echo "  -> Created $filename\n";
    }

    echo "\nModule '$safeModuleName' created successfully!\n";
}
