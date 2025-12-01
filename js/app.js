// 1. DEFINE FUNCTIONS FIRST (Global Scope)

// Main Search Function (Updated with Time & Sort)
function performSearch() {
    const searchTerm = document.getElementById('search-term')?.value || '';
    const category = document.getElementById('filter-category')?.value || '';
    const status = document.getElementById('filter-status')?.value || '';
    const time = document.getElementById('filter-time')?.value || '';
    const sort = document.getElementById('filter-sort')?.value || 'newest';
    const location = document.getElementById('filter-location')?.value || '';

    const params = new URLSearchParams();
    if (searchTerm) params.append('search', searchTerm);
    if (category) params.append('category', category);
    if (status) params.append('status', status);
    if (time) params.append('time', time);
    if (sort) params.append('sort', sort);
    if (location) params.append('location', location);

    fetch(`api/search.php?${params.toString()}`)
        .then(response => response.json())
        .then(items => {
            displaySearchResults(items);
        })
        .catch(error => console.error('Error searching:', error));
}

// Display Results Function (Updated with Returned Logic)
function displaySearchResults(items) {
    const resultsContainer = document.getElementById('search-results');

    // Safety check: If we are not on the dashboard, stop here
    if (!resultsContainer) return;

    if (items.length === 0) {
        resultsContainer.innerHTML = '<p class="text-center py-4 text-gray-500">No items found matching your criteria.</p>';
        return;
    }

    let html = '';
    items.forEach(item => {
        let badgeClass = '';
        let badgeText = '';

        if (item.is_returned == 1) {
            badgeClass = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
            badgeText = item.status.charAt(0).toUpperCase() + item.status.slice(1) + ' - Returned';
        } else {
            if (item.status === 'lost') {
                badgeClass = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
            } else {
                badgeClass = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
            }
            badgeText = item.status.charAt(0).toUpperCase() + item.status.slice(1);
        }

        const shortDesc = item.description.length > 150
            ? item.description.substring(0, 150) + '...'
            : item.description;

        html += `
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 flex flex-col md:flex-row items-start md:items-center transition-all hover:shadow-md">
                ${item.image_path ? `
                <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-4">
                    <img src="${item.image_path}" alt="${item.title}" class="h-24 w-24 object-cover rounded-lg">
                </div>
                ` : `
                <div class="flex-shrink-0 mb-4 md:mb-0 md:mr-4 h-24 w-24 bg-gray-200 dark:bg-gray-700 rounded-lg flex items-center justify-center text-gray-400">
                    <i class="fas fa-image text-2xl"></i>
                </div>
                `}
                
                <div class="flex-grow">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">${item.title}</h3>
                    <div class="flex flex-wrap gap-2 mt-1">
                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                            ${item.category}
                        </span>
                        <span class="px-2 py-1 text-xs rounded-full ${badgeClass}">
                            ${badgeText}
                        </span>
                    </div>
                    <p class="text-gray-600 dark:text-gray-400 mt-2 text-sm">${shortDesc}</p>
                    <div class="flex items-center mt-2 text-xs text-gray-500 dark:text-gray-400">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        <span>${item.location_name}</span>
                        <span class="mx-2">•</span>
                        <i class="fas fa-calendar-alt mr-1"></i>
                        <span>${new Date(item.date_occurred).toLocaleDateString()}</span>
                    </div>
                </div>
                
                <div class="mt-4 md:mt-0 md:ml-4 flex space-x-2">
                    <a href="view-item.php?id=${item.id}" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm transition-colors">
                        <i class="fas fa-eye mr-1"></i> View
                    </a>
                </div>
            </div>
        `;
    });

    resultsContainer.innerHTML = html;
}

// Mark Returned Function
function markItemReturned(itemId) {
    if (!confirm("Are you sure you want to mark this item as Returned?")) {
        return;
    }
    fetch('api/items.php?action=mark_returned', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `item_id=${itemId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Item successfully marked as Returned!");
            location.reload(); 
        } else {
            alert("Error: " + (data.message || "Unknown error."));
        }
    });
}

// Helper Functions (Modals, Claims)
function viewItemDetails(itemId) {
    fetch(`api/items.php?action=get&id=${itemId}`)
        .then(response => response.json())
        .then(item => { showItemModal(item); })
        .catch(error => console.error('Error fetching item details:', error));
}

function viewClaimDetails(claimId) {
    fetch(`api/claims.php?action=get&id=${claimId}`)
        .then(response => response.json())
        .then(claim => { showClaimModal(claim); })
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
                <div><h4 class="font-semibold">Claimant Information</h4><p>Name: ${claim.claimant_name}</p></div>
                <div><h4 class="font-semibold">Description</h4><p class="mt-1 p-3 bg-gray-100 dark:bg-gray-700 rounded">${claim.claimant_description}</p></div>
                ${claim.image_path ? `<div><h4 class="font-semibold">Evidence</h4><img src="${claim.image_path}" class="mt-2 rounded-lg max-w-xs"></div>` : ''}
                <div><h4 class="font-semibold">Secret Identifier</h4><p class="mt-1 p-3 bg-gray-100 dark:bg-gray-700 rounded">${claim.secret_provided}</p></div>
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button onclick="processClaim(${claim.id}, 'rejected')" class="px-4 py-2 text-sm font-medium text-white bg-red-500 hover:bg-red-600 rounded-md">Reject Claim</button>
                    <button onclick="processClaim(${claim.id}, 'verified')" class="px-4 py-2 text-sm font-medium text-white bg-green-500 hover:bg-green-600 rounded-md">Verify Claim</button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

function processClaim(claimId, action) {
    fetch('api/claims.php?action=process', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `id=${claimId}&action=${action}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) { alert(`Claim ${action} successfully!`); location.reload(); }
        else { alert('Error: ' + data.message); }
    });
}


