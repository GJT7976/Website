param([string]$ProjectPath = ".", [switch]$Test)
$ErrorActionPreference = 'Stop'
Push-Location $ProjectPath
try {
    if (-not (Test-Path 'pubspec.yaml')) { throw 'Run this from a Flutter project root or pass -ProjectPath.' }
    dart format --output=none --set-exit-if-changed lib test 2>$null
    flutter analyze
    if ($Test) { flutter test }
} finally { Pop-Location }
