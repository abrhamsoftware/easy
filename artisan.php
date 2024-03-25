<?php


$config = [
    "model" => "src/Models/",
    "interface" => "src/Interface/",
    "sample" => "png/Models/Sample/"
];


// Make It From File 

function stubData(string $make, string $name): string
{

    $data = '';
    $stub = [
        // Model
        "model" => str_replace("{{ name }}", $name, file_get_contents('Stubs/Model.stub')),

        // Interface
        "interface" => str_replace("{{ name }}", $name, file_get_contents('Stubs/Interface.stub')),

    ];



    if (in_array($make, array_keys($stub))) {
        $data = $stub[$make];
    } else {
        echo "make:?? Type Not Found \n";
        exit(1);
    }

    return $data;
}





function createFolderOrFail(string $folderPath): void
{
    if (!is_dir($folderPath)) {
        if (!mkdir($folderPath, 0777, true)) {
            echo "Failed to create folder. 😞";
        }
    }
}


function createFileOrFail(string $filePath, string $data)
{
    if (file_exists($filePath)) {
        echo "File already exists. \n";
    } else {
        file_put_contents($filePath, $data);
        echo "File created. \n";
    }
}



/**
 * Command Argument Proccessing
 */
if ($argc < 2) {
    echo "Argument required\n";
    exit(1);
} else if (strpos(strtolower($argv[1]), 'make:') !== 0 || strtolower($argv[1]) == 'make:') {
    echo "Argument not correct\n";
    exit(1);
} else {

    if ($argc < 3) {
        echo "File name required\n";
        exit(1);
    } else {
        [, $type] = explode(":", strtolower($argv[1]));

        $fromConfigPath = '';
        if (!in_array($type, array_keys($config))) {
            echo "make: type is not correct";
            exit(1);
        } else {
            $fromConfigPath = $config[$type];
        }
        createFolderOrFail($fromConfigPath);

        // Loop required for creation
        foreach (array_slice($argv, 2) as $fileName) {
            // Follder Processing, contain .
            $folderPath = $fromConfigPath;
            if (strpos($fileName, '.') !== false) {
                $temp = explode(".", strtolower($fileName));
                $temporary = array_map(function ($value) {
                    return ucfirst($value);
                }, $temp);
                $folderPath = $folderPath . implode('/', array_slice($temporary, 0, -1));
                createFolderOrFail($folderPath);
                $fileName = $temporary[count($temporary) - 1];
            }


            $fileContainPath = $folderPath . '/' . ucfirst($fileName) . (($type === 'blade') ? $fileContainPath . ".blade.php" : ".php");
            createFileOrFail($fileContainPath, stubData($type, $fileName));
        }
    }
}
