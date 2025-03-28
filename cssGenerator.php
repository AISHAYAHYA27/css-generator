<?php
$args = $argv[$argc - 1];
function myMerge($args, $recursive = false)
{
    $recursive = false;
    $dir = getcwd() . '/' . $args;
    //echo $dir;
    // var_dump($argv);
    $files = [];
    $filepath = [];
    $maxWidth = 0;
    $maxHeight = 0;
    $imageSizes = [];

    if (!is_dir($dir)) {
        die("Le dossier n'existe pas : $dir\n");
    }

    $repo = opendir($dir);
    while (false !== ($filename = readdir($repo))) {

        if ($filename === '.' || $filename === '..') {
            continue;
        }

        $path = "$dir/$filename";

        if (is_dir($path) && $recursive) {

            $filepath = array_merge($filepath, myMerge($path, true));
        } else {
            $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            if ($ext === 'png') {
                $filepath[] = $path;

                $size = getimagesize($path);
                $imageSizes[] = $size;
                $maxWidth = max($maxWidth, $size[0]);
                $maxHeight += $size[1];
            }
        }
    }
    closedir($repo);

    if (empty($filepath)) {
        die("Aucune image PNG trouvée dans le dossier $dir.\n");
    }

    $im = imagecreatetruecolor($maxWidth, $maxHeight);
    imagesavealpha($im, true);
    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $transparent);

    $currentHeight = 0;
    foreach ($filepath as $index => $path) {
        $img = imagecreatefrompng($path);
        imagecopy($im, $img, 0, $currentHeight, 0, 0, $imageSizes[$index][0], $imageSizes[$index][1]);
        $currentHeight += $imageSizes[$index][1];
        imagedestroy($img);
    }

    $outputPath = getcwd() . "/sprite.png";
    imagepng($im, $outputPath);
    imagedestroy($im);

    echo "Image fusionée sauvegardée sous : $outputPath\n";
    styleCss($imageSizes);
    return $outputPath;
}

function styleCss($tab)
{
    $cssFile = fopen("style.css", "w");
    echo count($tab);

    $cssContent = "";

    for ($i = 0; $i < count($tab); $i++) {
        $cssContent .= "
        .image$i{
        height :" . $tab[$i][1] . "px;
        width: " . $tab[$i][0] . "px;
        }";
    }
    fwrite($cssFile, $cssContent);
}



function CliOptions()
{
    $shortOpts = "-r:-s:-i:-p:-o:-c:";
    $longOpts = [
        "--recursive",
        "--output-style:",
        "--padding:",
        "--output-image:",
        "--columns_number:",
        "--override-size:"
    ];

    $options = getopt($shortOpts, $longOpts);

    return [
        'recursive' => isset($options['r']) || isset($options['recursive']),
        'output_image' => $options['i'] ?? $options['output-image'] ?? "sprite.png",
        'output_style' => isset($options['s']) || isset($options['output-style']) ?? "style.css",
        'padding' => isset($options['p']) || isset($options['padding']) ?? 0,
        'override_size' => isset($options['o']) || isset($options['override-size']) ?? 0,
        'columns_number' => isset($options['c']) || isset($options['columns_number']) ?? 0,
    ];
}

$options = CliOptions();
$recursive = $options['recursive'];
$output_image = $options['output_image'];
$output_style = $options['output_style'];
$padding = $options['padding'];
$override_size = $options['override_size'];
$columns_number = $options['columns_number'];

myMerge($args);



