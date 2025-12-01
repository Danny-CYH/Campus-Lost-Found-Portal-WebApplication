document.addEventListener('DOMContentLoaded', function () {
    initDashboardMap();
    initReportMap();
});

// --- 1. DASHBOARD MAP (View Items) ---
function initDashboardMap() {
    const mapElement = document.getElementById('map');
    if (!mapElement) return;

    // Initialize Map (Centered on UUM)
    // Coords: 6.460195, 100.505501
    const map = L.map('map').setView([6.460195, 100.505501], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Load Markers from PHP Data
    if (typeof mapItemsData !== 'undefined' && Array.isArray(mapItemsData)) {
        mapItemsData.forEach(item => {
            if (item.latitude && item.longitude) {

                const isFound = item.status === 'found';
                // Simple color logic (you can use custom icons for better visuals)
                // Leaflet default icons are blue, but we can customize later.

                const marker = L.marker([item.latitude, item.longitude]).addTo(map);

                const popupContent = `
                    <div class="text-center p-2">
                        <h3 class="font-bold text-gray-900">${item.title}</h3>
                        <span class="text-xs px-2 py-1 rounded-full ${isFound ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">
                            ${item.status.toUpperCase()}
                        </span>
                        <p class="text-xs text-gray-500 mt-1">${item.location_name}</p>
                        
                        <div class="mt-2 space-y-1">
                            <a href="view-item.php?id=${item.id}" class="block text-xs text-blue-600 hover:underline">View Details</a>
                            
                            <button onclick="filterByLocation('${item.location_name}')" 
                                class="block w-full mt-1 bg-blue-500 hover:bg-blue-600 text-white text-xs px-2 py-1 rounded transition-colors">
                                Show Items Here
                            </button>
                        </div>
                    </div>
                `;

                marker.bindPopup(popupContent);
            }
        });
    }
}

// --- 2. REPORT MAP (Select Location) ---
function initReportMap() {
    const reportMapElement = document.getElementById('location-map'); // ID in your report modal
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    if (!reportMapElement) return;

    // Default center (UUM)
    const defaultLat = 6.460195;
    const defaultLng = 100.505501;

    const reportMap = L.map('location-map').setView([defaultLat, defaultLng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(reportMap);

    let marker;

    // Function to update marker and inputs
    function placeMarker(lat, lng) {
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(reportMap);

            // Update inputs when dragged
            marker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                if (latInput) latInput.value = pos.lat;
                if (lngInput) lngInput.value = pos.lng;
            });
        }

        if (latInput) latInput.value = lat;
        if (lngInput) lngInput.value = lng;
    }

    // Click map to place marker
    reportMap.on('click', function (e) {
        placeMarker(e.latlng.lat, e.latlng.lng);
    });

    // Fix map rendering issue when modal opens (Leaflet needs to know container size)
    const openModalBtn = document.getElementById('open-report-modal');
    if (openModalBtn) {
        openModalBtn.addEventListener('click', function () {
            setTimeout(() => {
                reportMap.invalidateSize();
            }, 200);
        });
    }
}

// --- 3. HELPER: FILTER BY LOCATION ---
// This function is called when user clicks "Show Items Here" in the map popup
window.filterByLocation = function (locationName) {
    const locationSelect = document.getElementById('filter-location');
    const searchSection = document.getElementById('search-results');

    if (locationSelect) {
        // 1. Set the dropdown value
        locationSelect.value = locationName;

        // 2. Handle partial matches if exact match fails
        // (e.g. Map says "DKG 1 (Room 101)" but dropdown only has "DKG 1")
        if (locationSelect.value === "") {
            // Try to find a partial match in the dropdown options
            for (let i = 0; i < locationSelect.options.length; i++) {
                if (locationName.includes(locationSelect.options[i].value) && locationSelect.options[i].value !== "") {
                    locationSelect.selectedIndex = i;
                    break;
                }
            }
        }

        // 3. Trigger Search (Calls the function in app.js)
        if (typeof performSearch === 'function') {
            performSearch();
        }

        // 4. Scroll down to results
        if (searchSection) {
            searchSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
};