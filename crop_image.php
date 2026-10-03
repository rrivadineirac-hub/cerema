<?php
$srcPath = 'C:/Users/Administrador/.gemini/antigravity-ide/brain/be75fe82-4718-4c30-b391-06b23ecdce7e/media__1791054721900.png';
$outDir = 'C:/Users/Administrador/.gemini/antigravity-ide/brain/be75fe82-4718-4c30-b391-06b23ecdce7e/';

$src = imagecreatefrompng($srcPath);
$w = imagesx($src);
$h = imagesy($src);

echo "Width: $w, Height: $h\n";

$sliceHeight = ceil($h / 4);

for ($i = 0; $i < 4; $i++) {
    $y = $i * $sliceHeight;
    $currH = min($sliceHeight, $h - $y);
    if ($currH <= 0) break;

    $dest = imagecreatetruecolor($w, $currH);
    imagecopy($dest, $src, 0, 0, 0, $y, $w, $currH);
    $outPath = $outDir . "mantenimiento_part" . ($i + 1) . ".png";
    imagepng($dest, $outPath);
    imagedestroy($dest);
    echo "Saved part " . ($i + 1) . " (Y: $y to " . ($y + $currH) . ")\n";
}
imagedestroy($src);
