$ErrorActionPreference = "Stop"

docker compose down

if ($LASTEXITCODE -ne 0) {
    throw "Docker Compose shutdown failed."
}

Write-Host "[PASS] Containers stopped. Persistent volumes were preserved."