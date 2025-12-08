<x-master>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Autonomous Vacuum Control Panel</h1>
            <span class="badge badge-success badge-lg">Online</span>
        </div>

        <!-- Notification Container -->
        <div id="notification-container" class="mb-3"></div>

        <!-- Status Robot Card -->
        <div class="row mb-4">
            <div class="col-lg-6">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-primary font-weight-bold text-uppercase mb-1">
                            Status Robot
                        </div>
                        <div class="h3 mb-0">
                            <span class="badge" id="statusRobot">Standby</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Battery Monitoring Card -->
            <div class="col-lg-6">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="text-info font-weight-bold text-uppercase mb-1">
                            Status Baterai
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="h3 mb-0 mr-3" id="batteryPercent">85%</div>
                            <div class="progress" style="flex: 1; height: 25px;">
                                <div class="progress-bar bg-success" id="batteryBar" role="progressbar" 
                                     style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100">
                                </div>
                            </div>
                        </div>
                        <small class="text-muted mt-2 d-block" id="batteryEstimate">Estimasi: 3 jam 45 menit</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Control Section -->
        <div class="row mb-4">
            <!-- Kontrol Utama -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-primary">
                        <h6 class="m-0 font-weight-bold text-white">Kontrol Utama</h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-4">
                            <div class="col-md-4 mb-3">
                                <button class="btn btn-success btn-lg btn-block py-4" id="startBtn" onclick="startVacuum()">
                                    <i class="fas fa-play fa-2x mb-2 d-block"></i>
                                    <strong>MULAI</strong>
                                </button>
                            </div>
                            <div class="col-md-4 mb-3">
                                <button class="btn btn-danger btn-lg btn-block py-4" id="stopBtn" onclick="stopVacuum()">
                                    <i class="fas fa-stop fa-2x mb-2 d-block"></i>
                                    <strong>BERHENTI</strong>
                                </button>
                            </div>
                            <div class="col-md-4 mb-3">
                                <button class="btn btn-warning btn-lg btn-block py-4" id="homeBtn" onclick="returnToBase()">
                                    <i class="fas fa-home fa-2x mb-2 d-block"></i>
                                    <strong>KEMBALI</strong>
                                </button>
                            </div>
                        </div>
                        
                        <!-- Status Informasi -->
                        <div class="alert alert-info" id="statusInfo" role="alert">
                            <strong>Status:</strong> Siap digunakan
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pengaturan Daya Hisap -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-info">
                        <h6 class="m-0 font-weight-bold text-white">Daya Hisap</h6>
                    </div>
                    <div class="card-body">
                        <div class="btn-group-vertical w-100" role="group">
                            <button type="button" class="btn btn-outline-primary text-left py-3 border-bottom" 
                                    onclick="setPowerMode('eco')" id="ecoBtn">
                                <div class="font-weight-bold">ECO</div>
                                <small>Hemat Energi</small>
                            </button>
                            <button type="button" class="btn btn-outline-primary text-left py-3 border-bottom active" 
                                    onclick="setPowerMode('normal')" id="normalBtn">
                                <div class="font-weight-bold">NORMAL</div>
                                <small>Penggunaan Standar</small>
                            </button>
                            <button type="button" class="btn btn-outline-primary text-left py-3" 
                                    onclick="setPowerMode('strong')" id="strongBtn">
                                <div class="font-weight-bold">STRONG</div>
                                <small>Daya Maksimal</small>
                            </button>
                        </div>
                        
                        <div class="mt-3 alert alert-light" id="powerInfo">
                            <strong>Mode Saat Ini:</strong> NORMAL
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // ===== API Configuration =====
        // Routes di web.php, jadi tidak punya /api prefix
        const API_BASE_URL = "/v1/vacuum";

        // ===== State Management =====
        let vacuumState = {
            state: 'standby',
            powerMode: 'normal',
            powerValue: 200,
            batteryPercent: 85,
            batteryVoltage: 12.5,
            estimatedTime: '3h 45m'
        };

        // ===== UTILITY FUNCTIONS =====

        /**
         * Show notification to user
         */
        function showNotification(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const alertHTML = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            `;
            
            $('#notification-container').html(alertHTML);
            setTimeout(() => {
                $('#notification-container').fadeOut(function() {
                    $(this).empty().show();
                });
            }, 3000);
        }

        // ===== COMMAND FUNCTIONS =====

        /**
         * Send vacuum command
         * Commands: start, stop, return_home
         */
        function sendVacuumCommand(command) {
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            $.ajax({
                url: `${API_BASE_URL}/command`,
                type: 'POST',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({
                    command: command
                }),
                success: function(response) {
                    console.log('Command sent:', response);
                    showNotification('success', `Perintah ${command} berhasil dikirim`);
                    // Update UI state
                    setTimeout(fetchVacuumStatus, 500);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    showNotification('error', 'Gagal mengirim perintah: ' + error);
                }
            });
        }

        /**
         * Wrapper functions untuk tombol
         */
        function startVacuum() {
            sendVacuumCommand('start');
        }

        function stopVacuum() {
            sendVacuumCommand('stop');
        }

        function returnToBase() {
            sendVacuumCommand('return_home');
        }

        // ===== POWER MODE FUNCTIONS =====

        /**
         * Set power mode (eco, normal, strong)
         */
        function setPowerMode(mode) {
            const powerValues = {
                'eco': 150,
                'normal': 200,
                'strong': 255
            };
            
            const value = powerValues[mode];
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            $.ajax({
                url: `${API_BASE_URL}/power-mode`,
                type: 'POST',
                dataType: 'json',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                },
                data: JSON.stringify({
                    mode: mode,
                    value: value
                }),
                success: function(response) {
                    console.log('Power mode updated:', response);
                    
                    // Update UI state
                    vacuumState.powerMode = mode;
                    updatePowerModeUI(mode);
                    
                    const descriptions = {
                        'eco': 'Hemat Energi - Daya rendah untuk pembersihan ringan',
                        'normal': 'Mode Standar - Keseimbangan daya dan efisiensi',
                        'strong': 'Daya Maksimal - Pembersihan intensif'
                    };
                    
                    updatePowerInfo(mode, descriptions[mode]);
                    showNotification('success', `Mode ${mode.toUpperCase()} diaktifkan`);
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    console.error('Status:', status);
                    console.error('Response:', xhr.responseText);
                    showNotification('error', 'Gagal mengubah mode daya: ' + error);
                }
            });
        }

        /**
         * Update power mode buttons UI
         */
        function updatePowerModeUI(mode) {
            document.getElementById('ecoBtn').classList.toggle('active', mode === 'eco');
            document.getElementById('normalBtn').classList.toggle('active', mode === 'normal');
            document.getElementById('strongBtn').classList.toggle('active', mode === 'strong');
        }

        /**
         * Update power mode info text
         */
        function updatePowerInfo(mode, description) {
            const modeText = {
                'eco': 'ECO',
                'normal': 'NORMAL',
                'strong': 'STRONG'
            };
            document.getElementById('powerInfo').innerHTML = `
                <strong>Mode Saat Ini:</strong> ${modeText[mode]}<br>
                <small>${description}</small>
            `;
        }

        // ===== STATUS FETCHING FUNCTIONS =====

        /**
         * Fetch vacuum status dari server
         * GET /api/v1/vacuum/status
         */
        function fetchVacuumStatus() {
            $.ajax({
                url: `${API_BASE_URL}/status`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        const data = response.data;
                        updateVacuumStatusUI(data.state, data.power_mode, data.power_value);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching status:', error);
                }
            });
        }

        /**
         * Fetch battery data terbaru
         * GET /api/v1/vacuum/battery/latest
         */
        function fetchBatteryData() {
            $.ajax({
                url: `${API_BASE_URL}/battery/latest`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        const data = response.data;
                        updateBatteryUI(data.battery_percent, data.estimated_time);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching battery:', error);
                }
            });
        }

        /**
         * Fetch battery history untuk chart
         * GET /api/v1/vacuum/battery/history?minutes=60
         */
        function fetchBatteryHistory(minutes = 60) {
            $.ajax({
                url: `${API_BASE_URL}/battery/history?minutes=${minutes}`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data) {
                        console.log('Battery history:', response.data);
                        // Bisa digunakan untuk membuat chart
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching history:', error);
                }
            });
        }

        /**
         * Fetch full status sekaligus
         * GET /api/v1/vacuum/full-status
         */
        function fetchFullStatus() {
            $.ajax({
                url: `${API_BASE_URL}/full-status`,
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        const vacuum = response.vacuum;
                        const battery = response.battery;
                        
                        // Update semua UI sekaligus
                        updateVacuumStatusUI(vacuum.state, vacuum.power_mode, vacuum.power_value);
                        updateBatteryUI(battery.percent, battery.estimated_time);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching full status:', error);
                }
            });
        }

        // ===== UI UPDATE FUNCTIONS =====

        /**
         * Update vacuum status di UI
         */
        function updateVacuumStatusUI(state, powerMode, powerValue) {
            // Update status badge
            const statusMap = {
                'standby': '<span class="badge badge-secondary">Standby</span>',
                'working': '<span class="badge badge-success">Berjalan</span>',
                'stopping': '<span class="badge badge-danger">Berhenti</span>',
                'returning': '<span class="badge badge-warning">Kembali ke Base</span>',
                'charging': '<span class="badge badge-info">Charging</span>'
            };
            
            document.getElementById('statusRobot').innerHTML = statusMap[state];
            
            // Update power mode UI
            updatePowerModeUI(powerMode);
            
            // Update status info
            const statusMessages = {
                'standby': 'Siap digunakan',
                'working': 'Vacuum sedang berjalan...',
                'stopping': 'Vacuum telah dihentikan',
                'returning': 'Vacuum kembali ke home base...',
                'charging': 'Vacuum sedang berada di home base'
            };
            
            document.getElementById('statusInfo').innerHTML = '<strong>Status:</strong> ' + statusMessages[state];
        }

        /**
         * Update battery di UI
         */
        function updateBatteryUI(batteryPercent, estimatedTime) {
            // Update percentage
            document.getElementById('batteryPercent').textContent = batteryPercent + '%';
            
            // Update progress bar
            const batteryBar = document.getElementById('batteryBar');
            batteryBar.style.width = batteryPercent + '%';
            
            // Update bar color based on percentage
            if (batteryPercent > 50) {
                batteryBar.classList.remove('bg-warning', 'bg-danger');
                batteryBar.classList.add('bg-success');
            } else if (batteryPercent > 20) {
                batteryBar.classList.remove('bg-success', 'bg-danger');
                batteryBar.classList.add('bg-warning');
            } else {
                batteryBar.classList.remove('bg-success', 'bg-warning');
                batteryBar.classList.add('bg-danger');
            }
            
            // Update estimated time
            if (estimatedTime) {
                document.getElementById('batteryEstimate').textContent = 'Estimasi: ' + estimatedTime;
            }
        }

        // ===== AUTO REFRESH / POLLING =====

        let statusPollingInterval;
        let batteryPollingInterval;

        /**
         * Start polling vacuum status
         */
        function startStatusPolling(interval = 2000) {
            // Initial fetch
            fetchVacuumStatus();
            
            // Set polling
            statusPollingInterval = setInterval(function() {
                fetchVacuumStatus();
            }, interval);
            
            console.log('Status polling started: every ' + interval + 'ms');
        }

        /**
         * Start polling battery data
         */
        function startBatteryPolling(interval = 5000) {
            // Initial fetch
            fetchBatteryData();
            
            // Set polling
            batteryPollingInterval = setInterval(function() {
                fetchBatteryData();
            }, interval);
            
            console.log('Battery polling started: every ' + interval + 'ms');
        }

        /**
         * Stop polling
         */
        function stopPolling() {
            if (statusPollingInterval) clearInterval(statusPollingInterval);
            if (batteryPollingInterval) clearInterval(batteryPollingInterval);
            console.log('Polling stopped');
        }

        // ===== INITIALIZATION =====

        /**
         * Initialize pada saat page load
         */
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== Vacuum Control Panel Loaded ===');
            
            // Fetch initial data
            fetchFullStatus();
            
            // Start polling
            startStatusPolling(2000);      // Poll status setiap 2 detik
            startBatteryPolling(5000);     // Poll battery setiap 5 detik
            
            console.log('Initialization complete');
        });

        // ===== CLEANUP =====

        /**
         * Stop polling saat user meninggalkan page
         */
        window.addEventListener('beforeunload', function() {
            stopPolling();
        });
    </script>

    <style>
        .btn-lg {
            font-size: 1.1rem;
            padding: 1rem !important;
        }

        .btn-group-vertical .btn {
            border-radius: 0;
            padding: 1rem;
        }

        .btn-group-vertical .btn:first-child {
            border-top-left-radius: 0.25rem;
            border-top-right-radius: 0.25rem;
        }

        .btn-group-vertical .btn:last-child {
            border-bottom-left-radius: 0.25rem;
            border-bottom-right-radius: 0.25rem;
        }

        .btn-group-vertical .btn.active {
            background-color: #007bff;
            color: white;
            border-color: #007bff;
        }

        .card-header.bg-primary {
            background-color: #4e73df !important;
        }

        .card-header.bg-info {
            background-color: #17a2b8 !important;
        }

        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }

        .border-left-info {
            border-left: 0.25rem solid #17a2b8 !important;
        }

        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }

        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }

        .border-left-danger {
            border-left: 0.25rem solid #e74c3c !important;
        }

        .progress {
            background-color: #e3e6f0;
        }

        .badge-lg {
            padding: 0.5rem 1rem;
            font-size: 1rem;
        }
    </style>
</x-master>