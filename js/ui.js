/**
 * UI.js - Filtreler, arama, sayfalama ve liste görüntüleme işlemleri
 */

/**
 * Filtre seçeneklerini doldurur
 */
function populateFilterOptions() {
  if (!allListings || !Array.isArray(allListings)) return;
  
  const brandOptions = document.getElementById('brandOptions');
  const locationOptions = document.getElementById('locationOptions');
  
  // my-listings sayfasında bu elementler mevcut değil
  if (!brandOptions || !locationOptions) return;
  
  const brands = [...new Set(allListings.map(listing => listing.brand))].sort();
  brandOptions.innerHTML = '<div class="dropdown-option multi-select selected" data-value="all">Tüm Markalar</div>';
  brands.forEach(brand => {
    const option = document.createElement('div');
    option.className = 'dropdown-option multi-select';
    option.dataset.value = brand;
    option.textContent = brand;
    brandOptions.appendChild(option);
  });

  const locations = [...new Set(allListings.map(listing => listing.location))].sort();
  locationOptions.innerHTML = '<div class="dropdown-option multi-select selected" data-value="all">Tüm Konumlar</div>';
  locations.forEach(location => {
    const option = document.createElement('div');
    option.className = 'dropdown-option multi-select';
    option.dataset.value = location;
    option.textContent = location;
    locationOptions.appendChild(option);
  });
}

/**
 * Dropdown menüleri ayarlar
 */
function setupDropdowns() {
  setupSingleSelectDropdown('category', (value) => {
    currentFilter = value;
    currentPage = 1;
    applyFiltersAndRender();
  });

  setupSingleSelectDropdown('condition', (value) => {
    currentConditionFilter = value;
    currentPage = 1;
    applyFiltersAndRender();
  });

  setupSingleSelectDropdown('price', (value) => {
    currentPriceRangeFilter = value;
    currentPage = 1;
    applyFiltersAndRender();
  });

  setupSingleSelectDropdown('sort', (value) => {
    currentSort = value;
    currentPage = 1;
    applyFiltersAndRender();
  });

  setupMultiSelectDropdown('brand', (values) => {
    currentBrandFilter = values;
    currentPage = 1;
    applyFiltersAndRender();
  });

  setupMultiSelectDropdown('location', (values) => {
    currentLocationFilter = values;
    currentPage = 1;
    applyFiltersAndRender();
  });

  const brandSearchInput = document.getElementById('brandSearchInput');
  if (brandSearchInput) {
    brandSearchInput.addEventListener('input', (e) => {
      const searchTerm = e.target.value.toLowerCase().trim();
      const options = document.querySelectorAll('#brandOptions .dropdown-option');
      options.forEach(option => {
        if (option.dataset.value === 'all') return;
        const text = option.textContent.toLowerCase();
        option.classList.toggle('hidden', !text.includes(searchTerm));
      });
    });
    brandSearchInput.addEventListener('click', (e) => e.stopPropagation());
  }

  const locationSearchInput = document.getElementById('locationSearchInput');
  if (locationSearchInput) {
    locationSearchInput.addEventListener('input', (e) => {
      const searchTerm = e.target.value.toLowerCase().trim();
      const options = document.querySelectorAll('#locationOptions .dropdown-option');
      options.forEach(option => {
        if (option.dataset.value === 'all') return;
        const text = option.textContent.toLowerCase();
        option.classList.toggle('hidden', !text.includes(searchTerm));
      });
    });
    locationSearchInput.addEventListener('click', (e) => e.stopPropagation());
  }

  document.addEventListener('click', (e) => {
    document.querySelectorAll('.dropdown-filter.open').forEach(dropdown => {
      if (!dropdown.contains(e.target)) {
        dropdown.classList.remove('open');
        const searchInput = dropdown.querySelector('.dropdown-search input');
        if (searchInput) {
          searchInput.value = '';
          dropdown.querySelectorAll('.dropdown-option').forEach(opt => opt.classList.remove('hidden'));
        }
      }
    });
  });
}

/**
 * Tekli seçim dropdown ayarları
 */
