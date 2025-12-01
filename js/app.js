// Main application functionality
document.addEventListener('DOMContentLoaded', function () {
    // Report modal functionality
    const reportModal = document.getElementById('report-modal');
    const openReportModalBtn = document.getElementById('open-report-modal');
    const closeReportModalBtn = document.getElementById('close-report-modal');
    const cancelReportBtn = document.getElementById('cancel-report');

    const menuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (menuButton && mobileMenu) {
        menuButton.addEventListener('click', function () {
            // Toggles the 'hidden' class on the mobile menu div
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    if (openReportModalBtn) {
        openReportModalBtn.addEventListener('click', function () {
            reportModal.classList.remove('hidden');
        });
    }

    if (closeReportModalBtn) {
        closeReportModalBtn.addEventListener('click', function () {
            reportModal.classList.add('hidden');
        });
    }

    if (cancelReportBtn) {
        cancelReportBtn.addEventListener('click', function () {
            reportModal.classList.add('hidden');
        });
    }

    // Image preview for report form
    const imageInput = document.getElementById('image');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview');

    if (imageInput) {
        imageInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const reader = new FileReader();

                reader.onload = function (e) {
                    previewImg.src = e.target.result;
                    imagePreview.classList.remove('hidden');
                }

                reader.readAsDataURL(this.files[0]);
            } else {
                imagePreview.classList.add('hidden');
            }
        });
    }

    // Report form submission
    const reportForm = document.getElementById('report-form');
    if (reportForm) {
        reportForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('api/items.php?action=create', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Item reported successfully!');
                        reportModal.classList.add('hidden');
                        reportForm.reset();
                        imagePreview.classList.add('hidden');
                        location.reload(); // Refresh to show new item
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while submitting the report.');
                });
        });
    }

    // Search and filter functionality
    const searchTerm = document.getElementById('search-term');
    const filterCategory = document.getElementById('filter-category');
    const filterStatus = document.getElementById('filter-status');
    const filterDate = document.getElementById('filter-date');

    [searchTerm, filterCategory, filterStatus, filterDate].forEach(element => {
        if (element) {
            element.addEventListener('input', performSearch);
        }
    });

    // Mark item as returned
    document.querySelectorAll('.mark-returned').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const itemId = this.getAttribute('data-id');

            if (confirm('Are you sure you want to mark this item as returned?')) {
                fetch('api/items.php?action=markReturned', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `id=${itemId}`
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Item marked as returned!');
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred.');
                    });
            }
        });
    });

    // View item details
    document.querySelectorAll('.view-item').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const itemId = this.getAttribute('data-id');
            viewItemDetails(itemId);
        });
    });

    // View claim details
    document.querySelectorAll('.view-claim').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const claimId = this.getAttribute('data-id');
            viewClaimDetails(claimId);
        });
    });
});

function performSearch() {
    const searchTerm = document.getElementById('search-term').value;
    const category = document.getElementById('filter-category').value;
    const status = document.getElementById('filter-status').value;
    const date = document.getElementById('filter-date').value;

    const params = new URLSearchParams();
    if (searchTerm) params.append('search', searchTerm);
    if (category) params.append('category', category);
    if (status) params.append('status', status);
    if (date) params.append('date', date);

    fetch(`api/search.php?${params.toString()}`)
        .then(response => response.json())
        .then(items => {
            displaySearchResults(items);
        })
        .catch(error => console.error('Error searching:', error));
}

function displaySearchResults(items) {
    const resultsContainer = document.getElementById('search-results');

    if (items.length === 0) {
        resultsContainer.innerHTML = '<p class="text-center py-4 text-gray-500">No items found matching your criteria.</p>';
        return;
    }

    let html = '';
    items.forEach(item => {
        html += `
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex flex-col md:flex-row items-start md:items-center">
                ${item.image_path ? `
                <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-4">
                    <img src="${item.image_path}" alt="${item.title}" class="h-24 w-24 object-cover rounded-lg">
                </div>
                ` : ''}
                <div class="flex-grow">
                    <h3 class="text-lg font-semibold">${item.title}</h3>
                    <div class="flex flex-wrap gap-2 mt-1">
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">${item.category}</span>
                        <span class="px-2 py-1 text-xs rounded-full ${item.status === 'lost' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'}">${item.status}</span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">${item.description.substring(0, 150)}${item.description.length > 150 ? '...' : ''}</p>
                    <div class="flex items-center mt-2 text-sm text-gray-500 dark:text-gray-400">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        <span>${item.location_name}</span>
                        <span class="mx-2">•</span>
                        <i class="fas fa-calendar-alt mr-1"></i>
                        <span>${new Date(item.date_occurred).toLocaleDateString()}</span>
                    </div>
                </div>
                <div class="mt-4 md:mt-0 md:ml-4 flex space-x-2">
                    <button onclick="viewItemDetails(${item.id})" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                        <i class="fas fa-eye mr-1"></i> View
                    </button>
                    ${item.status === 'found' ? `
                    <button onclick="openClaimModal(${item.id})" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-sm">
                        <i class="fas fa-hand-holding-heart mr-1"></i> Claim
                    </button>
                    ` : ''}
                </div>
            </div>
        `;
    });

    resultsContainer.innerHTML = html;
}

function viewItemDetails(itemId) {
    fetch(`api/items.php?action=get&id=${itemId}`)
        .then(response => response.json())
        .then(item => {
            showItemModal(item);
        })
        .catch(error => console.error('Error fetching item details:', error));
}

function viewClaimDetails(claimId) {
    fetch(`api/claims.php?action=get&id=${claimId}`)
        .then(response => response.json())
        .then(claim => {
            showClaimModal(claim);
        })
        .catch(error => console.error('Error fetching claim details:', error));
}

function showClaimModal(claim) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50';
    modal.innerHTML = `
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white dark:bg-gray-800">
            <div class="flex justify-between items-center pb-3 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-medium text-gray-900 dark:text-white">Claim Review: ${claim.title}</h3>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-gray-400 hover:text-gray-500">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mt-4 space-y-4">
                <div>
                    <h4 class="font-semibold">Claimant Information</h4>
                    <p>Name: ${claim.claimant_name}</p>
                    <p>Submitted: ${new Date(claim.created_at).toLocaleDateString()}</p>
                </div>
                
                <div>
                    <h4 class="font-semibold">Claimant's Description</h4>
                    <p class="mt-1 p-3 bg-gray-100 dark:bg-gray-700 rounded">${claim.claimant_description}</p>
                </div>
                
                ${claim.image_path ? `
                <div>
                    <h4 class="font-semibold">Evidence Provided</h4>
                    <img src="${claim.image_path}" class="mt-2 rounded-lg max-w-xs">
                </div>
                ` : ''}
                
                <div>
                    <h4 class="font-semibold">Secret Identifier Provided</h4>
                    <p class="mt-1 p-3 bg-gray-100 dark:bg-gray-700 rounded">${claim.secret_provided}</p>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button onclick="processClaim(${claim.id}, 'rejected')" class="px-4 py-2 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-md">
                        Reject Claim
                    </button>
                    <button onclick="processClaim(${claim.id}, 'verified')" class="px-4 py-2 text-sm font-medium text-white bg-green-500 hover:bg-green-600 rounded-md">
                        Verify Claim
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function processClaim(claimId, action) {
    fetch('api/claims.php?action=process', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${claimId}&action=${action}`
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Claim ${action} successfully!`);
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred.');
        });
}