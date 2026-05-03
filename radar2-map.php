<?php
// radar-map.php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Weather Radar Map - Multiple Products</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
        #map { height: 100vh; width: 100%; }
        
        .controls {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            max-width: 280px;
        }
        
        .controls h3 { margin: 0 0 10px 0; font-size: 16px; }
        
        button {
            display: block;
            width: 100%;
            margin: 5px 0;
            padding: 10px;
            background: #007cba;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        
        button.active {
            background: #005a87;
            font-weight: bold;
        }
        
        button:hover { background: #006699; }
        
        .legend {
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            font-size: 14px;
            line-height: 1.4;
        }
    </style>
</head>
<body>

<div id="map"></div>

<!-- Control Panel -->
<div class="controls">
    <h3>Radar Products</h3>
    <button onclick="switchRadar(this)" data-product="n0q" class="active">Base Reflectivity (N0Q - 8-bit)</button>
    <button onclick="switchRadar(this)" data-product="n0r">Base Reflectivity (N0R - 4-bit)</button>
    <button onclick="switchRadar(this)" data-product="n0v">Velocity (N0V)</button>
    <button onclick="switchRadar(this)" data-product="daa">1-Hour Rainfall</button>
    <button onclick="switchRadar(this)" data-product="dta">Storm Total Rainfall</button>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    let map = L.map('map').setView([39.5, -98.35], 5);
    let currentRadarLayer = null;

    // Base map
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Radar product configurations
    const radarProducts = {
        n0q: {
            url: 'https://mesonet.agron.iastate.edu/cgi-bin/wms/nexrad/n0q.cgi',
            layer: 'nexrad-n0q-900913',
            name: 'Base Reflectivity (N0Q)',
            opacity: 0.75
        },
        n0r: {
            url: 'https://mesonet.agron.iastate.edu/cgi-bin/wms/nexrad/n0r.cgi',
            layer: 'nexrad-n0r-900913',
            name: 'Base Reflectivity (N0R)',
            opacity: 0.75
        },
        n0v: {
            url: 'https://mesonet.agron.iastate.edu/cgi-bin/wms/nexrad/n0v.cgi',
            layer: 'nexrad-n0v-900913',
            name: 'Velocity (N0V)',
            opacity: 0.8
        },
        daa: {
            url: 'https://mesonet.agron.iastate.edu/cgi-bin/wms/nexrad/daa.cgi',
            layer: 'nexrad-daa-900913',
            name: '1-Hour Rainfall',
            opacity: 0.7
        },
        dta: {
            url: 'https://mesonet.agron.iastate.edu/cgi-bin/wms/nexrad/dta.cgi',
            layer: 'nexrad-dta-900913',
            name: 'Storm Total Rainfall',
            opacity: 0.7
        }
    };

    function createRadarLayer(productKey) {
        const prod = radarProducts[productKey];
        
        return L.tileLayer.wms(prod.url, {
            layers: prod.layer,
            format: 'image/png',
            transparent: true,
            opacity: prod.opacity,
            attribution: 'Radar data &copy; <a href="https://mesonet.agron.iastate.edu/">IEM</a>'
        });
    }

    function switchRadar(button) {
        const productKey = button.getAttribute('data-product');
        
        // Remove old layer
        if (currentRadarLayer) {
            map.removeLayer(currentRadarLayer);
        }
        
        // Create and add new layer
        currentRadarLayer = createRadarLayer(productKey);
        currentRadarLayer.addTo(map);
        
        // Update active button
        document.querySelectorAll('.controls button').forEach(btn => {
            btn.classList.remove('active');
        });
        button.classList.add('active');
    }

    // Initialize with N0Q (best quality)
    currentRadarLayer = createRadarLayer('n0q');
    currentRadarLayer.addTo(map);

    // Legend (you can expand this per product if desired)
    const legend = L.control({position: 'bottomright'});
    legend.onAdd = function () {
        const div = L.DomUtil.create('div', 'legend');
        div.innerHTML = `
            <strong>Legend</strong><br>
            <small>Colors vary by product</small><br><br>
            <span style="color:#00ff00;">■</span> Light<br>
            <span style="color:#ffff00;">■</span> Moderate<br>
            <span style="color:#ff9900;">■</span> Heavy<br>
            <span style="color:#ff0000;">■</span> Extreme
        `;
        return div;
    };
    legend.addTo(map);

    // Auto-refresh every 4 minutes
    setInterval(() => {
        if (currentRadarLayer) {
            const currentUrl = currentRadarLayer._url;
            currentRadarLayer.setUrl(currentUrl + (currentUrl.includes('?') ? '&' : '?') + 't=' + Date.now());
        }
    }, 240000);
</script>

</body>
</html>