function setupSingleSelectDropdown(type, callback) {
  const dropdown = document.getElementById(`${type}Dropdown`);
  const button = document.getElementById(`${type}Button`);
  const options = document.getElementById(`${type}Options`);
  const buttonText = document.getElementById(`${type}ButtonText`);

  // my-listings sayfasında bu elementler mevcut değil
  if (!dropdown || !button || !options || !buttonText) return;

  button.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = dropdown.classList.contains('open');
    document.querySelectorAll('.dropdown-filter.open').forEach(d => d.classList.remove('open'));
    if (!isOpen) dropdown.classList.add('open');
  });

  options.addEventListener('click', (e) => {
    if (e.target.classList.contains('dropdown-option')) {
      const value = e.target.dataset.value;
      const clickedOption = e.target;
      
      options.querySelectorAll('.dropdown-option').forEach(opt => opt.classList.remove('selected'));
      clickedOption.classList.add('selected');
      
      buttonText.textContent = clickedOption.textContent;
      dropdown.classList.remove('open');
      callback(value);
    }
  });
}

/**
 * Çoklu seçim dropdown ayarları
 */
function setupMultiSelectDropdown(type, callback) {
  const dropdown = document.getElementById(`${type}Dropdown`);
  const button = document.getElementById(`${type}Button`);
  const options = document.getElementById(`${type}Options`);
  const buttonText = document.getElementById(`${type}ButtonText`);
  let selectedValues = [];

  // my-listings sayfasında bu elementler mevcut değil
  if (!dropdown || !button || !options || !buttonText) return;

  button.addEventListener('click', (e) => {
    e.stopPropagation();
    const isOpen = dropdown.classList.contains('open');
    document.querySelectorAll('.dropdown-filter.open').forEach(d => d.classList.remove('open'));
    if (!isOpen) dropdown.classList.add('open');
  });

  options.addEventListener('click', (e) => {
    if (e.target.classList.contains('dropdown-option')) {
      const value = e.target.dataset.value;
      
      if (value === 'all') {
        selectedValues = [];
        options.querySelectorAll('.dropdown-option').forEach(opt => {
          opt.classList.toggle('selected', opt.dataset.value === 'all');
        });
        buttonText.textContent = e.target.textContent;
      } else {
        const allOption = options.querySelector('.dropdown-option[data-value="all"]');
        allOption.classList.remove('selected');
        
        if (selectedValues.includes(value)) {
          selectedValues = selectedValues.filter(v => v !== value);
          e.target.classList.remove('selected');
        } else {
          selectedValues.push(value);
          e.target.classList.add('selected');
        }
        
        if (selectedValues.length === 0) {
          allOption.classList.add('selected');
          buttonText.textContent = allOption.textContent;
        } else if (selectedValues.length === 1) {
          buttonText.textContent = selectedValues[0];
        } else {
          buttonText.textContent = `${selectedValues.length} ${type === 'brand' ? 'Marka' : 'Konum'} Seçili`;
        }
      }
      
      callback(selectedValues);
    }
  });
}

/**
 * Arama işleyicisi
 */
function handleSearch(e) {
  currentSearch = e.target.value.toLowerCase().trim();
  currentPage = 1;
  applyFiltersAndRender();
}

/**
 * Filtreleri uygular ve render eder
 */
function applyFiltersAndRender() {
  let filtered = allListings;

  if (currentFilter !== 'all') {
    filtered = filtered.filter(listing => listing.category === currentFilter);
  }

  if (currentConditionFilter !== 'all') {
    filtered = filtered.filter(listing => listing.condition === currentConditionFilter);
  }

  if (currentBrandFilter.length > 0) {
    filtered = filtered.filter(listing => currentBrandFilter.includes(listing.brand));
  }

  if (currentLocationFilter.length > 0) {
    filtered = filtered.filter(listing => currentLocationFilter.includes(listing.location));
  }

  if (currentPriceRangeFilter !== 'all') {
    const [min, max] = currentPriceRangeFilter.split('-').map(Number);
    filtered = filtered.filter(listing => {
      const priceInTRY = convertToTRY(listing.price, listing.currency, listing);
      return priceInTRY >= min && priceInTRY <= max;
    });
  }

  if (currentSearch) {
    filtered = filtered.filter(listing => {
      const searchableText = `${listing.title} ${listing.callsign} ${listing.description}`.toLowerCase();
      return searchableText.includes(currentSearch);
    });
  }

  // Sıralama işlemini uygula
  filtered = sortListings(filtered, currentSort);

  displayedListings = filtered;
  renderListings();
}

/**
 * Sıralama fonksiyonu
 */
