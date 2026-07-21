<?php
// Generador de Iconos PWA PNG con GD Library
$assetsDir = __DIR__ . '/../assets';
if (!file_exists($assetsDir)) {
    mkdir($assetsDir, 0777, true);
}

function makeIcon($size, $outputPath) {
    $img = imagecreatetruecolor($size, $size);
    
    // Background Dark Gold Gradient Fill
    $bgDark = imagecolorallocate($img, 10, 10, 12);
    $gold = imagecolorallocate($img, 212, 175, 55);
    $goldLight = imagecolorallocate($img, 243, 208, 117);
    $white = imagecolorallocate($img, 255, 255, 255);

    imagefill($img, 0, 0, $bgDark);

    // Rounded rectangle border
    $borderMargin = (int)($size * 0.08);
    $borderRadius = (int)($size * 0.15);
    imagefilledellipse($img, (int)($size/2), (int)($size/2), (int)($size * 0.78), (int)($size * 0.78), $gold);
    imagefilledellipse($img, (int)($size/2), (int)($size/2), (int)($size * 0.70), (int)($size * 0.70), $bgDark);

    // Inner symbol: Wallet / Currency Symbol '$'
    $fontFile = 5; // Built-in GD font
    $text = "$";
    $cx = (int)($size / 2) - 8;
    $cy = (int)($size / 2) - 14;
    if ($size > 200) {
        $cx = (int)($size / 2) - 16;
        $cy = (int)($size / 2) - 24;
    }
    imagestring($img, $fontFile, $cx, $cy, $text, $goldLight);

    imagepng($img, $outputPath);
    imagedestroy($img);
}

makeIcon(192, $assetsDir . '/icon-192.png');
makeIcon(512, $assetsDir . '/icon-512.png');

echo "Iconos PWA creados exitosamente.\n";
?>
