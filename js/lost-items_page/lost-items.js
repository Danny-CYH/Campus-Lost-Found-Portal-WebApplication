// Lost Items Gallery Functionality
document.addEventListener('DOMContentLoaded', function () {
    // Elements
    const searchInput = document.getElementById('search-input');
    const clearSearch = document.getElementById('clear-search');
    const filterToggle = document.getElementById('filter-toggle');
    const mobileFilterPanel = document.getElementById('mobile-filter-panel');
    const filterControls = document.getElementById('filter-controls');
    const activeFilters = document.getElementById('active-filters');
    const clearFilters = document.getElementById('clear-filters');
    const resetFilters = document.getElementById('reset-filters');
    const itemsGrid = document.getElementById('items-grid');
    const loadingState = document.getElementById('loading-state');
    const emptyState = document.getElementById('empty-state');
    const resultsCount = document.getElementById('results-count');
    const resultsDescription = document.getElementById('results-description');
    const viewToggles = document.querySelectorAll('.view-toggle');
    const paginationBtns = document.querySelectorAll('.pagination-btn');

    // Filter elements
    const categoryFilter = document.getElementById('category-filter');
    const locationFilter = document.getElementById('location-filter');
    const dateFilter = document.getElementById('date-filter');
    const sortFilter = document.getElementById('sort-filter');

    // Mobile filter elements
    const mobileCategoryFilter = document.getElementById('mobile-category-filter');
    const mobileLocationFilter = document.getElementById('mobile-location-filter');
    const mobileDateFilter = document.getElementById('mobile-date-filter');
    const mobileSortFilter = document.getElementById('mobile-sort-filter');
    const mobileApplyFilters = document.getElementById('mobile-apply-filters');
    const mobileClearFilters = document.getElementById('mobile-clear-filters');

    // Current state
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

    // Initialize
    init();

    function init() {
        // Event listeners
        setupEventListeners();
        // Load initial items
        filterItems();
    }

    function setupEventListeners() {
        // Search functionality
        searchInput.addEventListener('input', debounce(function (e) {
            currentState.search = e.target.value;
            currentState.page = 1;
            updateClearSearchButton();
            filterItems();
        }, 300));

        clearSearch.addEventListener('click', function () {
            searchInput.value = '';
            currentState.search = '';
            updateClearSearchButton();
            filterItems();
        });

        // Filter functionality
        categoryFilter.addEventListener('change', function (e) {
            currentState.category = e.target.value;
            currentState.page = 1;
            filterItems();
        });

        locationFilter.addEventListener('change', function (e) {
            currentState.location = e.target.value;
            currentState.page = 1;
            filterItems();
        });

        dateFilter.addEventListener('change', function (e) {
            currentState.date = e.target.value;
            currentState.page = 1;
            filterItems();
        });

        sortFilter.addEventListener('change', function (e) {
            currentState.sort = e.target.value;
            filterItems();
        });

        // Mobile filter functionality
        filterToggle.addEventListener('click', function () {
            mobileFilterPanel.classList.toggle('hidden');
        });

        mobileApplyFilters.addEventListener('click', function () {
            currentState.category = mobileCategoryFilter.value;
            currentState.location = mobileLocationFilter.value;
            currentState.date = mobileDateFilter.value;
            currentState.sort = mobileSortFilter.value;
            currentState.page = 1;
            mobileFilterPanel.classList.add('hidden');
            filterItems();
        });

        mobileClearFilters.addEventListener('click', function () {
            mobileCategoryFilter.value = '';
            mobileLocationFilter.value = '';
            mobileDateFilter.value = '';
            mobileSortFilter.value = 'newest';
        });

        // Clear filters
        clearFilters.addEventListener('click', clearAllFilters);
        resetFilters.addEventListener('click', clearAllFilters);

        // View toggle
        viewToggles.forEach(toggle => {
            toggle.addEventListener('click', function () {
                const view = this.getAttribute('data-view');
                setView(view);
            });
        });

        // Pagination
        paginationBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const page = this.getAttribute('data-page');
                handlePagination(page);
            });
        });

        // Items per page
        document.getElementById('items-per-page').addEventListener('change', function (e) {
            currentState.itemsPerPage = parseInt(e.target.value);
            currentState.page = 1;
            filterItems();
        });
    }

    function filterItems() {
        showLoading();

        // Simulate API call delay
        setTimeout(() => {
            const items = document.querySelectorAll('.item-card');
            let visibleItems = 0;

            items.forEach(item => {
                const matchesSearch = matchesSearchCriteria(item);
                const matchesCategory = matchesCategoryCriteria(item);
                const matchesLocation = matchesLocationCriteria(item);
                const matchesDate = matchesDateCriteria(item);

                if (matchesSearch && matchesCategory && matchesLocation && matchesDate) {
                    item.style.display = 'block';
                    visibleItems++;
                } else {
                    item.style.display = 'none';
                }
            });

            updateResults(visibleItems);
            updateActiveFilters();
            hideLoading();
        }, 500);
    }

    function matchesSearchCriteria(item) {
        if (!currentState.search) return true;

        const searchTerm = currentState.search.toLowerCase();
        const title = item.querySelector('h3').textContent.toLowerCase();
        const description = item.querySelector('p').textContent.toLowerCase();
        const location = item.querySelector('.fa-map-marker-alt').nextSibling.textContent.toLowerCase();

        return title.includes(searchTerm) ||
            description.includes(searchTerm) ||
            location.includes(searchTerm);
    }

    function matchesCategoryCriteria(item) {
        if (!currentState.category) return true;
        return item.getAttribute('data-category') === currentState.category;
    }

    function matchesLocationCriteria(item) {
        if (!currentState.location) return true;
        return item.getAttribute('data-location') === currentState.location;
    }

    function matchesDateCriteria(item) {
        if (!currentState.date) return true;

        const itemDate = new Date(item.getAttribute('data-date'));
        const now = new Date();

        switch (currentState.date) {
            case 'today':
                return itemDate.toDateString() === now.toDateString();
            case 'week':
                const weekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
                return itemDate >= weekAgo;
            case 'month':
                const monthAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
                return itemDate >= monthAgo;
            default:
                return true;
        }
    }

    function updateResults(visibleCount) {
        resultsCount.textContent = visibleCount;

        if (visibleCount === 0) {
            emptyState.classList.remove('hidden');
            itemsGrid.classList.add('hidden');
            resultsDescription.textContent = 'No items match your current filters';
        } else {
            emptyState.classList.add('hidden');
            itemsGrid.classList.remove('hidden');

            let description = `Showing ${visibleCount} lost items`;
            if (currentState.search) {
                description += ` matching "${currentState.search}"`;
            }
            if (currentState.category) {
                description += ` in ${getCategoryLabel(currentState.category)}`;
            }
            if (currentState.location) {
                description += ` at ${getLocationLabel(currentState.location)}`;
            }
            resultsDescription.textContent = description;
        }

        updatePagination(visibleCount);
    }

    function updateActiveFilters() {
        activeFilters.innerHTML = '';

        const filters = [];

        if (currentState.search) {
            filters.push({
                type: 'search',
                label: `Search: "${currentState.search}"`,
                value: currentState.search
            });
        }

        if (currentState.category) {
            filters.push({
                type: 'category',
                label: `Category: ${getCategoryLabel(currentState.category)}`,
                value: currentState.category
            });
        }

        if (currentState.location) {
            filters.push({
                type: 'location',
                label: `Location: ${getLocationLabel(currentState.location)}`,
                value: currentState.location
            });
        }

        if (currentState.date) {
            filters.push({
                type: 'date',
                label: `Date: ${getDateLabel(currentState.date)}`,
                value: currentState.date
            });
        }

        if (filters.length > 0) {
            activeFilters.classList.remove('hidden');

            filters.forEach(filter => {
                const filterElement = document.createElement('div');
                filterElement.className = 'flex items-center bg-uum-green/10 text-uum-green px-3 py-1 rounded-full text-sm';
                filterElement.innerHTML = `
                    <span>${filter.label}</span>
                    <button class="ml-2 text-uum-green hover:text-uum-blue remove-filter" data-type="${filter.type}">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                activeFilters.appendChild(filterElement);
            });

            // Add event listeners to remove buttons
            document.querySelectorAll('.remove-filter').forEach(btn => {
                btn.addEventListener('click', function () {
                    const type = this.getAttribute('data-type');
                    removeFilter(type);
                });
            });
        } else {
            activeFilters.classList.add('hidden');
        }
    }

    function removeFilter(type) {
        switch (type) {
            case 'search':
                currentState.search = '';
                searchInput.value = '';
                updateClearSearchButton();
                break;
            case 'category':
                currentState.category = '';
                categoryFilter.value = '';
                mobileCategoryFilter.value = '';
                break;
            case 'location':
                currentState.location = '';
                locationFilter.value = '';
                mobileLocationFilter.value = '';
                break;
            case 'date':
                currentState.date = '';
                dateFilter.value = '';
                mobileDateFilter.value = '';
                break;
        }

        currentState.page = 1;
        filterItems();
    }

    function clearAllFilters() {
        currentState.search = '';
        currentState.category = '';
        currentState.location = '';
        currentState.date = '';
        currentState.page = 1;

        searchInput.value = '';
        categoryFilter.value = '';
        locationFilter.value = '';
        dateFilter.value = '';
        sortFilter.value = 'newest';

        mobileCategoryFilter.value = '';
        mobileLocationFilter.value = '';
        mobileDateFilter.value = '';
        mobileSortFilter.value = 'newest';

        updateClearSearchButton();
        mobileFilterPanel.classList.add('hidden');
        filterItems();
    }

    function setView(view) {
        currentState.view = view;

        viewToggles.forEach(toggle => {
            if (toggle.getAttribute('data-view') === view) {
                toggle.classList.add('active', 'bg-white', 'dark:bg-gray-600', 'shadow-sm', 'text-gray-700', 'dark:text-gray-300');
                toggle.classList.remove('text-gray-500', 'dark:text-gray-400');
            } else {
                toggle.classList.remove('active', 'bg-white', 'dark:bg-gray-600', 'shadow-sm', 'text-gray-700', 'dark:text-gray-300');
                toggle.classList.add('text-gray-500', 'dark:text-gray-400');
            }
        });

        if (view === 'grid') {
            itemsGrid.classList.remove('grid-cols-1');
            itemsGrid.classList.add('grid-cols-1', 'sm:grid-cols-2', 'lg:grid-cols-3', 'xl:grid-cols-4');
        } else {
            itemsGrid.classList.remove('sm:grid-cols-2', 'lg:grid-cols-3', 'xl:grid-cols-4');
            itemsGrid.classList.add('grid-cols-1');

            // Add list view specific classes to items
            document.querySelectorAll('.item-card').forEach(card => {
                if (view === 'list') {
                    card.classList.add('flex');
                    card.classList.remove('flex-col');
                } else {
                    card.classList.remove('flex');
                    card.classList.add('flex-col');
                }
            });
        }
    }

    function handlePagination(page) {
        if (page === 'prev') {
            if (currentState.page > 1) {
                currentState.page--;
            }
        } else if (page === 'next') {
            currentState.page++;
        } else {
            currentState.page = parseInt(page);
        }

        filterItems();
    }

    function updatePagination(totalItems) {
        const totalPages = Math.ceil(totalItems / currentState.itemsPerPage);
        const startItem = (currentState.page - 1) * currentState.itemsPerPage + 1;
        const endItem = Math.min(currentState.page * currentState.itemsPerPage, totalItems);

        document.getElementById('pagination-start').textContent = startItem;
        document.getElementById('pagination-end').textContent = endItem;
        document.getElementById('pagination-total').textContent = totalItems;

        // Update pagination buttons (simplified for this example)
        // In a real implementation, you would generate pagination buttons dynamically
    }

    function updateClearSearchButton() {
        if (currentState.search) {
            clearSearch.classList.remove('hidden');
        } else {
            clearSearch.classList.add('hidden');
        }
    }

    function showLoading() {
        loadingState.classList.remove('hidden');
        itemsGrid.classList.add('hidden');
        emptyState.classList.add('hidden');
    }

    function hideLoading() {
        loadingState.classList.add('hidden');
    }

    function getCategoryLabel(category) {
        const labels = {
            'electronics': 'Electronics',
            'books': 'Books & Notes',
            'clothing': 'Clothing',
            'accessories': 'Accessories',
            'keys': 'Keys & IDs',
            'bags': 'Bags & Wallets',
            'other': 'Other'
        };
        return labels[category] || category;
    }

    function getLocationLabel(location) {
        const labels = {
            'library': 'Main Library',
            'edc': 'EDC Building',
            'student-center': 'Student Center',
            'cafeteria': 'Cafeteria',
            'sports-complex': 'Sports Complex',
            'residential': 'Residential Colleges'
        };
        return labels[location] || location;
    }

    function getDateLabel(date) {
        const labels = {
            'today': 'Today',
            'week': 'This Week',
            'month': 'This Month'
        };
        return labels[date] || date;
    }

    // Utility function for debouncing
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
});