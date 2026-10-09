$ErrorActionPreference = "Stop"

docker compose up -d --build --wait

if ($LASTEXITCODE -ne 0) {
    throw "Docker Compose failed to start."
}

Write-Host ""
Write-Host "[PASS] CIM stack is running."
Write-Host ""
Write-Host "Public  : http://localhost:8080"
Write-Host "CMS     : http://localhost:8080/admin"
Write-Host "Mailpit : http://localhost:8025"
Write-Host ""