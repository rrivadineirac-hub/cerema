# deploy.ps1 - Script PowerShell para ejecutar el despliegue automático
Set-Location -Path $PSScriptRoot

Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host "  DESPLEGADOR AUTOMÁTICO CEREMA -> crema.free.je" -ForegroundColor Cyan
Write-Host "=======================================================" -ForegroundColor Cyan
Write-Host ""

if (-not (Test-Path -Path ".env.deploy")) {
    Write-Host "⚠️  No existe '.env.deploy'. Copiando plantilla..." -ForegroundColor Yellow
    Copy-Item -Path ".env.deploy.example" -Destination ".env.deploy"
    Write-Host "Por favor edita c:\xampp\htdocs\cerema\.env.deploy con tus datos de InfinityFree y vuelve a ejecutar este script." -ForegroundColor Yellow
    exit
}

php deploy.php
