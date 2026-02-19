<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>IoT Monitoring - Static View</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;700;900&display=swap');
    body { background-color: #b8d8d0; font-family: 'Inter', sans-serif; margin: 0; overflow: hidden; }
    .card-bg { background: #92acc4; box-shadow: 15px 15px 30px rgba(0,0,0,0.1); }
    .header-bg { background-color: #5c7eb8; }
    .nav-btn { transition: all 0.3s ease; border-width: 2px; border-color: white; }
    .nav-btn.active { background-color: white; color: #5c7eb8; transform: scale(1.05); }
    .label-pill { background: #b8d8d0; color: #333; padding: 4px 25px; border-radius: 99px; font-weight: 900; display: inline-block; }
    
    #page-history::-webkit-scrollbar { width: 8px; }
    #page-history::-webkit-scrollbar-thumb { background: #5c7eb8; border-radius: 10px; }
    
    .status-indicator {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      background-color: #f59e0b; /* Kuning standby */
      animation: pulse 2s infinite;
      display: inline-block;
      margin-right: 8px;
    }
    
    @keyframes pulse {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.5; }
    }

    .status-badge {
      position: fixed;
      top: 100px;
      right: 20px;
      background: rgba(255, 255, 255, 0.95);
      padding: 10px 18px;
      border-radius: 25px;
      font-size: 13px;
      font-weight: bold;
      z-index: 1000;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      color: #f59e0b;
      border: 2px solid #f59e0b;
    }
  </style>
</head>
<body class="h-screen flex flex-col p-4 md:p-6 gap-4">

  <div id="statusBadge" class="status-badge">
    <i class="fas fa-pause-circle"></i> Manual Input Mode (Postman)
  </div>

  <header class="header-bg rounded-2xl text-white px-10 py-4 flex justify-between items-center shadow-lg">
    <div class="flex items-center gap-6">
      <div class="flex items-center gap-3">
        <span class="status-indicator"></span>
        <h1 id="header-title" class="text-2xl font-black uppercase tracking-wider">Device Data Monitoring</h1>
      </div>
      <nav class="flex gap-4 ml-10">
        <button onclick="showPage('dashboard')" id="nav-dash" class="nav-btn active px-6 py-2 rounded-full font-bold uppercase text-sm">Dashboard</button>
        <button onclick="showPage('history')" id="nav-hist" class="nav-btn px-6 py-2 rounded-full font-bold uppercase text-sm">History & Chart</button>
      </nav>
    </div>
    <div id="timestamp" class="font-mono text-2xl font-bold opacity-90">-- --- ----, --:--:--</div>
  </header>

  <main id="page-dashboard" class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
    <div class="card-bg rounded-[3.5rem] p-8 text-white flex flex-col justify-between">
      <h2 class="text-3xl font-black text-center tracking-widest opacity-80 uppercase">Temperature</h2>
      <div class="text-[8rem] font-black text-center leading-none"><span id="tempMain">0</span><span class="text-4xl">°C</span></div>
      <div class="grid grid-cols-1 gap-2 font-bold bg-black/10 p-6 rounded-[2.5rem] backdrop-blur-md">
        <div class="flex justify-between text-2xl border-b border-white/10 pb-2"><span>AVG</span><span id="tempAvg">: 0°C</span></div>
        <div class="flex justify-between text-2xl"><span>MAX</span><span id="tempMax" class="text-orange-300">: 0°C</span></div>
      </div>
    </div>

    <div class="card-bg rounded-[3.5rem] p-8 text-white flex flex-col justify-between">
      <h2 class="text-3xl font-black text-center tracking-widest opacity-80 uppercase">Humidity</h2>
      <div class="text-[8rem] font-black text-center leading-none"><span id="humMain">0</span><span class="text-4xl">%</span></div>
      <div class="grid grid-cols-1 gap-2 font-bold bg-black/10 p-6 rounded-[2.5rem] backdrop-blur-md">
        <div class="flex justify-between text-2xl border-b border-white/10 pb-2"><span>AVG</span><span id="humAvg">: 0%</span></div>
        <div class="flex justify-between text-2xl"><span>MAX</span><span id="humMax" class="text-blue-200">: 0%</span></div>
      </div>
    </div>

    <div class="card-bg rounded-[3.5rem] p-8 text-white flex flex-col justify-between">
      <h2 class="text-3xl font-black text-center tracking-widest opacity-80 uppercase">Decibel</h2>
      <div class="text-[8rem] font-black text-center leading-none"><span id="decibelMain">0</span><span class="text-4xl">dB</span></div>
      <div class="grid grid-cols-1 gap-2 font-bold bg-black/10 p-6 rounded-[2.5rem] backdrop-blur-md">
        <div class="flex justify-between text-2xl border-b border-white/10 pb-2"><span>AVG</span><span id="decibelAvg">: 0 dB</span></div>
        <div class="flex justify-between text-2xl"><span>MAX</span><span id="decibelMax" class="text-red-300">: 0 dB</span></div>
      </div>
    </div>
  </main>

  <main id="page-history" class="hidden flex-1 flex flex-col gap-4 overflow-y-auto pr-2">
    <div class="flex justify-end">
        <div class="bg-white/40 px-4 py-1 rounded-lg text-sm font-bold flex items-center gap-2">
            <i class="fa-regular fa-calendar"></i> <span id="currentDate">-- --- ----</span>
        </div>
    </div>

    <div class="card-bg rounded-3xl p-6 flex flex-col h-[320px] shrink-0">
      <div class="mb-4"><span class="label-pill uppercase text-xs tracking-widest" style="background:#ff5252; color:white;">Temperature</span></div>
      <div class="flex-1 min-h-0"><canvas id="chartTemp"></canvas></div>
    </div>

    <div class="card-bg rounded-3xl p-6 flex flex-col h-[320px] shrink-0">
      <div class="mb-4"><span class="label-pill uppercase text-xs tracking-widest" style="background:#42a5f5; color:white;">Humidity</span></div>
      <div class="flex-1 min-h-0"><canvas id="chartHum"></canvas></div>
    </div>

    <div class="card-bg rounded-3xl p-6 flex flex-col h-[320px] shrink-0">
      <div class="mb-4"><span class="label-pill uppercase text-xs tracking-widest" style="background:#66bb6a; color:white;">Decibel</span></div>
      <div class="flex-1 min-h-0"><canvas id="chartDecibel"></canvas></div>
    </div>
  </main>

  <div class="card-bg rounded-3xl p-6 shrink-0">
    <h3 class="font-black text-white mb-4 uppercase tracking-widest">
      Log Data History
    </h3>
    <div class="overflow-x-auto">
      <table class="w-full text-white text-center border-collapse">
        <thead class="bg-black/30">
          <tr>
            <th class="p-2">No</th>
            <th class="p-2">Time</th>
            <th class="p-2">Temperature (°C)</th>
            <th class="p-2">Humidity (%)</th>
            <th class="p-2">Decibel (dB)</th>
          </tr>
        </thead>
        <tbody id="logTableBody" class="bg-black/10">
          <tr><td colspan="5" class="p-4 opacity-50 italic">Silahkan input data melalui Postman ke Database</td></tr>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    let historyData = { labels: [], temp: [], hum: [], decibel: [] };
    let chartTemp, chartHum, chartDecibel;

    function initCharts() {
      const getAxisY = (minVal, maxVal, step, unit) => ({
        min: minVal, max: maxVal,
        grid: { color: 'rgba(255,255,255,0.1)' },
        border: { display: true, color: '#fff', width: 2 },
        ticks: { stepSize: step, color: '#fff', font: { weight: 'bold' }, callback: (v) => v + unit }
      });
      const axisX = { grid: { color: 'rgba(255,255,255,0.1)' }, ticks: { color: '#fff' } };
      const getConfig = (color) => ({
        borderColor: color, borderWidth: 3, pointRadius: 5, pointBackgroundColor: '#fff', tension: 0.3, fill: false
      });

      chartTemp = new Chart(document.getElementById('chartTemp'), {
        type: 'line',
        data: { labels: historyData.labels, datasets: [{ ...getConfig('#ff5252'), data: historyData.temp }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: getAxisY(0, 50, 10, '°C'), x: axisX } }
      });

      chartHum = new Chart(document.getElementById('chartHum'), {
        type: 'line',
        data: { labels: historyData.labels, datasets: [{ ...getConfig('#42a5f5'), data: historyData.hum }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: getAxisY(0, 100, 20, '%'), x: axisX } }
      });

      chartDecibel = new Chart(document.getElementById('chartDecibel'), {
        type: 'line',
        data: { labels: historyData.labels, datasets: [{ ...getConfig('#66bb6a'), data: historyData.decibel }] },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: getAxisY(0, 100, 20, ' dB'), x: axisX } }
      });
    }

    function showPage(page) {
      const dash = document.getElementById('page-dashboard');
      const hist = document.getElementById('page-history');
      const title = document.getElementById('header-title');
      
      if(page === 'dashboard') {
        dash.classList.remove('hidden'); hist.classList.add('hidden');
        document.getElementById('nav-dash').classList.add('active');
        document.getElementById('nav-hist').classList.remove('active');
        title.innerText = "Device Data Monitoring";
      } else {
        dash.classList.add('hidden'); hist.classList.remove('hidden');
        document.getElementById('nav-dash').classList.remove('active');
        document.getElementById('nav-hist').classList.add('active');
        title.innerText = "Device Data History";
        setTimeout(() => { 
            chartTemp.update(); chartHum.update(); chartDecibel.update(); 
        }, 200);
      }
    }

    window.onload = () => {
      initCharts();
      // Update jam/waktu saja
      setInterval(() => {
        document.getElementById('timestamp').innerText = new Date().toLocaleString('id-ID').toUpperCase();
        document.getElementById('currentDate').innerText = new Date().toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
      }, 1000);
      
      console.log('✅ Halaman siap. Silahkan kirim data sensor via API Postman.');
      console.log('ℹ️ Real-time fetch dinonaktifkan agar tidak mengganggu proses input.');
    };
  </script>
</body>
</html>