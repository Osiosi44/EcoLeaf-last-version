$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$trackedFiles = git -C $projectRoot ls-tree -r --name-only HEAD
if ($LASTEXITCODE -ne 0) { throw 'Could not read the repository file list.' }
$missingFiles = @($trackedFiles | Where-Object { -not (Test-Path -LiteralPath (Join-Path $projectRoot $_)) })
if ($missingFiles.Count) { throw "The checkout is incomplete. Download these files before packaging: $($missingFiles -join ', ')" }
$outputDirectory = Join-Path $projectRoot 'artifacts'
New-Item -ItemType Directory -Force -Path $outputDirectory | Out-Null
$archivePath = Join-Path $outputDirectory 'ecoleaf-hosting.zip'
$sourceFiles = @(
    Get-ChildItem -LiteralPath $projectRoot -File | Where-Object { $_.Extension -in '.php', '.css', '.js' }
    Get-Item -LiteralPath (Join-Path $projectRoot '.htaccess')
    Get-Item -LiteralPath (Join-Path $projectRoot 'admin'), (Join-Path $projectRoot 'components'), (Join-Path $projectRoot 'db'), (Join-Path $projectRoot 'images')
)
if (Test-Path -LiteralPath (Join-Path $projectRoot 'db/config.local.php')) {
    throw 'Temporarily move db/config.local.php outside the project before packaging, so credentials are not included.'
}
Add-Type -AssemblyName System.IO.Compression.FileSystem
Add-Type -AssemblyName System.Drawing
if (Test-Path -LiteralPath $archivePath) { Remove-Item -LiteralPath $archivePath }
$archive = [System.IO.Compression.ZipFile]::Open($archivePath, 'Create')
try {
    foreach ($item in $sourceFiles) {
        $files = if ($item.PSIsContainer) { Get-ChildItem -LiteralPath $item.FullName -File -Recurse } else { @($item) }
        foreach ($file in $files) {
            $relativePath = $file.FullName.Substring($projectRoot.Length + 1).Replace('\', '/')
            if ($relativePath.StartsWith('images/') -and $file.Length -gt 1MB -and $file.Extension -in '.jpg', '.jpeg', '.png') {
                $original = [System.Drawing.Image]::FromFile($file.FullName)
                $resized = $null
                $graphics = $null
                $stream = $null
                try {
                    $scale = [Math]::Min([double]1, [double](1600.0 / [Math]::Max($original.Width, $original.Height)))
                    $targetWidth = [Math]::Max(1, [int]($original.Width * $scale))
                    $targetHeight = [Math]::Max(1, [int]($original.Height * $scale))
                    $resized = [System.Drawing.Bitmap]::new($targetWidth, $targetHeight)
                    $graphics = [System.Drawing.Graphics]::FromImage($resized)
                    $graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
                    $graphics.DrawImage($original, 0, 0, $resized.Width, $resized.Height)
                    $entry = $archive.CreateEntry($relativePath)
                    $stream = $entry.Open()
                    $format = if ($file.Extension -eq '.png') { [System.Drawing.Imaging.ImageFormat]::Png } else { [System.Drawing.Imaging.ImageFormat]::Jpeg }
                    $resized.Save($stream, $format)
                } finally {
                    if ($stream) { $stream.Dispose() }
                    if ($graphics) { $graphics.Dispose() }
                    if ($resized) { $resized.Dispose() }
                    $original.Dispose()
                }
            } else {
                [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive, $file.FullName, $relativePath) | Out-Null
            }
        }
    }
} finally { $archive.Dispose() }
Copy-Item -LiteralPath (Join-Path $projectRoot 'database/init.sql') -Destination (Join-Path $outputDirectory 'ecoleaf-database.sql') -Force
Write-Output "Website: $archivePath"
Write-Output "Database: $(Join-Path $outputDirectory 'ecoleaf-database.sql')"