function sortListings(listings, sortType) {
  const sorted = [...listings];
  
  switch (sortType) {
    case 'newest':
      // Yeniden eskiye - created_at DESC
      sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      break;
      
    case 'oldest':
      // Eskiden yeniye - created_at ASC
      sorted.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
      break;
      
    case 'price_asc':
      // Ucuzdan pahalıya - price ASC (TRY bazında)
      sorted.sort((a, b) => {
        const priceA = convertToTRY(a.price, a.currency, a);
        const priceB = convertToTRY(b.price, b.currency, b);
        return priceA - priceB;
      });
      break;
      
    case 'price_desc':
      // Pahalıdan ucuza - price DESC (TRY bazında)
      sorted.sort((a, b) => {
        const priceA = convertToTRY(a.price, a.currency, a);
        const priceB = convertToTRY(b.price, b.currency, b);
        return priceB - priceA;
      });
      break;
      
    case 'title_asc':
      // A'dan Z'ye - title ASC
      sorted.sort((a, b) => a.title.localeCompare(b.title, 'tr'));
      break;
      
    case 'title_desc':
      // Z'den A'ya - title DESC
      sorted.sort((a, b) => b.title.localeCompare(a.title, 'tr'));
      break;
      
    default:
      // Varsayılan: yeniden eskiye
      sorted.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  }
  
  return sorted;
}

/**
 * İlanları render eder
 */
function renderListings() {
  const grid = document.getElementById('listingsGrid');
  const noResults = document.getElementById('noResults');
  const paginationContainer = document.getElementById('paginationContainer');

  // my-listings sayfasında bu elementler mevcut değil
  if (!grid || !noResults || !paginationContainer) return;

  console.log('[DEBUG] renderListings çağrıldı. Toplam ilan:', displayedListings.length);

  // Toplam sayfa sayısını hesapla
  totalPages = Math.ceil(displayedListings.length / itemsPerPage);
  
  // Eğer mevcut sayfa toplam sayfadan büyükse, son sayfaya git
  if (currentPage > totalPages && totalPages > 0) {
    currentPage = totalPages;
  }
  
  // Hangi ilanların gösterileceğini hesapla
  const startIndex = (currentPage - 1) * itemsPerPage;
  const endIndex = startIndex + itemsPerPage;
  const listingsToShow = displayedListings.slice(startIndex, endIndex);

  if (displayedListings.length === 0) {
    grid.innerHTML = '';
    paginationContainer.innerHTML = '';
    noResults.style.display = 'block';
    // Mesajı güncelle - filtrelere mi yoksa gerçekten ilan yok mu?
    if (allListings.length === 0) {
      noResults.textContent = 'Henüz onaylanmış ilan bulunmamaktadır. İlk ilanı siz ekleyin!';
    } else {
      noResults.textContent = 'Sonuç bulunamadı. Lütfen farklı bir arama veya filtre deneyin.';
    }
    return;
  }

  noResults.style.display = 'none';
  grid.innerHTML = '';

  listingsToShow.forEach(listing => {
    const card = createListingCard(listing);
    grid.appendChild(card);
  });

  // Sayfa navigasyonunu oluştur
  renderPagination();
}

/**
 * Sayfalama render eder
 */
function renderPagination() {
  const paginationContainer = document.getElementById('paginationContainer');
  
  if (totalPages <= 1) {
    paginationContainer.innerHTML = '';
    return;
  }

  let paginationHTML = '<div class="pagination">';
  
  // Önceki sayfa butonu
  if (currentPage > 1) {
    paginationHTML += `<button class="pagination-btn prev-next" onclick="changePage(${currentPage - 1})">‹ Önceki</button>`;
  } else {
    paginationHTML += `<button class="pagination-btn prev-next disabled" disabled>‹ Önceki</button>`;
  }

  // Sayfa numaraları
  const maxVisiblePages = 5;
  let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
  let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

  // Eğer başlangıç ve bitiş sayfaları arasında yeterli sayı yoksa, ayarla
  if (endPage - startPage + 1 < maxVisiblePages) {
    startPage = Math.max(1, endPage - maxVisiblePages + 1);
  }

  // İlk sayfa ve üç nokta
  if (startPage > 1) {
    paginationHTML += `<button class="pagination-btn" onclick="changePage(1)">1</button>`;
    if (startPage > 2) {
      paginationHTML += `<span class="pagination-dots">...</span>`;
    }
  }

  // Sayfa numaraları
  for (let i = startPage; i <= endPage; i++) {
    if (i === currentPage) {
      paginationHTML += `<button class="pagination-btn active">${i}</button>`;
    } else {
      paginationHTML += `<button class="pagination-btn" onclick="changePage(${i})">${i}</button>`;
    }
  }

  // Son sayfa ve üç nokta
  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      paginationHTML += `<span class="pagination-dots">...</span>`;
    }
    paginationHTML += `<button class="pagination-btn" onclick="changePage(${totalPages})">${totalPages}</button>`;
  }

  // Sonraki sayfa butonu
  if (currentPage < totalPages) {
    paginationHTML += `<button class="pagination-btn prev-next" onclick="changePage(${currentPage + 1})">Sonraki ›</button>`;
  } else {
    paginationHTML += `<button class="pagination-btn prev-next disabled" disabled>Sonraki ›</button>`;
  }

  paginationHTML += '</div>';
  
  paginationContainer.innerHTML = paginationHTML;
}

