param([string]$ProjectPath = ".", [string]$AppName = "App", [string]$Version = "0.0.0")
$ErrorActionPreference='Stop'
Push-Location $ProjectPath
try {
    $dest = Join-Path (Get-Location) 'completed/Website_Delivery'
    $android = Join-Path $dest 'Android'; $windows = Join-Path $dest 'Windows'; $web = Join-Path $dest 'Web_Demo/web'
    New-Item -ItemType Directory -Force -Path $android,$windows,$web | Out-Null

    $apk = 'build/app/outputs/flutter-apk/app-release.apk'
    if (Test-Path $apk) { Copy-Item $apk (Join-Path $android "$AppName-$Version-android.apk") -Force }
    else { Write-Warning 'Signed release APK not found.' }

    $msix = Get-ChildItem -Path 'build' -Recurse -File -ErrorAction SilentlyContinue | Where-Object { $_.Extension -in '.msix','.msixbundle' } | Select-Object -First 1
    if ($msix) { Copy-Item $msix.FullName (Join-Path $windows $msix.Name) -Force }
    else { Write-Warning 'MSIX/MSIXBundle not found. Do not claim Windows customer installer is complete.' }

    if (Test-Path 'build/web') { Copy-Item 'build/web/*' $web -Recurse -Force }
    else { Write-Warning 'Web build not found.' }

    $manifest = @"
# Website Upload Manifest

App: $AppName
Version: $Version
Release channel: Direct website sale
Required build flags: PRO_LOCKS_ENABLED=true; ENTITLEMENT_CHANNEL=WEBSITE

- Android: customer signed APK in `Android/`
- Windows: customer installer in `Windows/`
- Web/PWA demo: deploy contents of `Web_Demo/web/` to the configured demo URL/path
- This website edition is a free download with Pro locks active. Niagara Indie Apps website entitlement unlocks Pro; Google Play Billing must not control this edition.
- AAB and Flutter source are NOT customer downloads.

Verify signing, SHA-256 checksums, production demo URL, minimum OS/browser requirements, and release notes before upload.
"@
    Set-Content -Path (Join-Path $dest 'WEBSITE_UPLOAD_MANIFEST.md') -Value $manifest -Encoding UTF8

    New-Item -ItemType Directory -Force -Path 'source_release' | Out-Null
    Write-Host 'Website delivery staged. Create the private source ZIP separately under source_release/ after verification.'
} finally { Pop-Location }
