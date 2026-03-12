$ports = @(5000, 5001, 5002, 5003, 5004, 5005, 5006, 8001)
$results = @()

foreach ($p in $ports) {
    $status = "OFFLINE"
    $id = $null
    $conn = Get-NetTCPConnection -LocalPort $p -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($conn) {
        $status = "ONLINE"
        $id = $conn.OwningProcess
    }
    
    $results += [PSCustomObject]@{
        Port = $p
        Status = $status
        PID = $id
    }
}

$results | Format-Table -AutoSize
