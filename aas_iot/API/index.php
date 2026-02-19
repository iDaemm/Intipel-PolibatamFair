<?php
/* ==========================================================
   BAGIAN 1: LOGIKA API (POST & GET)
   ========================================================== */
include "../config.php"; 

// --- LOGIKA POST: TERIMA DATA & HITUNG AVG/MAX ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: application/json");
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (isset($input['device_id'], $input['temp'], $input['hum'], $input['decibel'])) {
        $dev_id = $input['device_id'];
        $tAct   = $input['temp'];
        $hAct   = $input['hum'];
        $dAct   = $input['decibel'];

        try {
            // 1. Simpan dulu ke history agar bisa dihitung AVG/MAX-nya
            $sqlLog = "INSERT INTO device_records_miqdam (device_id, temp, hum, decibel, measurement_time) VALUES (?, ?, ?, ?, NOW())";
            $conn->prepare($sqlLog)->execute([$dev_id, $tAct, $hAct, $dAct]);

            // 2. Ambil perhitungan AVG dan MAX dari history
            $stmt = $conn->prepare("SELECT 
                AVG(temp) as t_avg, MAX(temp) as t_max, 
                AVG(hum) as h_avg, MAX(hum) as h_max, 
                AVG(decibel) as d_avg, MAX(decibel) as d_max 
                FROM device_records_miqdam WHERE device_id = ?");
            $stmt->execute([$dev_id]);
            $calc = $stmt->fetch(PDO::FETCH_ASSOC);

            // 3. Update tabel utama dengan data terbaru + hasil hitung AVG/MAX
            $sqlUpd = "UPDATE devices_miqdam SET 
                temp_act = ?, temp_avg = ?, temp_max = ?, 
                hum_act = ?, hum_avg = ?, hum_max = ?, 
                decibel_act = ?, decibel_avg = ?, decibel_max = ?, 
                last_measurement = NOW() WHERE id = ?";
            $conn->prepare($sqlUpd)->execute([
                $tAct, $calc['t_avg'], $calc['t_max'], 
                $hAct, $calc['h_avg'], $calc['h_max'], 
                $dAct, $calc['d_avg'], $calc['d_max'], 
                $dev_id
            ]);

            echo json_encode(["statusCode" => 200, "message" => "Success Update & Calculate"]);
        } catch (PDOException $e) {
            echo json_encode(["statusCode" => 500, "message" => $e->getMessage()]);
        }
    }
    exit;
}

// --- LOGIKA GET: UNTUK DASHBOARD ---
if (isset($_GET['action'])) {
    header("Content-Type: application/json");
    $device_id = $_GET['device_id'] ?? 1;

    if ($_GET['action'] === 'current') {
        $stmt = $conn->prepare("SELECT * FROM devices_miqdam WHERE id = ?");
        $stmt->execute([$device_id]);
        $d = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode(["statusCode" => 200, "data" => [
            "temp" => $d['temp_act'], "tempAvg" => round($d['temp_avg'], 1), "tempMax" => $d['temp_max'],
            "hum" => $d['hum_act'], "humAvg" => round($d['hum_avg'], 1), "humMax" => $d['hum_max'],
            "noise" => $d['decibel_act'], "noiseAvg" => round($d['decibel_avg'], 1), "noiseMax" => $d['decibel_max']
        ]]);
    } 
    elseif ($_GET['action'] === 'history') {
        $stmt = $conn->prepare("SELECT measurement_time, temp, hum, decibel FROM device_records_miqdam WHERE device_id = ? ORDER BY measurement_time DESC LIMIT 15");
        $stmt->execute([$device_id]);
        echo json_encode(["statusCode" => 200, "data" => array_reverse($stmt->fetchAll(PDO::FETCH_ASSOC))]);
    }
    exit;
}

