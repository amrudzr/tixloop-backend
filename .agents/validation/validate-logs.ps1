# validate-logs.ps1
#
# Purpose: Automatically validate execution log filename formats and metadata.
# Scope: Pre-commit or pre-release verification.
# Ownership: Quality Assurance.

$PSScriptRoot = Split-Path -Parent -Path $MyInvocation.MyCommand.Definition
$LogDir = Join-Path $PSScriptRoot "..\..\.antigravity\logs\execution"

if (-not (Test-Path -Path $LogDir)) {
    Write-Error "Error: Execution log directory not found at $LogDir."
    exit 1
}

# Filter to files starting with three digits and sort by LastWriteTime descending to find the actual latest log.
$LatestLogs = Get-ChildItem -Path $LogDir -Filter "*.md" | Where-Object { $_.Name -match "^\d{3}_" } | Sort-Object LastWriteTime -Descending

if ($LatestLogs.Count -eq 0) {
    Write-Host "No execution logs with sequence prefix found in $LogDir. Please create one."
    exit 1
}

$LatestLog = $LatestLogs[0]

# Expect format: 001_2026-05-23_slug-name.md
# Regex: ^\d{3}_\d{4}-\d{2}-\d{2}_[a-z0-9\-]+\.md$
if ($LatestLog.Name -notmatch "^\d{3}_\d{4}-\d{2}-\d{2}_[a-z0-9\-]+\.md$") {
    Write-Error "Error: Log filename '$($LatestLog.Name)' does not match the unified sequence format: [sequence]_[YYYY-MM-DD]_[slug].md"
    exit 1
}

Write-Host "Success: Latest log file '$($LatestLog.Name)' has valid format."
exit 0
