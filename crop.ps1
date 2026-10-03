Add-Type -AssemblyName System.Drawing
$srcPath = 'C:\Users\Administrador\.gemini\antigravity-ide\brain\be75fe82-4718-4c30-b391-06b23ecdce7e\media__1791054721900.png'
$bmp = [System.Drawing.Bitmap]::FromFile($srcPath)
$w = $bmp.Width
$h = $bmp.Height
Write-Host "Width: $w, Height: $h"

$sliceH = [math]::Ceiling($h / 4)

for ($i = 0; $i -lt 4; $i++) {
    $y = $i * $sliceH
    $curH = [math]::Min($sliceH, $h - $y)
    if ($curH -le 0) { break }

    $rect = New-Object System.Drawing.Rectangle(0, $y, $w, $curH)
    $cropBmp = $bmp.Clone($rect, $bmp.PixelFormat)
    $outPath = "C:\Users\Administrador\.gemini\antigravity-ide\brain\be75fe82-4718-4c30-b391-06b23ecdce7e\mantenimiento_part_$($i+1).png"
    $cropBmp.Save($outPath, [System.Drawing.Imaging.ImageFormat]::Png)
    $cropBmp.Dispose()
    Write-Host "Saved part_$($i+1).png"
}
$bmp.Dispose()