// 2. MAIN EXECUTION (Event Listeners)
document.addEventListener('DOMContentLoaded', function () {
    
    // --- SEARCH LOGIC ---
    const searchInput = document.getElementById('search-term');
    const categorySelect = document.getElementById('filter-category');
    const statusSelect = document.getElementById('filter-status');
    const timeSelect = document.getElementById('filter-time'); 
    const sortSelect = document.getElementById('filter-sort'); 
    
    const searchBtn = document.getElementById('search-btn');
    const resetBtn = document.getElementById('reset-search-btn');

    // Restore Search State (Fixes "Back" Button Issue)
    if (searchInput && sessionStorage.getItem('hasSearched') === 'true') {
        searchInput.value = sessionStorage.getItem('searchVal') || '';
        if(categorySelect) categorySelect.value = sessionStorage.getItem('categoryVal') || '';
        if(statusSelect) statusSelect.value = sessionStorage.getItem('statusVal') || '';
        if(timeSelect) timeSelect.value = sessionStorage.getItem('timeVal') || ''; 
        if(sortSelect) sortSelect.value = sessionStorage.getItem('sortVal') || 'newest'; 
        
        performSearch(); // Now safe because function is defined at top
    }

    // Attach Search Listener
    if (searchBtn) {
        searchBtn.addEventListener('click', function() {
            sessionStorage.setItem('hasSearched', 'true');
            sessionStorage.setItem('searchVal', searchInput ? searchInput.value : '');
            sessionStorage.setItem('categoryVal', categorySelect ? categorySelect.value : '');
            sessionStorage.setItem('statusVal', statusSelect ? statusSelect.value : '');
            sessionStorage.setItem('timeVal', timeSelect ? timeSelect.value : '');
            sessionStorage.setItem('sortVal', sortSelect ? sortSelect.value : 'newest');

            performSearch();
        });
    }

    // Attach Enter Key Listener
    if (searchInput) {
        searchInput.addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                if(searchBtn) searchBtn.click();
            }
        });
    }

    // Attach Reset Listener
    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if(searchInput) searchInput.value = '';
            if(categorySelect) categorySelect.value = '';
            if(statusSelect) statusSelect.value = '';
            if(timeSelect) timeSelect.value = ''; 
            if(sortSelect) sortSelect.value = 'newest';

            sessionStorage.clear(); 
            const resultsContainer = document.getElementById('search-results');
            if(resultsContainer) resultsContainer.innerHTML = '';
        });
    }

    // --- MOBILE MENU ---
    const menuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    if (menuButton && mobileMenu) {
        menuButton.addEventListener('click', function () {
            mobileMenu.classList.toggle('hidden');
        });
    }
    
    // --- REPORT MODAL ---
    const reportModal = document.getElementById('report-modal');
    const openReportModalBtn = document.getElementById('open-report-modal');
    const closeReportModalBtn = document.getElementById('close-report-modal');
    const cancelReportBtn = document.getElementById('cancel-report');
    
    if (openReportModalBtn && reportModal) openReportModalBtn.addEventListener('click', () => reportModal.classList.remove('hidden'));
    if (closeReportModalBtn && reportModal) closeReportModalBtn.addEventListener('click', () => reportModal.classList.add('hidden'));
    if (cancelReportBtn && reportModal) cancelReportBtn.addEventListener('click', () => reportModal.classList.add('hidden'));

    // --- IMAGE PREVIEW ---
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

    // --- REPORT FORM SUBMIT ---
    const reportForm = document.getElementById('report-form');
    if (reportForm) {
        reportForm.addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);

            fetch('api/items.php?action=create', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Item reported successfully!');
                    location.reload(); 
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        });
    }

    // --- VIEW ITEM & CLAIM DETAILS (Delegation or Direct) ---
    // Note: These use the helper functions defined at the top
    document.querySelectorAll('.view-item').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const itemId = this.getAttribute('data-id');
            viewItemDetails(itemId);
        });
    });

    document.querySelectorAll('.view-claim').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const claimId = this.getAttribute('data-id');
            viewClaimDetails(claimId);
        });
    });
});