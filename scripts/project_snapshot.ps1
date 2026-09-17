param([string]$ProjectPath = ".")
$ErrorActionPreference = 'Stop'
Push-Location $ProjectPath
try {
    Write-Output "=== PROJECT SNAPSHOT ==="
    Write-Output "Path: $(Get-Location)"
    if (Test-Path '.git') {
        Write-Output "`n--- git status --short ---"
        git status --short
        Write-Output "`n--- changed files ---"
        git diff --name-only
    }
    Write-Output "`n--- top-level files ---"
    Get-ChildItem -Force | Where-Object { $_.Name -notin @('.git','build','.dart_tool') } | Select-Object -ExpandProperty Name
    if (Test-Path 'pubspec.yaml') {
        Write-Output "`n--- Flutter identity ---"
        Select-String -Path 'pubspec.yaml' -Pattern '^(name|version):' | ForEach-Object { $_.Line }
    }
} finally { Pop-Location }
