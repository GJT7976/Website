param([string]$ProjectPath = ".", [string]$AppName = "App", [string]$Version = "0.0.0")
$ErrorActionPreference='Stop'
Push-Location $ProjectPath
try {
    $root = Join-Path (Get-Location) 'completed/PROLOCKS'
    $play = Join-Path $root 'Google_Play'
    New-Item -ItemType Directory -Force -Path $play | Out-Null

    $aab = 'build/app/outputs/bundle/release/app-release.aab'
    if (Test-Path $aab) {
        Copy-Item $aab (Join-Path $play "$AppName-$Version-google-play.aab") -Force
    } else {
        Write-Warning 'Google Play AAB not found. Run scripts/build_release.ps1 -Target prolocks first.'
    }

    $notes = @"
# PROLOCKS Release Notes

App: $AppName
Version: $Version
Release channel: Google Play
Required build flag: PRO_LOCKS_ENABLED=true

- `Google_Play/` contains the AAB intended for Google Play upload.
- This package contains the Google Play AAB only.
- The AAB MUST retain Pro/Premium locks because it is the Google Play sale build.
- Website APK, Windows, and Web/PWA builds are separate and MUST have all Pro locks disabled.
"@
    Set-Content -Path (Join-Path $root 'RELEASE_NOTES.md') -Value $notes -Encoding UTF8
    Write-Host "PROLOCKS release staged at: $root"
} finally { Pop-Location }
