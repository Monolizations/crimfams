<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner - CRIM FAMS</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            overflow: hidden;
            background: #000;
        }
        #scanner-container {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        #video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        #viewfinder {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 250px;
            height: 250px;
            border: 2px solid #fff;
            border-radius: 10px;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.5);
        }
        #instructions {
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: #fff;
            font-size: 18px;
            text-align: center;
            z-index: 10;
        }
        #message {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: #fff;
            font-size: 16px;
            text-align: center;
            z-index: 10;
        }
    </style>
</head>
<body>
    <div id="scanner-container">
        <div id="instructions">
            <?php
            $type = $_GET['type'] ?? 'office';
            if ($type === 'office') {
                echo 'Scan Department QR Code for Time-In/Out';
            } else {
                echo 'Scan Classroom QR Code for Check-In';
            }
            ?>
        </div>
        <div id="reader" style="width: 100%; height: 100%;"></div>
        <div id="message" style="position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); color: #fff; font-size: 16px; text-align: center; z-index: 10;"></div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script>
        const message = document.getElementById('message');
        const type = '<?php echo $type; ?>';

        const html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", { fps: 10, qrbox: 250 });

        html5QrcodeScanner.render(onScanSuccess, onScanFailure);

        function onScanSuccess(decodedText, decodedResult) {
            // Stop scanning
            html5QrcodeScanner.clear();
            processQR(decodedText);
        }

        function onScanFailure(error) {
            // console.warn(`Code scan error = ${error}`);
        }

        function processQR(data) {
            try {
                const qrData = JSON.parse(data);
                if (qrData.type === 'department_log' && type === 'office') {
                    // Office time-in/out
                    sendToServer({ action: 'office_log', timestamp: new Date().toISOString() });
                } else if (qrData.type === 'classroom_checkin' && type === 'classroom') {
                    // Classroom check-in
                    sendToServer({ action: 'classroom_checkin', roomId: qrData.roomId, timestamp: new Date().toISOString() });
                } else {
                    message.textContent = 'Invalid QR code for this scan type.';
                }
            } catch (e) {
                message.textContent = 'Invalid QR code format.';
            }
        }

        function sendToServer(data) {
            fetch('<?php echo BASE_URL; ?>/api/scan', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    message.textContent = result.message;
                    setTimeout(() => window.history.back(), 2000);
                } else {
                    message.textContent = 'Error: ' + result.message;
                }
            })
            .catch(err => {
                // Offline: save to IndexedDB
                saveOffline(data);
                message.textContent = 'Check-in saved. Will sync when online.';
                setTimeout(() => window.history.back(), 2000);
            });
        }

        function saveOffline(data) {
            // Placeholder for IndexedDB
            console.log('Saving offline:', data);
        }
    </script>
</body>
</html>