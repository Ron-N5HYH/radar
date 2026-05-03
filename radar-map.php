<?php
// radar-map.php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Weather Radar Map</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        body { margin: 0; padding: 0; }
        #map { height: 100vh; width: 100%; }
        .legend {
            background: white;
            padding: 10px;
            border-radius: 5px;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
            font-size: 14px;
        }
    </style>
</head>
<body>

<div id="map"></div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Initialize map centered on the US
    const map = L.map('map').setView([32.96, -97.09], 8);
// 32.96147354360878, -97.09085037250216 My House 
    // Base map (OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Radar overlay (NEXRAD Base Reflectivity from Iowa Mesonet)
    const radarLayer = L.tileLayer.wms('https://mesonet.agron.iastate.edu/cgi-bin/wms/nexrad/n0r.cgi', {
        layers: 'nexrad-n0r-900913',   // Latest composite
        format: 'image/png',
        transparent: true,
        opacity: 0.7,
        attribution: 'Radar data &copy; <a href="https://mesonet.agron.iastate.edu/">IEM</a>'
    }).addTo(map);

    // Optional: Add layer control
    const baseMaps = {
        "OpenStreetMap": L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        })
    };

    const overlayMaps = {
        "Weather Radar": radarLayer
    };

    L.control.layers(baseMaps, overlayMaps).addTo(map);

    // Legend
    const legend = L.control({position: 'bottomright'});
    legend.onAdd = function () {
        const div = L.DomUtil.create('div', 'legend');
        div.innerHTML = `
            <strong>Radar Intensity</strong><br>
            <span style="color:#00ff00;">■</span> Light Rain<br>
            <span style="color:#ffff00;">■</span> Moderate<br>
            <span style="color:#ff9900;">■</span> Heavy<br>
            <span style="color:#ff0000;">■</span> Very Heavy / Hail
        `;
        return div;
    };
    legend.addTo(map);

    // Optional: Auto-refresh radar every 5 minutes
    setInterval(() => {
        radarLayer.setUrl(radarLayer._url + '?' + new Date().getTime()); // Force reload
    }, 300000);
</script>

</body>
</html>