/**
 * Sayfa değiştirme fonksiyonu
 */
window.changePage = function(page) {
  currentPage = page;
  renderListings();
  // Sayfanın başına kaydır
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

/**
 * İlan kartı oluşturur
 */
function createListingCard(listing) {
    const card = document.createElement('div');
    card.className = 'listing-card';
    card.onclick = () => openDetailPanel(listing);

    let displayImage;
    let imageCountBadge = '';
    
    if (listing.images && listing.images.length > 0) {
        const featuredIndex = listing.featured_image_index || 0;
        displayImage = `<img src="${escapeHtml(listing.images[featuredIndex].data)}" alt="${escapeHtml(listing.title)}">`;
        if (listing.images.length > 1) {
            imageCountBadge = `<div class="image-count-badge">${listing.images.length} 📷</div>`;
        }
    } else {
        displayImage = escapeHtml(listing.emoji || '📻');
    }

    const currencySymbol = getCurrencySymbol(listing.currency || 'TRY');

    // İndirim badge'i - old_price varsa göster
    let discountBadge = '';
    if (listing.old_price && listing.discount_percent > 0) {
        discountBadge = `<div class="discount-badge">%${listing.discount_percent} İndirim</div>`;
    }

    // Fiyat gösterimi - old_price varsa üstü çizili göster
    let priceHtml = '';
    if (listing.old_price && listing.discount_percent > 0) {
        priceHtml = `
            <p class="listing-price">
                <span class="old-price">${currencySymbol}${escapeHtml(String(listing.old_price))}</span>
                <span class="new-price">${currencySymbol}${escapeHtml(String(listing.price))} ${escapeHtml(listing.currency || 'TRY')}</span>
            </p>
        `;
    } else {
        priceHtml = `<p class="listing-price">${currencySymbol}${escapeHtml(String(listing.price))} ${escapeHtml(listing.currency || 'TRY')}</p>`;
    }

    // Kullanıcı kontrolü - user_id'ye göre
    const isMyListing = ativ_ajax.user_id && listing.user_id == ativ_ajax.user_id;

    card.innerHTML = `
        ${isMyListing ? '<div class="my-listings-badge">Benim İlanım</div>' : ''}
        ${discountBadge}
        ${imageCountBadge}
        ${isMyListing && pageType === 'my-listings' ? `
          <div class="listing-actions">
            <button class="action-btn edit-btn" onclick="event.stopPropagation(); editListing(${parseInt(listing.id)})" title="Düzenle" aria-label="İlanı düzenle">✏️</button>
            <button class="action-btn delete-btn" onclick="event.stopPropagation(); confirmDeleteListing(${parseInt(listing.id)})" title="Sil" aria-label="İlanı sil">🗑️</button>
          </div>
        ` : ''}
        <div class="listing-image">${displayImage}</div>
        <div class="listing-content">
          <h3 class="listing-title">${escapeHtml(listing.title)}</h3>
          <p class="listing-brand-model">${escapeHtml(listing.brand)} ${escapeHtml(listing.model)}</p>
          <p class="listing-callsign">${escapeHtml(listing.callsign)}</p>
          ${priceHtml}
        </div>
      `;

    return card;
}

/**
 * Toggle accordion detail section for a listing row
 * @param {HTMLElement} titleElement - The h3.listing-row-title element that was clicked
 */
function toggleListingDetails(titleElement) {
  if (!titleElement) return;

  // Find the parent listing-row
  const listingRow = titleElement.closest('.listing-row');
  if (!listingRow) return;

  // Find the parent wrapper
  const wrapper = listingRow.closest('.listing-row-wrapper');
  if (!wrapper) return;

  // Find all accordion sections within this wrapper
  const detailsElement = wrapper.querySelector('.listing-row-details-expanded');
  if (!detailsElement) return;

  // Close any other open accordion sections
  document.querySelectorAll('.listing-row-details-expanded.expanded').forEach(element => {
    if (element !== detailsElement) {
      element.classList.remove('expanded');
    }
  });

  // Toggle current accordion
  detailsElement.classList.toggle('expanded');
}

// Global window ataması - inline onclick handler'lar için gerekli
window.toggleListingDetails = toggleListingDetails;
