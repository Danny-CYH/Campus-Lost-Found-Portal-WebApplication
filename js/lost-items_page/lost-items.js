document.addEventListener('DOMContentLoaded', function () {

    // 1. SELECT ELEMENTS
    const searchInput = document.getElementById('search-input');
    const clearSearch = document.getElementById('clear-search');
    const filterToggle = document.getElementById('filter-toggle');
    const mobileFilterPanel = document.getElementById('mobile-filter-panel');

    const categoryFilter = document.getElementById('category-filter');
    const locationFilter = document.getElementById('location-filter');
    const dateFilter = document.getElementById('date-filter');
    const sortFilter = document.getElementById('sort-filter');

    // Mobile Filters
    const mobileCategoryFilter = document.getElementById('mobile-category-filter');
    const mobileLocationFilter = document.getElementById('mobile-location-filter');
    const mobileDateFilter = document.getElementById('mobile-date-filter');
    const mobileSortFilter = document.getElementById('mobile-sort-filter');
    const mobileApplyFilters = document.getElementById('mobile-apply-filters');
    const mobileClearFilters = document.getElementById('mobile-clear-filters');

    const clearFilters = document.getElementById('clear-filters');
    const resetFilters = document.getElementById('reset-filters');

    const itemsGrid = document.getElementById('items-grid');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');
    const resultsCount = document.getElementById('results-count');
    const resultsDescription = document.getElementById('results-description');

    const viewToggles = document.querySelectorAll('.view-toggle');
    const paginationBtns = document.querySelectorAll('.pagination-btn');
    const activeFilters = document.getElementById('active-filters');

    // 2. STATE MANAGEMENT
    let currentState = {
        search: '',
        category: '',
        location: '',
        date: '',
        sort: 'newest',
        view: 'grid',
        page: 1,
        itemsPerPage: 12
    };

    // 3. INITIALIZE
    init();

    function init() {
        setupEventListeners();
        // Run filter immediately to show items
        filterItems();
    }

    // 4. MAIN FILTER FUNCTION
    function filterItems() {
        showLoading();

        // Small delay to allow UI to show spinner
        setTimeout(() => {
            // Get all items freshly from the DOM
            const allItems = Array.from(document.querySelectorAll('.item-card'));

            // A. FILTER
            const matchingItems = allItems.filter(item => {
                return matchesSearchCriteria(item) &&
                    matchesCategoryCriteria(item) &&
                    matchesLocationCriteria(item) &&
                    matchesDateCriteria(item);
            });

            // B. SORT
            sortMatchingItems(matchingItems);

            // C. PAGINATION LOGIC
            const totalItems = matchingItems.length;
            const totalPages = Math.ceil(totalItems / currentState.itemsPerPage) || 1;

            // Fix page number bounds
            if (currentState.page > totalPages) currentState.page = 1;
            if (currentState.page < 1) currentState.page = 1;

            const startIndex = (currentState.page - 1) * currentState.itemsPerPage;
            const endIndex = startIndex + currentState.itemsPerPage;

            // D. RENDER
            // First hide ALL items
            allItems.forEach(item => item.style.display = 'none');

            // Show only the slice for current page
            matchingItems.slice(startIndex, endIndex).forEach(item => {
                item.style.display = 'block';
                // Apply Grid/List styling
                if (currentState.view === 'list') {
                    item.classList.add('flex');
                    item.classList.remove('flex-col');
                } else {
                    item.classList.remove('flex');
                    item.classList.add('flex-col');
                }
            });

            // Re-append to grid to visually reorder them based on sort
            if (itemsGrid) {
                matchingItems.forEach(item => itemsGrid.appendChild(item));
            }

            // Update UI elements
            updateResultsText(totalItems);
            updateActiveFiltersUI();
            updatePaginationUI(totalItems, totalPages);

            // ✅ CRITICAL: Hide loading spinner
            hideLoading();

        }, 300);
    }

    // 5. HELPER: MATCHING FUNCTIONS
    function matchesSearchCriteria(item) {
        if (!currentState.search) return true;

        const term = currentState.search.toLowerCase();
        // Use optional chaining (?.) to prevent crashes if element missing
        const title = item.querySelector('h3')?.textContent.toLowerCase() || '';
        const desc = item.querySelector('p')?.textContent.toLowerCase() || '';
        const loc = (item.getAttribute('data-location') || '').toLowerCase();

        return title.includes(term) || desc.includes(term) || loc.includes(term);
    }

    function matchesCategoryCriteria(item) {
        if (!currentState.category) return true;
        const cat = (item.getAttribute('data-category') || '').toLowerCase();
        return cat.includes(currentState.category.toLowerCase());
    }

    function matchesLocationCriteria(item) {
        if (!currentState.location) return true;
        const loc = (item.getAttribute('data-location') || '').toLowerCase();
        return loc.includes(currentState.location.toLowerCase());
    }

    function matchesDateCriteria(item) {
        if (!currentState.date) return true;

        const dateStr = item.getAttribute('data-date');
        if (!dateStr) return true; // Keep item if no date found

        const itemDate = new Date(dateStr);
        const now = new Date();

        // Reset times to compare dates properly
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        const itemDateOnly = new Date(itemDate.getFullYear(), itemDate.getMonth(), itemDate.getDate());

        if (currentState.date === 'today') {
            return itemDateOnly.getTime() === today.getTime();
        }
        else if (currentState.date === 'week') {
            const weekAgo = new Date(today);
            weekAgo.setDate(today.getDate() - 7);
            return itemDate >= weekAgo;
        }
        else if (currentState.date === 'month') {
            const monthAgo = new Date(today);
            monthAgo.setMonth(today.getMonth() - 1);
            return itemDate >= monthAgo;
        }
        return true;
    }

    // 6. HELPER: SORT FUNCTION
    function sortMatchingItems(items) {
        items.sort((a, b) => {
            const dateA = new Date(a.getAttribute('data-date') || 0);
            const dateB = new Date(b.getAttribute('data-date') || 0);

            if (currentState.sort === 'oldest') {
                return dateA - dateB;
            } else {
                // newest or recent
                return dateB - dateA;
            }
        });
    }

    // 7. HELPER: UI UPDATES
    function updateResultsText(count) {
        if (resultsCount) resultsCount.textContent = count;

        if (count === 0) {
            if (emptyState) emptyState.classList.remove('hidden');
            if (itemsGrid) itemsGrid.classList.add('hidden');
            if (resultsDescription) resultsDescription.textContent = 'No items match your current filters';
        } else {
            if (emptyState) emptyState.classList.add('hidden');
            if (itemsGrid) itemsGrid.classList.remove('hidden');

            if (resultsDescription) {
                let desc = `Showing ${count} lost items`;
                if (currentState.search) desc += ` matching "${currentState.search}"`;
                resultsDescription.textContent = desc;
            }
        }
    }

    function updateActiveFiltersUI() {
        if (!activeFilters) return;
        activeFilters.innerHTML = '';
        const filters = [];

        if (currentState.search) filters.push({ label: `Search: "${currentState.search}"`, type: 'search' });
        if (currentState.category) filters.push({ label: `Category: ${currentState.category}`, type: 'category' });
        if (currentState.location) filters.push({ label: `Location: ${currentState.location}`, type: 'location' });
        if (currentState.date) filters.push({ label: `Date: ${currentState.date}`, type: 'date' });

        if (filters.length > 0) {
            activeFilters.classList.remove('hidden');
            filters.forEach(f => {
                const badge = document.createElement('div');
                badge.className = 'flex items-center bg-green-100 text-green-800 px-3 py-1 rounded-full text-sm mr-2 mb-2';
                badge.innerHTML = `<span>${f.label}</span><button class="ml-2 hover:text-red-500 remove-filter" data-type="${f.type}"><i class="fas fa-times"></i></button>`;
                activeFilters.appendChild(badge);
            });

            document.querySelectorAll('.remove-filter').forEach(btn => {
                btn.addEventListener('click', function () {
                    removeSpecificFilter(this.getAttribute('data-type'));
                });
            });
        } else {
            activeFilters.classList.add('hidden');
        }
    }

    function updatePaginationUI(totalItems, totalPages) {
        const startItem = totalItems === 0 ? 0 : (currentState.page - 1) * currentState.itemsPerPage + 1;
        const endItem = Math.min(currentState.page * currentState.itemsPerPage, totalItems);

        const startEl = document.getElementById('pagination-start');
        const endEl = document.getElementById('pagination-end');
        const totalEl = document.getElementById('pagination-total');

        if (startEl) startEl.textContent = startItem;
        if (endEl) endEl.textContent = endItem;
        if (totalEl) totalEl.textContent = totalItems;

        paginationBtns.forEach(btn => {
            const type = btn.getAttribute('data-page');
            if (type === 'prev') {
                btn.disabled = currentState.page <= 1;
                btn.classList.toggle('opacity-50', currentState.page <= 1);
            } else if (type === 'next') {
                btn.disabled = currentState.page >= totalPages;
                btn.classList.toggle('opacity-50', currentState.page >= totalPages);
            } else {
                // Number buttons (simplified logic: highlight if matches current page)
                const pageNum = parseInt(type);
                if (!isNaN(pageNum)) {
                    if (pageNum === currentState.page) {
                        btn.classList.add('bg-green-600', 'text-white');
                        btn.classList.remove('bg-gray-100', 'text-gray-700');
                    } else {
                        btn.classList.remove('bg-green-600', 'text-white');
                        btn.classList.add('bg-gray-100', 'text-gray-700');
                    }
                }
            }
        });
    }

    function showLoading() {
        if (loadingState) loadingState.classList.remove('hidden');
        if (itemsGrid) itemsGrid.classList.add('hidden');
        if (emptyState) emptyState.classList.add('hidden');
    }

    function hideLoading() {
        if (loadingState) loadingState.classList.add('hidden');
    }

    function removeSpecificFilter(type) {
        if (type === 'search') { currentState.search = ''; if (searchInput) searchInput.value = ''; }
        if (type === 'category') { currentState.category = ''; if (categoryFilter) categoryFilter.value = ''; }
        if (type === 'location') { currentState.location = ''; if (locationFilter) locationFilter.value = ''; }
        if (type === 'date') { currentState.date = ''; if (dateFilter) dateFilter.value = ''; }
        filterItems();
    }

    // 8. HELPERS FOR LABELS (Updated to use values directly)
    function getCategoryLabel(val) { return val; }
    function getLocationLabel(val) { return val; }
    function getDateLabel(val) {
        const map = { 'today': 'Today', 'week': 'This Week', 'month': 'This Month' };
        return map[val] || val;
    }
    
    // 8. SETUP EVENT LISTENERS
    function setupEventListeners() {
        // Search
        if (searchInput) {
            searchInput.addEventListener('input', debounce(function (e) {
                currentState.search = e.target.value;
                currentState.page = 1;
                filterItems();
            }, 300));
        }

        if (clearSearch) {
            clearSearch.addEventListener('click', function () {
                if (searchInput) searchInput.value = '';
                currentState.search = '';
                filterItems();
            });
        }

        // Filters
        if (categoryFilter) categoryFilter.addEventListener('change', (e) => { currentState.category = e.target.value; currentState.page = 1; filterItems(); });
        if (locationFilter) locationFilter.addEventListener('change', (e) => { currentState.location = e.target.value; currentState.page = 1; filterItems(); });
        if (dateFilter) dateFilter.addEventListener('change', (e) => { currentState.date = e.target.value; currentState.page = 1; filterItems(); });
        if (sortFilter) sortFilter.addEventListener('change', (e) => { currentState.sort = e.target.value; filterItems(); });

        // Mobile Filter Panel
        if (filterToggle && mobileFilterPanel) {
            filterToggle.addEventListener('click', () => mobileFilterPanel.classList.toggle('hidden'));
        }

        if (mobileApplyFilters) {
            mobileApplyFilters.addEventListener('click', () => {
                if (mobileCategoryFilter) currentState.category = mobileCategoryFilter.value;
                if (mobileLocationFilter) currentState.location = mobileLocationFilter.value;
                if (mobileDateFilter) currentState.date = mobileDateFilter.value;
                if (mobileSortFilter) currentState.sort = mobileSortFilter.value;
                currentState.page = 1;
                mobileFilterPanel.classList.add('hidden');
                filterItems();
            });
        }

        if (mobileClearFilters) {
            mobileClearFilters.addEventListener('click', () => {
                if (mobileCategoryFilter) mobileCategoryFilter.value = '';
                if (mobileLocationFilter) mobileLocationFilter.value = '';
                if (mobileDateFilter) mobileDateFilter.value = '';
                if (mobileSortFilter) mobileSortFilter.value = 'newest';
            });
        }

        // Global Clear
        if (clearFilters) clearFilters.addEventListener('click', clearAllState);
        if (resetFilters) resetFilters.addEventListener('click', clearAllState);

        // Pagination
        paginationBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const page = this.getAttribute('data-page');
                if (page === 'prev') {
                    if (currentState.page > 1) currentState.page--;
                } else if (page === 'next') {
                    // Logic handled in filterItems validation, just increment
                    currentState.page++;
                } else {
                    currentState.page = parseInt(page);
                }
                filterItems();
            });
        });

        // Items Per Page
        const perPageEl = document.getElementById('items-per-page');
        if (perPageEl) {
            perPageEl.addEventListener('change', function (e) {
                currentState.itemsPerPage = parseInt(e.target.value);
                currentState.page = 1;
                filterItems();
            });
        }

        // View Toggle
        viewToggles.forEach(toggle => {
            toggle.addEventListener('click', function () {
                currentState.view = this.getAttribute('data-view');
                // Update active button state
                viewToggles.forEach(t => {
                    t.classList.remove('active', 'bg-white', 'text-gray-700', 'shadow-sm');
                    t.classList.add('text-gray-500');
                });
                this.classList.add('active', 'bg-white', 'text-gray-700', 'shadow-sm');
                this.classList.remove('text-gray-500');
                filterItems();
            });
        });
    }

    function clearAllState() {
        currentState = { search: '', category: '', location: '', date: '', sort: 'newest', view: 'grid', page: 1, itemsPerPage: 12 };
        if (searchInput) searchInput.value = '';
        if (categoryFilter) categoryFilter.value = '';
        if (locationFilter) locationFilter.value = '';
        if (dateFilter) dateFilter.value = '';
        if (sortFilter) sortFilter.value = 'newest';
        if (mobileFilterPanel) mobileFilterPanel.classList.add('hidden');
        filterItems();
    }

    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
});