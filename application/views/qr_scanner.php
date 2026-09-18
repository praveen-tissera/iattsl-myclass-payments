<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?php echo base_url() . '/css/bootstrap.min.css' ?>">
    <title><?php echo isset($title) ? $title : 'QR Code Scanner'; ?></title>
    
    <!-- Google Fonts for premium typography -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Standardize layout matching main system */
        .main-container {
            flex: 1;
            padding: 40px 15px;
        }

        /* Glassmorphism panel styling */
        .glass-panel {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            margin-bottom: 30px;
        }

        .gradient-text {
            background: linear-gradient(135deg, #38bdf8, #818cf8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-weight: 800;
        }

        /* Camera Viewport Container styling */
        .camera-container {
            position: relative;
            width: 100%;
            max-width: 500px;
            margin: 0 auto 20px auto;
            border-radius: 20px;
            overflow: hidden;
            background: #020617;
            border: 3px solid rgba(56, 189, 248, 0.3);
            box-shadow: 0 0 25px rgba(56, 189, 248, 0.15);
            aspect-ratio: 4/3;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* The html5-qrcode element placeholder */
        #reader {
            width: 100% !important;
            height: 100% !important;
            border: none !important;
        }

        /* Overrides html5-qrcode styling to fit our custom theme */
        #reader video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
            border-radius: 17px;
        }

        /* Custom Scanner Laser & Target Overlay */
        .scanner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 5;
        }

        .scanner-target {
            width: 240px;
            height: 240px;
            border: 2px dashed rgba(56, 189, 248, 0.8);
            position: relative;
            border-radius: 16px;
            box-shadow: 0 0 0 4000px rgba(15, 23, 42, 0.6);
            transition: all 0.3s ease;
        }

        .scanner-target::before {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            width: 25px;
            height: 25px;
            border-top: 5px solid #38bdf8;
            border-left: 5px solid #38bdf8;
            border-top-left-radius: 8px;
        }

        .scanner-target::after {
            content: '';
            position: absolute;
            top: -2px;
            right: -2px;
            width: 25px;
            height: 25px;
            border-top: 5px solid #38bdf8;
            border-right: 5px solid #38bdf8;
            border-top-right-radius: 8px;
        }

        .scanner-bottom-corners::before {
            content: '';
            position: absolute;
            bottom: -2px;
            left: -2px;
            width: 25px;
            height: 25px;
            border-bottom: 5px solid #38bdf8;
            border-left: 5px solid #38bdf8;
            border-bottom-left-radius: 8px;
        }

        .scanner-bottom-corners::after {
            content: '';
            position: absolute;
            bottom: -2px;
            right: -2px;
            width: 25px;
            height: 25px;
            border-bottom: 5px solid #38bdf8;
            border-right: 5px solid #38bdf8;
            border-bottom-right-radius: 8px;
        }

        /* Moving laser line */
        .laser-line {
            position: absolute;
            left: 5%;
            width: 90%;
            height: 3px;
            background: linear-gradient(90deg, transparent, #38bdf8, #818cf8, #38bdf8, transparent);
            box-shadow: 0 0 10px #38bdf8;
            animation: laserMove 2.5s infinite linear;
        }

        @keyframes laserMove {
            0% { top: 10px; }
            50% { top: calc(100% - 13px); }
            100% { top: 10px; }
        }

        /* Success/Active states */
        .camera-container.success {
            border-color: #10b981;
            box-shadow: 0 0 25px rgba(16, 185, 129, 0.3);
        }

        .camera-container.success .scanner-target {
            border-color: #10b981;
            box-shadow: 0 0 0 4000px rgba(15, 23, 42, 0.85);
        }

        .camera-container.success .scanner-target::before,
        .camera-container.success .scanner-target::after,
        .camera-container.success .scanner-bottom-corners::before,
        .camera-container.success .scanner-bottom-corners::after {
            border-color: #10b981;
        }

        .camera-container.success .laser-line {
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
            animation-play-state: paused;
        }

        /* Control elements */
        .control-select {
            background-color: #1e293b;
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #f8fafc;
            border-radius: 12px;
            padding: 10px 15px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
        }

        .control-select:focus {
            background-color: #1e293b;
            border-color: #38bdf8;
            color: #f8fafc;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.25);
        }

        .custom-btn {
            border-radius: 12px;
            padding: 10px 24px;
            font-weight: 600;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.95rem;
        }

        .btn-start {
            background: linear-gradient(135deg, #0ea5e9, #2563eb);
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .btn-start:hover:not(:disabled) {
            background: linear-gradient(135deg, #38bdf8, #3b82f6);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35);
            color: white;
        }

        .btn-stop {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .btn-stop:hover:not(:disabled) {
            background: #ef4444;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(239, 68, 68, 0.25);
        }

        /* Scan Log Dashboard Table */
        .scanned-list-card {
            background: rgba(15, 23, 42, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px;
            max-height: 380px;
            overflow-y: auto;
        }

        .log-table {
            color: #e2e8f0;
            font-size: 0.85rem;
        }

        .log-table th {
            color: #94a3b8;
            font-weight: 600;
            border-top: none;
            border-bottom: 2px solid rgba(255, 255, 255, 0.05);
        }

        .log-table td {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            vertical-align: middle;
        }

        .badge-scan {
            background-color: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 8px;
            border: 1px solid rgba(56, 189, 248, 0.2);
        }

        /* Status Badge colors */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 20px;
        }
        
        .status-idle {
            background-color: rgba(148, 163, 184, 0.1);
            color: #94a3b8;
            border: 1px solid rgba(148, 163, 184, 0.2);
        }
        
        .status-active {
            background-color: rgba(56, 189, 248, 0.1);
            color: #38bdf8;
            border: 1px solid rgba(56, 189, 248, 0.2);
            animation: pulse 2s infinite;
        }

        .status-processing {
            background-color: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .status-success-state {
            background-color: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        @keyframes pulse {
            0% { opacity: 0.6; }
            50% { opacity: 1; }
            100% { opacity: 0.6; }
        }

        /* Layout modifications to footer */
        .footer-copyright {
            padding: 20px;
            text-align: center;
            color: #64748b;
            font-size: 0.85rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            background: #020617;
        }

        /* Responsive spacing improvements */
        @media(max-width: 576px) {
            .glass-panel {
                padding: 15px;
            }
            .camera-container {
                aspect-ratio: 1;
            }
            .scanner-target {
                width: 180px;
                height: 180px;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation menus matching roles -->
    <?php 
    if($this->session->userdata('user_role') == 'administrator'){
        $this->load->view('includesui/menu_admin');
    }elseif($this->session->userdata('user_role') == 'teacher'){
        $this->load->view('includesui/menu_teacher');
    }elseif($this->session->userdata('user_role') == 'cordinator'){
        $this->load->view('includesui/menu_cordinator');
    }
    ?>

    <div class="container main-container">
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="glass-panel text-center">
                    <h2 class="display-5 gradient-text mb-2">IATTSL Student Scanner</h2>
                    <p class="text-muted mb-4">Scan student QR codes to view their payment information.</p>
                    
                    <!-- Scanner Status indicator -->
                    <div id="scannerStatus" class="status-badge status-idle">
                        <span class="mr-2">●</span> Scanner Offline
                    </div>

                    <!-- Scanner Camera Feed Wrapper -->
                    <div class="camera-container" id="cameraContainer">
                        <div id="reader"></div>
                        <!-- Custom HUD Overlay -->
                        <div class="scanner-overlay" id="scannerOverlay" style="display: none;">
                            <div class="scanner-target">
                                <div class="laser-line"></div>
                                <div class="scanner-bottom-corners"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Setup and Controls Area -->
                    <div class="form-group text-left mt-3">
                        <label for="cameraSelect" class="text-muted font-weight-bold" style="font-size: 0.8rem; text-transform: uppercase;">Select Active Camera</label>
                        <select class="form-control control-select mb-3" id="cameraSelect">
                            <option value="">Detecting cameras...</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <button class="btn custom-btn btn-start mr-2" id="startBtn">Start Camera</button>
                        <button class="btn custom-btn btn-stop" id="stopBtn" disabled>Stop Camera</button>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-4">
                <!-- Session/Server Alert Output -->
                <div id="alertPlaceholder"></div>

                <div class="glass-panel" style="height: calc(100% - 30px);">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="m-0 font-weight-bold">Student Payment Dashboard</h4>
                        <!-- Audio checkbox option -->
                        <div class="form-check form-check-inline text-muted" style="font-size: 0.85rem;">
                            <input class="form-check-input" type="checkbox" id="beepEnabled" checked>
                            <label class="form-check-label" for="beepEnabled">Audio Alert (Beep)</label>
                        </div>
                    </div>
                    <p class="text-muted mb-4" style="font-size: 0.85rem;"></p>

                    <div class="scanned-list-card">
                        <table class="table log-table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Student ID</th>
                                    <th>Server Status</th>
                                </tr>
                            </thead>
                            <tbody id="scanLogBody">
                                <tr id="noScansRow">
                                    <td colspan="3" class="text-center text-muted py-4">No active scans detected in this session.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="scanned-list-card">
                        <div class="card-header px-0">
                            <h5 class="m-0">Student Name: <span id="studentName">-</span></h5>
                        </div>
                        <table class="table log-table">
                            <thead>
                                <tr>
                                    <th>Subjects</th>
                                    <th>Last Payment</th>
                                    <th>Current Month Payment Status</th>
                                </tr>
                            </thead>
                            <tbody id="scanSubjectLogBody">
                                <tr id="noScansRow">
                                    <td colspan="3" class="text-center text-muted py-4">No active scans detected in this session.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Auto-resume control option -->
                    <div class="mt-3 text-muted" style="font-size: 0.85rem;">
                        <span class="font-weight-bold">Cooldown Mode:</span> 2.5 seconds pause between scans of the same code to prevent accidental double-submits.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-copyright">
        Copyright © 2025 - IATTSL. All Rights Reserved
    </div>

    <!-- Core Javascript Scripts -->
    <script src="<?php echo base_url() . '/script/jquery.js' ?>"></script>
    <script src="<?php echo base_url() . '/script/bootstrap.min.js' ?>"></script>
    
    <!-- HTML5-QRCODE Library CDN -->
    <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"  crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->
<script src="<?php echo base_url() . '/script/html5-qrcode.min.js' ?>"></script>

    <script>
        $(document).ready(function() {
            const cameraSelect = $('#cameraSelect');
            const startBtn = $('#startBtn');
            const stopBtn = $('#stopBtn');
            const cameraContainer = $('#cameraContainer');
            const scannerOverlay = $('#scannerOverlay');
            const scannerStatus = $('#scannerStatus');
            const scanLogBody = $('#scanLogBody');
            const scanSubjectLogBody = $('#scanSubjectLogBody');
            const noScansRow = $('#noScansRow');
            const alertPlaceholder = $('#alertPlaceholder');
            const beepEnabled = $('#beepEnabled');

            let html5QrCode = null;
            let currentCameraId = null;
            let isScannerRunning = false;
            
            // Audio Context for the synthesized beep alert
            let audioCtx = null;

            // Simple synthesizer beep utilizing HTML5 Web Audio API
            function playSuccessBeep() {
                if (!beepEnabled.is(':checked')) return;
                
                try {
                    if (!audioCtx) {
                        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    }
                    
                    if (audioCtx.state === 'suspended') {
                        audioCtx.resume();
                    }

                    const osc = audioCtx.createOscillator();
                    const gain = audioCtx.createGain();
                    
                    osc.connect(gain);
                    gain.connect(audioCtx.destination);
                    
                    osc.type = 'sine';
                    osc.frequency.value = 880; // A5 note, very clear high beep
                    
                    gain.gain.setValueAtTime(0, audioCtx.currentTime);
                    gain.gain.linearRampToValueAtTime(0.3, audioCtx.currentTime + 0.05); // quick fade-in
                    gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.25); // fade-out
                    
                    osc.start(audioCtx.currentTime);
                    osc.stop(audioCtx.currentTime + 0.26);
                } catch (e) {
                    console.error("Web Audio Beep failed to play: ", e);
                }
            }

            // unpaid warning beep
            function playWarningBeep() {
    if (!$('#beepEnabled').is(':checked')) return;

    try {
        if (!audioCtx) {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        }

        if (audioCtx.state === 'suspended') {
            audioCtx.resume();
        }

        const duration = 3; // seconds
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();

        osc.connect(gain);
        gain.connect(audioCtx.destination);

        osc.type = 'square';

        // Alternate frequencies to create alarm effect
        let time = audioCtx.currentTime;

        for (let i = 0; i < 6; i++) {
            osc.frequency.setValueAtTime(800, time);
            osc.frequency.setValueAtTime(450, time + 0.25);
            time += 0.5;
        }

        gain.gain.setValueAtTime(0.001, audioCtx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.4, audioCtx.currentTime + 0.05);
        gain.gain.setValueAtTime(0.4, audioCtx.currentTime + duration - 0.1);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + duration);

        osc.start(audioCtx.currentTime);
        osc.stop(audioCtx.currentTime + duration);

    } catch (e) {
        console.error("Warning alarm failed:", e);
    }
}

            // Detect available cameras on load
            function detectCameras(showWarning = true) {
                // Check for secure context first (browsers block media access on non-secure context)
                if (window.isSecureContext === false) {
                    showAlert('<strong>Insecure Context Detected:</strong> Camera access is blocked by browsers on non-HTTPS origins. Please access this page using <code>http://localhost/...</code>, <code>http://127.0.0.1/...</code>, or via a secure <code>https://</code> domain.', 'danger');
                    cameraSelect.empty().append('<option value="">Secure context required (HTTPS or localhost)</option>');
                    startBtn.prop('disabled', true);
                    return;
                }

                Html5Qrcode.getCameras().then(devices => {
                    cameraSelect.empty();
                    if (devices && devices.length > 0) {
                        devices.forEach((device, index) => {
                            const label = device.label || `Camera ${index + 1}`;
                            cameraSelect.append(`<option value="${device.id}">${label}</option>`);
                        });
                        // Match current selection or pick first
                        if (currentCameraId) {
                            cameraSelect.val(currentCameraId);
                        } else {
                            currentCameraId = devices[0].id;
                        }
                        startBtn.prop('disabled', false);
                    } else {
                        // Fallback option when permission is not yet granted
                        cameraSelect.append('<option value="default">Default Camera (Facing Environment/Rear)</option>');
                        currentCameraId = "default";
                        startBtn.prop('disabled', false);
                        if (showWarning) {
                            showAlert('Click "Start Camera" to trigger the browser permission prompt and start scanning.', 'info');
                        }
                    }
                }).catch(err => {
                    console.warn("Error listing cameras: ", err);
                    if (cameraSelect.children().length === 0 || cameraSelect.val() === "") {
                        cameraSelect.empty().append('<option value="default">Default Camera (Facing Environment/Rear)</option>');
                        currentCameraId = "default";
                    }
                    startBtn.prop('disabled', false);
                    if (showWarning) {
                        showAlert('Click "Start Camera" to grant camera permission.', 'info');
                    }
                });
            }

            // Display floating Bootstrap alert message
            function showAlert(message, type = 'info') {
                const alertHtml = `
                    <div class="alert alert-${type} alert-dismissible fade show" role="alert" style="border-radius: 12px;">
                        ${message}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                `;
                alertPlaceholder.html(alertHtml);
            }

            // Track last scanned code and timestamp to prevent rapid double-scanning
            let lastScannedText = "";
            let lastScanTime = 0;
            const SCAN_COOLDOWN_MS = 2500; // 2 seconds cooldown for identical scans

            // QR Code Scan success callback handler
            function onScanSuccess(decodedText, decodedResult) {
                const now = Date.now();
                
                // Cooldown check for identical codes
                if (decodedText === lastScannedText && (now - lastScanTime) < SCAN_COOLDOWN_MS) {
                    return; // Ignore duplicate scans in the cooldown window
                }
                
                lastScannedText = decodedText;
                lastScanTime = now;

                // Visual Success flash
                playSuccessBeep();
                cameraContainer.addClass('success');
                scannerOverlay.fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
                updateScannerStatus('processing');

                // Log the scan locally instantly
                noScansRow.hide();
                const logTime = new Date().toLocaleTimeString();
                const randomId = 'row_' + Math.random().toString(36).substr(2, 9);
                const stdRegNo = decodedText.split('/')[0] + '/' + decodedText.split('/')[1].split('-').slice(0, 2).join('-');
                const newRow = `
                    <tr id="${randomId}">
                        <td><span class="text-muted">${logTime}</span></td>
                        <td><span class="badge-scan">${escapeHtml(stdRegNo)}</span></td>
                        <td class="server-status"><span class="text-warning">● Sending...</span></td>
                    </tr>
                `;
                 const newSubjectRow = `
                    <tr id="subject_${randomId}">
 
                        <td class="subject-status" colspan="3"><span class="text-warning">● Sending...</span></td>
                    </tr>
                `;
                hasUnpaidSubject = false; // Reset unpaid subject flag for this scan
                scanLogBody.empty().prepend(newRow);
                scanSubjectLogBody.empty().prepend(newSubjectRow);

                // Send AJAX POST request to CodeIgniter Controller process_scan method
                $.ajax({
                    url: '<?php echo site_url("QrScanner/process_scan"); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        qr_code: decodedText
                    },
                    success: function(response) {
                        const targetRow = $(`#${randomId}`);
                        const targetSubjectRow = $(`#subject_${randomId}`);
                        if (response.status === 'success') {
                            // add student name to the top of the subject table
                            $('#studentName').text(response.studentname || 'N/A');
                            console.log("Scan processed successfully: ", response);
                        let rows = '';

                            response.activesubjects.forEach(item => {
                                if (item.current_month_payment_status === 'Unpaid') {
                                    hasUnpaidSubject = true;
                                }
                                rows += `
                                    <tr>
                                        <td>${item.course_name}</td>
                                        <td>${item.last_payment?.invoice_label || 'N/A'}</td>
                                        <td>${item.current_month_payment_status}</td>
                                    </tr>
                                `;
                            });

                            $(`#subject_${randomId}`).replaceWith(rows);

                                if (hasUnpaidSubject) {
                                 playWarningBeep();
                                }else{
                                 playSuccessBeep();
                                }



                            // Redirect to the ID validator page
                            // console.log("Redirecting to: ", response.redirect_url);
                            // window.location.href = response.redirect_url;
                            targetRow.find('.server-status').html('<span class="text-success font-weight-bold">✓ Logged (Controller)</span>');
                            
                            showAlert(`Processed Code: <strong>${escapeHtml(response.scanned_code)}</strong>. Printed in controller logs!`, 'success');
                        } else {
                            targetRow.find('.server-status').html(`<span class="text-danger">✗ Error: ${escapeHtml(response.message)}</span>`);
                            showAlert(`Server rejected code: ${escapeHtml(response.message)}`, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Scan submission failed: ", error);
                        $(`#${randomId}`).find('.server-status').html('<span class="text-danger">✗ HTTP Error</span>');
                        showAlert('Connection to server failed. Could not log scanned QR code in the controller.', 'danger');
                    },
                    complete: function() {
                        // Reset success flash visualization state
                        setTimeout(() => {
                            cameraContainer.removeClass('success');
                            if (isScannerRunning) {
                                updateScannerStatus('active');
                            }
                        }, 1000);
                    }
                });
            }

            function updateScannerStatus(state) {
                scannerStatus.removeClass('status-idle status-active status-processing status-success-state');
                
                switch (state) {
                    case 'idle':
                        scannerStatus.addClass('status-idle').html('<span class="mr-2">●</span> Scanner Offline');
                        break;
                    case 'active':
                        scannerStatus.addClass('status-active').html('<span class="mr-2">●</span> Scanner Running');
                        break;
                    case 'processing':
                        scannerStatus.addClass('status-processing').html('<span class="mr-2">●</span> Submitting data...');
                        break;
                }
            }

            // Start QR Scanner method
            function startScanner() {
                const selectedCamera = cameraSelect.val();
                
                if (!html5QrCode) {
                    html5QrCode = new Html5Qrcode("reader");
                }

                updateScannerStatus('active');
                scannerOverlay.fadeIn(300);

                // Configure config target: specific device ID or facingMode back camera
                let cameraConfig = { facingMode: "environment" };
                if (selectedCamera && selectedCamera !== "default") {
                    cameraConfig = selectedCamera;
                }

                html5QrCode.start(
                    cameraConfig, 
                    {
                        fps: 12,                  // Frame rate for scanner
                        qrbox: function(width, height) {
                            // Scale qrbox size dynamically based on dimensions
                            const side = Math.min(width, height) * 0.65;
                            return { width: side, height: side };
                        },
                        aspectRatio: 1.333333     // Standard 4:3 view
                    },
                    onScanSuccess,
                    (errorMessage) => {
                        // Scan errors are verbose/frequent as it scans every frame, we ignore them
                    }
                ).then(() => {
                    isScannerRunning = true;
                    startBtn.prop('disabled', true);
                    stopBtn.prop('disabled', false);
                    cameraSelect.prop('disabled', true);

                    // Re-detect cameras now that permission has been granted, to fetch real device names
                    detectCameras(false);
                }).catch(err => {
                    console.error("Unable to start scanner: ", err);
                    updateScannerStatus('idle');
                    scannerOverlay.fadeOut(200);
                    showAlert(`Error opening camera: ${err}. Please verify camera permission is granted and no other application is using it.`, 'danger');
                });
            }

            // Stop QR Scanner method
            function stopScanner() {
                if (html5QrCode && isScannerRunning) {
                    html5QrCode.stop().then(() => {
                        isScannerRunning = false;
                        startBtn.prop('disabled', false);
                        stopBtn.prop('disabled', true);
                        cameraSelect.prop('disabled', false);
                        scannerOverlay.fadeOut(300);
                        updateScannerStatus('idle');
                    }).catch(err => {
                        console.error("Error stopping scanner: ", err);
                        showAlert('Error stopping the camera feed cleanly.', 'warning');
                    });
                }
            }

            // Helper to escape HTML tags to mitigate cross-site scripting
            function escapeHtml(str) {
                return $('<div>').text(str).html();
            }

            // Event bindings
            startBtn.on('click', startScanner);
            stopBtn.on('click', stopScanner);
            cameraSelect.on('change', function() {
                currentCameraId = $(this).val();
            });

            // Initial Setup run
            detectCameras(true);
        });




    
    </script>
</body>

</html>
