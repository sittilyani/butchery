// scale_integration.php
<?php
function readSerialScale($port = 'COM3', $baudRate = 9600) {
    // For Windows
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $fp = fopen($port, 'rb');
        stream_set_blocking($fp, false);

        if ($fp) {
            $data = fread($fp, 100);
            fclose($fp);

            // Parse scale output (varies by manufacturer)
            // Typical format: "ST,GS,+1.234kg"
            if (preg_match('/(\d+\.?\d*)/', $data, $matches)) {
                return floatval($matches[1]);
            }
        }
    }
    return 0;
}

// AJAX endpoint for weight reading
if (isset($_GET['get_weight'])) {
    header('Content-Type: application/json');
    echo json_encode(['weight' => readSerialScale()]);
    exit;
}
?>

<script>
 // Using Web Bluetooth API
    async function connectBluetoothScale() {
        try {
            const device = await navigator.bluetooth.requestDevice({
                filters: [{ services: ['weight-scale'] }]
            });

            const server = await device.gatt.connect();
            const service = await server.getPrimaryService('weight-scale');
            const characteristic = await service.getCharacteristic('weight-measurement');

            await characteristic.startNotifications();
            characteristic.addEventListener('characteristicvaluechanged', event => {
                const weight = parseWeightValue(event.target.value);
                $('#weight-display').text(weight.toFixed(2) + ' kg');
            });
        } catch (error) {
            console.error('Bluetooth scale connection failed:', error);
        }
    }
   // WebSocket connection to scale server
   const scaleSocket = new WebSocket('ws://localhost:8080/scale');

        scaleSocket.onopen = function() {
            console.log('Connected to scale');
            $('#scale-status').addClass('connected');
        };

        scaleSocket.onmessage = function(event) {
            const data = JSON.parse(event.data);
            const weight = parseFloat(data.weight);

            $('#weight-display').text(weight.toFixed(2) + ' kg');

            // Auto-fill weight for selected product
            if (selectedProductId) {
                $('#weight-input-' + selectedProductId).val(weight.toFixed(2));
                updateItemWeight(selectedProductId, weight);
            }
        };

        scaleSocket.onclose = function() {
            $('#scale-status').removeClass('connected');
        };

    // Using WebUSB API (Chrome/Edge only)
    async function connectToScale() {
        try {
            const device = await navigator.usb.requestDevice({
                filters: [{ vendorId: 0x1234 }] // Scale vendor ID
            });

            await device.open();
            await device.selectConfiguration(1);
            await device.claimInterface(0);

            device.transferIn(1, 64).then(result => {
                const weight = parseWeight(result.data);
                $('#weight-display').text(weight + ' kg');
            });
        } catch (error) {
            console.error('Scale connection failed:', error);
        }
    }

    // Using Web Bluetooth API
    async function connectBluetoothScale() {
        try {
            const device = await navigator.bluetooth.requestDevice({
                filters: [{ services: ['weight-scale'] }]
            });

            const server = await device.gatt.connect();
            const service = await server.getPrimaryService('weight-scale');
            const characteristic = await service.getCharacteristic('weight-measurement');

            await characteristic.startNotifications();
            characteristic.addEventListener('characteristicvaluechanged', event => {
                const weight = parseWeightValue(event.target.value);
                $('#weight-display').text(weight.toFixed(2) + ' kg');
            });
        } catch (error) {
            console.error('Bluetooth scale connection failed:', error);
        }
    }

    // Poll for weight every second
function startWeightPolling() {
    setInterval(() => {
        $.ajax({
            url: 'get_scale_weight.php',
            method: 'GET',
            success: function(response) {
                if (response.weight > 0) {
                    $('#weight-display').text(response.weight.toFixed(2) + ' kg');
                    $('#scale-status').addClass('connected');
                } else {
                    $('#scale-status').removeClass('connected');
                }
            }
        });
    }, 1000);
}



</script>