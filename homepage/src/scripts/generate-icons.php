<?php
$config = App::config()->icons();

foreach (get_dir_items($config['dir'], full_paths: true) as $iconset_dir) {
    generate_icons($iconset_dir, $config);
}

function generate_icons($dir, $config) {
    if (!is_dir($dir)) return;
    $source = $dir . '/' . $config['svg_name'];
    if (!is_file($source)) return;

    $source_t = filemtime($source);
    $output_t = PHP_INT_MAX;

    $outputs = [$dir . '/' . $config['ico_name']];
    foreach ($config['png_sizes'] as $size)
        $outputs[] = $dir . '/' . $config['png_name']($size);

    foreach ($outputs as $output) {
        if (!is_file($output)) {
            $output_t = 0;
            break;
        }

        $t = filemtime($output);
        if ($t < $output_t) $output_t = $t;
    }

    if ($output_t >= $source_t) return;

    $svg = file_get_contents($source);

    foreach ($config['png_sizes'] as $size) {
        $image = new Imagick();

        $image->setBackgroundColor(new ImagickPixel('transparent'));
        $image->setResolution(300, 300);
        $image->readImageBlob($svg);

        $image->setImageFormat('png');
        $image->resizeImage($size, $size, Imagick::FILTER_LANCZOS, 1);

        $output = $dir . '/' . $config['png_name']($size);
        $image->writeImage($output);
        $image->clear();
    }

    $ico = new Imagick();
    foreach ($config['ico_sizes'] as $size) {
        $image = new Imagick();

        $image->setBackgroundColor(new ImagickPixel('transparent'));
        $image->setResolution(300, 300);
        $image->readImageBlob($svg);

        $image->setImageFormat('png');
        $image->resizeImage($size, $size, Imagick::FILTER_LANCZOS, 1);

        $ico->addImage($image);
        $image->clear();
    }

    $ico->setIteratorIndex(0);
    $ico->setImageFormat('ico');
    $ico->writeImages($dir . '/' . $config['ico_name'], true);
    $ico->clear();
}
?>
