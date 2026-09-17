param(
    [string]$ProjectPath = ".",
    [string]$AppName = "App",
    [string]$Version = "0.0.0",
    [ValidateSet('website','prolocks','android-apk','windows','web','android-aab','all')][string]$Target = 'website'
)
$ErrorActionPreference = 'Stop'
Push-Location $ProjectPath
try {
    if (-not (Test-Path 'pubspec.yaml')) { throw 'Run this from a Flutter project root or pass -ProjectPath.' }

    flutter pub get
    flutter analyze
    flutter test

    function Get-ApkSigner {
        $cmd = Get-Command apksigner -ErrorAction SilentlyContinue
        if ($cmd) { return $cmd.Source }

        $sdkRoot = $env:ANDROID_SDK_ROOT
        if (-not $sdkRoot) { $sdkRoot = $env:ANDROID_HOME }
        if ($sdkRoot) {
            $candidate = Get-ChildItem -Path (Join-Path $sdkRoot 'build-tools') -Directory -ErrorAction SilentlyContinue |
                Sort-Object Name -Descending |
                ForEach-Object { Join-Path $_.FullName 'apksigner.bat' } |
                Where-Object { Test-Path $_ } |
                Select-Object -First 1
            if ($candidate) { return $candidate }
        }
        return $null
    }

    function Assert-ProductionSignedApk([string]$ApkPath) {
        if (-not (Test-Path $ApkPath)) { throw "APK not found: $ApkPath" }
        $apksigner = Get-ApkSigner
        if (-not $apksigner) {
            throw 'Cannot verify APK signing because apksigner was not found. Install Android SDK Build-Tools or add apksigner to PATH. Website delivery must not be declared complete without signing verification.'
        }
        $verify = & $apksigner verify --verbose --print-certs $ApkPath 2>&1
        if ($LASTEXITCODE -ne 0) { throw "APK signing verification failed:`n$verify" }
        $text = ($verify | Out-String)
        if ($text -match 'CN=Android Debug' -or $text -match 'Android Debug') {
            throw 'Website APK is signed with an Android debug certificate. A production/release signing key is required.'
        }
        Write-Host "Verified signed production APK: $ApkPath"
    }

    function Stage-WebsiteApk {
        $src = 'build/app/outputs/flutter-apk/app-release.apk'
        Assert-ProductionSignedApk $src
        $destDir = 'completed/Website_Delivery/Android'
        New-Item -ItemType Directory -Force -Path $destDir | Out-Null
        $dest = Join-Path $destDir "$AppName-$Version-android-signed-prolocked.apk"
        Copy-Item $src $dest -Force
        Write-Host "Website APK staged (PRO LOCKS ON / WEBSITE entitlement): $dest"
    }

    function Stage-GooglePlayAab {
        $src = 'build/app/outputs/bundle/release/app-release.aab'
        if (-not (Test-Path $src)) { throw "Google Play AAB not found: $src" }
        $destDir = 'completed/PROLOCKS/Google_Play'
        New-Item -ItemType Directory -Force -Path $destDir | Out-Null
        $dest = Join-Path $destDir "$AppName-$Version-google-play-prolocked.aab"
        Copy-Item $src $dest -Force
        Write-Host "Google Play AAB staged (PRO LOCKS ON): $dest"
    }

    function Build-WebsiteAndroidApk {
        flutter build apk --release --dart-define=PRO_LOCKS_ENABLED=true --dart-define=ENTITLEMENT_CHANNEL=WEBSITE
        if ($LASTEXITCODE -ne 0) { throw 'Website APK build failed.' }
        Stage-WebsiteApk   # stage NOW before any Pro-locked APK can overwrite app-release.apk
    }
    function Build-WebsiteWindowsInstaller {
        flutter build windows --release --dart-define=PRO_LOCKS_ENABLED=true --dart-define=ENTITLEMENT_CHANNEL=WEBSITE
        if ($LASTEXITCODE -ne 0) { throw 'Website Windows build failed.' }
        $pubspec = Get-Content 'pubspec.yaml' -Raw
        if ($pubspec -match '(?m)^\s*msix\s*:') {
            dart run msix:create
        } else {
            Write-Warning 'Windows release built, but MSIX packaging is not configured. Configure the msix package/publisher identity before claiming a customer installer is complete.'
        }
    }
    function Build-WebsiteWebDemo {
        flutter build web --release --dart-define=PRO_LOCKS_ENABLED=true --dart-define=ENTITLEMENT_CHANNEL=WEBSITE
        if ($LASTEXITCODE -ne 0) { throw 'Website Web/PWA build failed.' }
    }
    function Build-GooglePlayAab {
        flutter build appbundle --release --dart-define=PRO_LOCKS_ENABLED=true --dart-define=ENTITLEMENT_CHANNEL=GOOGLE_PLAY
        if ($LASTEXITCODE -ne 0) { throw 'Google Play AAB build failed.' }
        Stage-GooglePlayAab
    }
    switch ($Target) {
        'android-apk' { Build-WebsiteAndroidApk }
        'windows' { Build-WebsiteWindowsInstaller }
        'web' { Build-WebsiteWebDemo }
        'android-aab' { Build-GooglePlayAab }
        'website' {
            Build-WebsiteAndroidApk
            Build-WebsiteWindowsInstaller
            Build-WebsiteWebDemo
        }
        'prolocks' {
            # Google Play release is AAB ONLY and must retain Pro locks.
            Build-GooglePlayAab
        }
        'all' {
            # Website APK MUST be built and staged first, using WEBSITE entitlement.
            Build-WebsiteAndroidApk
            Build-WebsiteWindowsInstaller
            Build-WebsiteWebDemo
            # Play artifacts are built afterward and can never overwrite the already-staged website APK.
            Build-GooglePlayAab
        }
    }

    Write-Host ''
    Write-Host 'Required Android release split:'
    Write-Host '  WEBSITE: signed APK; PRO_LOCKS_ENABLED=true; ENTITLEMENT_CHANNEL=WEBSITE; free install + website Pro unlock.'
    Write-Host '  GOOGLE PLAY: AAB; PRO_LOCKS_ENABLED=true; ENTITLEMENT_CHANNEL=GOOGLE_PLAY; Play entitlement active.'
    Write-Host 'WEBSITE builds: APK + Windows + Web/PWA are Pro-locked and use website licensing. GOOGLE PLAY: separate Pro-locked AAB using Play entitlement.'
} finally { Pop-Location }