$device_id = 1;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Monitoring</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #b8d8d0; }
        .card-iot { background: #92acc4; border-radius: 2rem; padding: 2rem; color: white; box-shadow: 0 10px 15px rgba(0,0,0,0.1); }
        .chart-box { background: rgba(255, 255, 255, 0.2); border-radius: 2rem; padding: 20px; border: 1px solid rgba(255,255,255,0.3); }
    </style>
</head>
<body class="p-6">

    <header class="bg-[#5c7eb8] rounded-2xl text-white p-5 flex justify-between items-center shadow-lg mb-8">
        <h1 class="text-xl font-black uppercase tracking-widest">Device Data Monitoring</h1>
        <div class="bg-black/20 px-4 py-2 rounded-lg">Device ID: <?php echo $device_id; ?> | <span id="clock">--:--:--</span></div>
    </header>

    <main class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-12">
        <div class="card-iot text-center">
            <p class="text-xs font-bold uppercase mb-2">Temperature</p>
            <h2 class="text-6xl font-black mb-4"><span id="v-temp">--</span>°C</h2>
            <div class="text-[10px] grid grid-cols-2 bg-black/10 p-2 rounded-xl uppercase">
                <span>AVG: <span id="v-temp-avg">--</span>°C</span>
                <span class="text-orange-300">MAX: <span id="v-temp-max">--</span>°C</span>
            </div>
        </div>
        <div class="card-iot text-center">
            <p class="text-xs font-bold uppercase mb-2">Humidity</p>
            <h2 class="text-6xl font-black mb-4"><span id="v-hum">--</span>%</h2>
            <div class="text-[10px] grid grid-cols-2 bg-black/10 p-2 rounded-xl uppercase">
                <span>AVG: <span id="v-hum-avg">--</span>%</span>
                <span class="text-blue-200">MAX: <span id="v-hum-max">--</span>%</span>
            </div>
        </div>
        <div class="card-iot text-center">
            <p class="text-xs font-bold uppercase mb-2">Noise Level</p>
            <h2 class="text-6xl font-black mb-4"><span id="v-noise">--</span>dB</h2>
            <div class="text-[10px] grid grid-cols-2 bg-black/10 p-2 rounded-xl uppercase">
                <span>AVG: <span id="v-noise-avg">--</span>dB</span>
                <span class="text-red-300">MAX: <span id="v-noise-max">--</span>dB</span>
            </div>
        </div>
    </main>

    <div class="bg-black/10 p-8 rounded-[3rem] mb-12">
        <h3 class="text-white font-bold uppercase mb-6 flex items-center gap-2"><i class="fas fa-chart-line"></i> Grafik Real-time</h3>
        <div class="chart-box">
            <canvas id="iotChart" style="max-height: 350px;"></canvas>
        </div>
    </div>

    <div class="bg-black/10 p-8 rounded-[3rem]">
        <h3 class="text-white font-bold uppercase mb-6"><i class="fas fa-history"></i> Log History</h3>
        <div class="overflow-hidden rounded-2xl">
            <table class="w-full text-white text-center">
                <thead class="bg-black/30 text-[10px] uppercase">
                    <tr><th class="p-4">Time</th><th class="p-4">Temperature</th><th class="p-4">Humidity</th><th class="p-4">Decibel</th></tr>
                </thead>
                <tbody id="logBody" class="text-sm"></tbody>
            </table>
        </div>
    </div>

    <script>
        const devID = 1;
        let chart;

        function initChart() {
            const ctx = document.getElementById('iotChart').getContext('2d');
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: [],
                    datasets: [
                        { label: 'Temp', borderColor: '#fdba74', data: [], tension: 0.4 },
                        { label: 'Hum', borderColor: '#93c5fd', data: [], tension: 0.4 },
                        { label: 'Noise', borderColor: '#fca5a5', data: [], tension: 0.4 }
                    ]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { labels: { color: 'white' } } },
                    scales: {
                        x: { ticks: { color: 'white' } },
                        y: { ticks: { color: 'white' } }
                    }
                }
            });
        }

        async function updateData() {
            try {
                // Get Current Data
                const resCurr = await fetch(`index.php?action=current&device_id=${devID}`);
                const d1 = await resCurr.json();
                if(d1.statusCode === 200) {
                    const v = d1.data;
                    document.getElementById('v-temp').innerText = v.temp;
                    document.getElementById('v-temp-avg').innerText = v.tempAvg;
                    document.getElementById('v-temp-max').innerText = v.tempMax;
                    document.getElementById('v-hum').innerText = v.hum;
                    document.getElementById('v-hum-avg').innerText = v.humAvg;
                    document.getElementById('v-hum-max').innerText = v.humMax;
                    document.getElementById('v-noise').innerText = v.noise;
                    document.getElementById('v-noise-avg').innerText = v.noiseAvg;
                    document.getElementById('v-noise-max').innerText = v.noiseMax;
                }

                // Get History Data
                const resHist = await fetch(`index.php?action=history&device_id=${devID}`);
                const d2 = await resHist.json();
                if(d2.statusCode === 200) {
                    const logs = d2.data;
                    // Update Chart
                    chart.data.labels = logs.map(l => l.measurement_time.split(' ')[1]);
                    chart.data.datasets[0].data = logs.map(l => l.temp);
                    chart.data.datasets[1].data = logs.map(l => l.hum);
                    chart.data.datasets[2].data = logs.map(l => l.decibel);
                    chart.update('none');

                    // Update Table
                    const rows = [...logs].reverse().slice(0, 7).map(r => `
                        <tr class="border-b border-white/5">
                            <td class="p-3 opacity-70">${r.measurement_time}</td>
                            <td class="p-3">${r.temp}°C</td>
                            <td class="p-3">${r.hum}%</td>
                            <td class="p-3">${r.decibel} dB</td>
                        </tr>
                    `).join('');
                    document.getElementById('logBody').innerHTML = rows;
                }
            } catch (e) { console.error(e); }
        }

        window.onload = () => {
            initChart();
            updateData();
            setInterval(updateData, 3000);
            setInterval(() => { document.getElementById('clock').innerText = new Date().toLocaleTimeString(); }, 1000);
        };
    </script>
</body>
</html>