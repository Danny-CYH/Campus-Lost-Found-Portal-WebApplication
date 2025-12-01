// js/report-item.js

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('reportForm');
    
    // --- HELPER FUNCTIONS FOR ERRORS ---
    function showError(elementId, message) {
        const errorEl = document.getElementById(elementId);
        if (errorEl) {
            errorEl.innerText = message;
            errorEl.classList.remove('hidden');
        }
    }

    function clearErrors() {
        // Select all error messages we created and hide them
        const errorMessages = document.querySelectorAll('[id^="error-"]');
        errorMessages.forEach(el => {
            el.innerText = '';
            el.classList.add('hidden');
        });
    }

    // --- FORM VALIDATION ---
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Stop submission to check data
            clearErrors(); // Remove old red text

            let isValid = true; // Assume form is good, set to false if we find errors

            // 1. Get Elements
            const statusEl = form.querySelector('input[name="status"]:checked');
            const titleEl = form.querySelector('input[name="title"]');
            const dateEl = form.querySelector('input[name="date_occurred"]');
            const categoryEl = form.querySelector('select[name="category"]');
            const locationEl = form.querySelector('select[name="location_name"]');
            const descEl = form.querySelector('textarea[name="description"]');
            const secretEl = form.querySelector('input[name="secret_identifier"]');
            const latEl = document.getElementById('latitude');
            const lngEl = document.getElementById('longitude');

            // 2. Validate Status
            if (!statusEl) {
                showError('error-status', 'Please select Lost or Found.');
                isValid = false;
            }

            // 3. Validate Title
            if (!titleEl || !titleEl.value.trim()) {
                showError('error-title', 'Item title is required.');
                isValid = false;
            }

            // 4. Validate Date (Empty + Future Logic)
            if (!dateEl || !dateEl.value.trim()) {
                showError('error-date', 'Date is required.');
                isValid = false;
            } else {
                // --- FIXED DATE LOGIC START ---
                // We split the string manually to force Local Time construction
                // This avoids UTC timezone confusion
                const parts = dateEl.value.split('-'); // e.g. ["2025", "12", "01"]
                const year = parseInt(parts[0], 10);
                const month = parseInt(parts[1], 10) - 1; // Months are 0-11 in JS
                const day = parseInt(parts[2], 10);
                
                const selectedDate = new Date(year, month, day); // Local Midnight
                
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Local Midnight
                
                if (selectedDate > today) {
                    showError('error-date', 'Date cannot be in the future.');
                    isValid = false;
                }
            }

            // 5. Validate Category
            if (!categoryEl || !categoryEl.value.trim()) {
                showError('error-category', 'Please select a category.');
                isValid = false;
            }

            // 6. Validate Location Name
            if (!locationEl || !locationEl.value.trim()) {
                showError('error-location', 'Please select a location.');
                isValid = false;
            }

            // 7. Validate Map Pins
            if (!latEl || !lngEl || latEl.value === "" || lngEl.value === "") {
                showError('error-map', 'Please pin the location on the map above.');
                isValid = false;
            }

            // 8. Validate Description
            if (!descEl || !descEl.value.trim()) {
                showError('error-description', 'Please provide a description.');
                isValid = false;
            }

            // 9. Validate Secret Identifier
            if (!secretEl || !secretEl.value.trim()) {
                showError('error-secret', 'Secret identifier is required.');
                isValid = false;
            }

            // --- FINAL CHECK ---
            if (isValid) {
                form.submit(); // Everything good, send to PHP
            } else {
                // Optional: Scroll to top so they see the first error
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }

    // ====== LEAFLET MAP LOGIC (UNCHANGED) ======
    const mapDiv = document.getElementById('map');
    if (mapDiv) {
        const dbData = (typeof locationsDB !== 'undefined') ? locationsDB : {};
        const firstKey = Object.keys(dbData)[0];
        const defaultLat = firstKey ? dbData[firstKey].lat : 6.466;
        const defaultLng = firstKey ? dbData[firstKey].lng : 100.507;

        const map = L.map('map').setView([defaultLat, defaultLng], 16);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

        const latInput = document.getElementById('latitude');
        const lngInput = document.getElementById('longitude');

        function updateLatLng(lat, lng) {
            if (latInput) latInput.value = lat;
            if (lngInput) lngInput.value = lng;
            
            // UX Bonus: If user moves pin, remove the red error message immediately
            const errorMap = document.getElementById('error-map');
            if (errorMap) errorMap.classList.add('hidden');
        }

        updateLatLng(defaultLat, defaultLng);

        marker.on('dragend', function (e) {
            const coords = marker.getLatLng();
            updateLatLng(coords.lat, coords.lng);
        });

        const locationSelect = document.getElementById('location_input');
        if (locationSelect) {
            locationSelect.addEventListener('change', function () {
                const selectedName = locationSelect.value;
                if (selectedName && dbData[selectedName]) {
                    const coords = dbData[selectedName];
                    marker.setLatLng([coords.lat, coords.lng]);
                    map.setView([coords.lat, coords.lng], 16);
                    updateLatLng(coords.lat, coords.lng);
                }
            });
        }
    }
});