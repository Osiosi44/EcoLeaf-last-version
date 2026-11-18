$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$outputDirectory = Join-Path $projectRoot 'artifacts'
New-Item -ItemType Directory -Force -Path $outputDirectory | Out-Null
$archivePath = Join-Path $outputDirectory 'ecoleaf-hosting.zip'
# Explicit shipping files keep credentials, development tooling, and obsolete assets out.
$applicationFiles = @(
    '.htaccess', 'index.php', 'home.php', 'about.php', 'package.php', 'booking.php',
    'process_order.php', 'my_orders.php', 'login.php', 'profile.php', 'privacy.php', '404.php',
    'styles.css', 'script.js', 'components/layout.php',
    'admin/adminDash.php', 'admin/actions.php', 'admin/orders.php', 'admin/users.php',
    'admin/services.php', 'admin/account.php',
    'db/app.php', 'db/config_session.php', 'db/db_con.php', 'db/config.example.php',
    'db/db_login.php', 'db/db_signup.php', 'db/db_logout.php', 'db/db_deleteUser.php',
    'db/db_review.php', 'db/cancel_order.php', 'db/profile/profile_update.php',
    'db/profile/profile_fetch.php', 'images/leaf.png'
)
foreach ($relativePath in $applicationFiles) {
    if (-not (Test-Path -LiteralPath (Join-Path $projectRoot $relativePath) -PathType Leaf)) {
        throw "Required application file is missing: $relativePath"
    }
}
$assetRoot = Join-Path $projectRoot 'assets'
$activePhotos = @('washer-poster.jpg','home-laundry.jpg','about-garment-care.jpg','dry-cleaning.jpg','wash-fold.jpg','alterations.jpg','delicate-care.jpg','ironing.jpg')
$assets = @(Get-ChildItem -LiteralPath $assetRoot -File -Recurse | Where-Object { $_.Extension -ne '.md' -and ($_.DirectoryName -ne (Join-Path $assetRoot 'photos') -or $_.Name -in $activePhotos) })
foreach ($photo in $activePhotos) {
    if (-not (Test-Path -LiteralPath (Join-Path $assetRoot ('photos/' + $photo)))) { throw "Required photo is missing: $photo" }
}
foreach ($requiredAsset in @('assets/washer.js','assets/vendor/three.module.min.js','assets/vendor/three-LICENSE.txt','assets/fonts/Manrope-Variable.ttf','assets/fonts/OFL.txt','assets/photos/home-laundry.jpg')) {
    if (-not (Test-Path -LiteralPath (Join-Path $projectRoot $requiredAsset) -PathType Leaf)) { throw "Required asset is missing: $requiredAsset" }
}
Add-Type -AssemblyName System.IO.Compression.FileSystem
if (Test-Path -LiteralPath $archivePath) { Remove-Item -LiteralPath $archivePath }
$archive = [System.IO.Compression.ZipFile]::Open($archivePath, 'Create')
try {
    foreach ($relativePath in $applicationFiles) {
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive,(Join-Path $projectRoot $relativePath),$relativePath) | Out-Null
    }
    foreach ($file in $assets) {
        $relativePath = $file.FullName.Substring($projectRoot.Length + 1).Replace('\','/')
        [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($archive,$file.FullName,$relativePath) | Out-Null
    }
} finally { $archive.Dispose() }
Copy-Item -LiteralPath (Join-Path $projectRoot 'database/init.sql') -Destination (Join-Path $outputDirectory 'ecoleaf-database.sql') -Force
Copy-Item -LiteralPath (Join-Path $projectRoot 'database/migrations/20261118_security.sql') -Destination (Join-Path $outputDirectory 'ecoleaf-upgrade.sql') -Force
Copy-Item -LiteralPath (Join-Path $projectRoot 'docs/DEPLOYMENT.md') -Destination (Join-Path $outputDirectory 'DEPLOYMENT.md') -Force
Write-Output "Website: $archivePath"
Write-Output "Existing database upgrade: $(Join-Path $outputDirectory 'ecoleaf-upgrade.sql')"
Write-Output "New installations only: $(Join-Path $outputDirectory 'ecoleaf-database.sql')"
Write-Output 'db/config.local.php is excluded. Keep the hosted copy unchanged.'
