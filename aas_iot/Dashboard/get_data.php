<?php
include "../config.php";
header("Content-Type: application/json");

// Pastikan ada parameter action
$action = $_GET['action'] ?? '';
$device_id = $_GET['device_id'] ?? 1;

if ($action === 'current') {
    // Ambil data terbaru untuk angka di dashboard
    $stmt = $conn->prepare("SELECT * FROM devices_miqdam WHERE id = ?");
    $stmt->execute([$device_id]);
    $d = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo json_encode([
        "statusCode" => 200, 
        "data" => [
            "temp" => $d['temp_act'], 
            "tempAvg" => round($d['temp_avg'], 1), 
            "tempMax" => $d['temp_max'],
            "hum" => $d['hum_act'], 
            "humAvg" => round($d['hum_avg'], 1), 
            "humMax" => $d['hum_max'],
            "noise" => $d['decibel_act'], 
            "noiseAvg" => round($d['decibel_avg'], 1), 
            "noiseMax" => $d['decibel_max']
        ]
    ]);
} 

elseif ($action === 'history') {
    // Ambil 15-20 data terakhir buat tabel/grafik
    $stmt = $conn->prepare("SELECT measurement_time, temp, hum, decibel FROM device_records_miqdam WHERE device_id = ? ORDER BY measurement_time DESC LIMIT 15");
    $stmt->execute([$device_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Kirim data, dibalik urutannya biar yang lama di kiri, yang baru di kanan
    echo json_encode([
        "statusCode" => 200, 
        "data" => array_reverse($results)
    ]);
} 

else {
    // Kalau action kosong atau salah
    echo json_encode([
        "statusCode" => 400, 
        "message" => "Parameter action salah (pilih current atau history)"
    ]);
}
?>