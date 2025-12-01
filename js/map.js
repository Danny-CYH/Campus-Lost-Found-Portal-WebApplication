// Initialize campus map
let map;
let markers = [];
let locationMarker;

function initMap() {
    // Default to campus center (adjust coordinates to your campus)
    const campusCenter = { lat: 40.7589, lng: -73.9851 };

    map = new google.maps.Map(document.getElementById('map'), {
        zoom: 15,
        center: campusCenter,
        styles: [
            {
                featureType: 'poi',
                elementType: 'labels',
                stylers: [{ visibility: 'on' }]
            }
        ]
    });

    // Initialize location map for reporting
    initLocationMap();

    // Load items on the map
    loadItemsOnMap();
}

function initLocationMap() {
    const locationMapElement = document.getElementById('location-map');
    if (!locationMapElement) return;

    const campusCenter = { lat: 40.7589, lng: -73.9851 };
    const locationMap = new google.maps.Map(locationMapElement, {
        zoom: 16,
        center: campusCenter
    });

    // Add marker for location selection
    locationMarker = new google.maps.Marker({
        position: campusCenter,
        map: locationMap,
        draggable: true
    });

    // Update hidden inputs when marker is dragged
    locationMarker.addListener('dragend', function () {
        document.getElementById('latitude').value = locationMarker.getPosition().lat();
        document.getElementById('longitude').value = locationMarker.getPosition().lng();
    });

    // Also allow clicking on map to set location
    locationMap.addListener('click', function (event) {
        locationMarker.setPosition(event.latLng);
        document.getElementById('latitude').value = event.latLng.lat();
        document.getElementById('longitude').value = event.latLng.lng();
    });

    // Set initial values
    document.getElementById('latitude').value = campusCenter.lat;
    document.getElementById('longitude').value = campusCenter.lng;
}

function loadItemsOnMap() {
    // Clear existing markers
    markers.forEach(marker => marker.setMap(null));
    markers = [];

    // Fetch items from API
    fetch('api/items.php?action=getAll')
        .then(response => response.json())
        .then(items => {
            items.forEach(item => {
                if (item.latitude && item.longitude && item.status !== 'returned') {
                    const marker = new google.maps.Marker({
                        position: { lat: parseFloat(item.latitude), lng: parseFloat(item.longitude) },
                        map: map,
                        title: item.title
                    });

                    // Customize marker based on status
                    if (item.status === 'lost') {
                        marker.setIcon({
                            url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
                        });
                    } else if (item.status === 'found') {
                        marker.setIcon({
                            url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
                        });
                    }

                    // Add info window
                    const infoWindow = new google.maps.InfoWindow({
                        content: `
                            <div class="p-2 max-w-xs">
                                <h3 class="font-bold">${item.title}</h3>
                                <p class="text-sm text-gray-600">${item.category}</p>
                                <p class="text-sm">Status: <span class="font-semibold ${item.status === 'lost' ? 'text-red-600' : 'text-green-600'}">${item.status}</span></p>
                                <p class="text-sm">Location: ${item.location_name}</p>
                                <p class="text-sm">Date: ${new Date(item.date_occurred).toLocaleDateString()}</p>
                                ${item.image_path ? `<img src="${item.image_path}" class="mt-2 rounded w-full h-24 object-cover">` : ''}
                                <div class="mt-2">
                                    <button onclick="viewItemDetails(${item.id})" class="text-blue-600 hover:text-blue-800 text-sm font-medium">View Details</button>
                                    ${item.status === 'found' ? `<button onclick="openClaimModal(${item.id})" class="ml-2 text-green-600 hover:text-green-800 text-sm font-medium">Claim</button>` : ''}
                                </div>
                            </div>
                        `
                    });

                    marker.addListener('click', function () {
                        infoWindow.open(map, marker);
                    });

                    markers.push(marker);
                }
            });
        })
        .catch(error => console.error('Error loading items:', error));
}

function viewItemDetails(itemId) {
    // Implementation for viewing item details
    fetch(`api/items.php?action=get&id=${itemId}`)
        .then(response => response.json())
        .then(item => {
            // Show item details in a modal
            showItemModal(item);
        })
        .catch(error => console.error('Error fetching item details:', error));
}

function openClaimModal(itemId) {
    // Implementation for opening claim modal
    const claimModal = document.getElementById('claim-modal');
    if (claimModal) {
        document.getElementById('claim-item-id').value = itemId;
        claimModal.classList.remove('hidden');
    }
}

function showItemModal(item) {
    // Create and show a modal with item details
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-medium text-gray-900 dark:text-white">${item.title}</h3>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    ${item.image_path ? `
                    <div>
                        <img src="${item.image_path}" alt="${item.title}" class="w-full h-64 object-cover rounded-lg">
                    </div>
                    ` : ''}
                    <div class="${item.image_path ? '' : 'col-span-2'}">
                        <div class="space-y-3">
                            <div>
                                <span class="font-semibold">Category:</span> ${item.category}
                            </div>
                            <div>
                                <span class="font-semibold">Status:</span> 
                                <span class="px-2 py-1 text-xs rounded-full ${item.status === 'lost' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'}">
                                    ${item.status}
                                </span>
                            </div>
                            <div>
                                <span class="font-semibold">Location:</span> ${item.location_name}
                            </div>
                            <div>
                                <span class="font-semibold">Date Occurred:</span> ${new Date(item.date_occurred).toLocaleDateString()}
                            </div>
                            <div>
                                <span class="font-semibold">Description:</span>
                                <p class="mt-1">${item.description}</p>
                            </div>
                        </div>
                        ${item.status === 'found' ? `
                        <div class="mt-4">
                            <button onclick="openClaimModal(${item.id})" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">
                                <i class="fas fa-hand-holding-heart mr-1"></i> Claim This Item
                            </button>
                        </div>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

// Initialize map when Google Maps API is loaded
google.maps.event.addDomListener(window, 'load', initMap);