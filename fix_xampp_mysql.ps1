Stop-Process -Name mysqld -ErrorAction SilentlyContinue
Stop-Process -Name mysql -ErrorAction SilentlyContinue
$xamppMysql = "C:\xampp\mysql"
$dataDir = "$xamppMysql\data"
$backupDir = "$xamppMysql\backup"
$dataOldDir = "$xamppMysql\data_old"

if (Test-Path -Path $dataOldDir) {
    Remove-Item -Recurse -Force $dataOldDir
}

Rename-Item -Path $dataDir -NewName "data_old"
Copy-Item -Path $backupDir -Destination $dataDir -Recurse

$excludeFolders = @('mysql', 'performance_schema', 'phpmyadmin', 'test')
Get-ChildItem -Path $dataOldDir -Directory | Where-Object { $_.Name -notin $excludeFolders } | ForEach-Object {
    Copy-Item -Path $_.FullName -Destination "$dataDir\$($_.Name)" -Recurse -Force
}

Copy-Item -Path "$dataOldDir\ibdata1" -Destination "$dataDir\ibdata1" -Force

Write-Host "XAMPP MySQL Data Folder fixed successfully! Please start MySQL from your XAMPP Control Panel."
