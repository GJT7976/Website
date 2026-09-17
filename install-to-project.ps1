param(
    [Parameter(Mandatory=$true)][string]$ProjectPath,
    [switch]$Force
)
$ErrorActionPreference = 'Stop'
$source = Split-Path -Parent $MyInvocation.MyCommand.Path
$target = (Resolve-Path $ProjectPath).Path
if (-not (Test-Path (Join-Path $target 'pubspec.yaml'))) {
    Write-Warning "No pubspec.yaml found at project root: $target"
}
$items = @('CLAUDE.md','PROJECT_SPEC.md','README_FIRST.md','docs','scripts')
foreach ($item in $items) {
    $src = Join-Path $source $item
    $dst = Join-Path $target $item
    if ((Test-Path $dst) -and -not $Force) {
        Write-Host "SKIP existing: $dst"
        continue
    }
    if (Test-Path $dst) { Remove-Item $dst -Recurse -Force }
    Copy-Item $src $dst -Recurse -Force
    Write-Host "COPIED: $item"
}
Write-Host "Starter installed to: $target"
Write-Host "Next: edit PROJECT_SPEC.md, then start Claude Code from that project root."
