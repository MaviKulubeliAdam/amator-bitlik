/**
 * Core.js - Temel yapılandırma, global değişkenler ve API işlemleri
 */

const defaultConfig = {
  header_title: "Amatör Telsiz İlan Vitrini",
  header_subtitle: "Kaliteli ekipmanları keşfedin",
  search_placeholder: "İlan ara... (başlık, çağrı işareti, açıklama)",
  load_more_text: "Daha Fazla Yükle",
  no_results_text: "Sonuç bulunamadı. Lütfen farklı bir arama deneyin.",
  primary_color: "#667eea",
  secondary_color: "#f8f9fa",
  surface_color: "#ffffff",
  text_color: "#1a1a1a",
  accent_color: "#667eea"
};

// Global değişkenler
let allListings = [];
let displayedListings = [];
let currentFilter = 'all';
let currentConditionFilter = 'all';
let currentBrandFilter = [];
let currentLocationFilter = [];
let currentPriceRangeFilter = 'all';
let currentSort = 'newest'; // Varsayılan sıralama
let currentSearch = '';
let itemsPerPage = 24; // Sayfa başına 24 ilan
let currentPage = 1; // Sayfa numarası (1'den başlar)
let totalPages = 1; // Toplam sayfa sayısı
let selectedListing = null;
let editingListing = null;
let isEditingRejectedListing = false; // Red edilen ilan düzenleniyorsa
let userCallsign = localStorage.getItem('userCallsign') || null;

let uploadedImages = [];
let featuredImageIndex = 0;

// Lightbox değişkenleri
let currentLightboxSlide = 0;
let isLightboxOpen = false;
let currentImages = [];
let lightboxSource = ''; // 'detail' veya 'gallery'

// Slider değişkenleri
let currentSlide = 0;

// Sayfa türü: 'gallery' veya 'my-listings'
let pageType = 'gallery';

/**
 * Uygulama başlatma fonksiyonu
 */
async function initApp() {
  await loadListings();
  populateFilterOptions();
  applyFiltersAndRender();

  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('input', handleSearch);
  }

  setupDropdowns();
  setupModal();
  setupForm();
}

/**
 * API çağrısı yapan ana fonksiyon
 * @param {string} action - İşlem türü
 * @param {Object} data - Gönderilecek veri
 * @returns {Promise} API yanıtı
 */
async function apiCall(action, data = null) {
  const formData = new FormData();
  formData.append('action', 'ativ_ajax');
  
  // İşlem türüne göre doğru nonce'u kullan
  const critical_actions = ['save_listing', 'update_listing', 'delete_listing'];
  const public_actions = ['get_listings', 'get_brands', 'get_locations'];
  
  if (critical_actions.includes(action) && ativ_ajax.is_user_logged_in) {
    formData.append('nonce', ativ_ajax.nonce);
  } else if (public_actions.includes(action)) {
    formData.append('nonce', ativ_ajax.public_nonce);
  } else {
    formData.append('nonce', ativ_ajax.nonce);
  }
  
  formData.append('action_type', action);

  if (data) {
    for (const key in data) {
      if (data[key] !== null && data[key] !== undefined) {
        if (typeof data[key] === 'object' && !(data[key] instanceof File)) {
          formData.append(key, JSON.stringify(data[key]));
        } else {
          formData.append(key, data[key]);
        }
      }
    }
  }

  try {
    const response = await fetch(ativ_ajax.url, {
      method: 'POST',
      body: formData
    });
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    const result = await response.json();
    
    if (!result.success) {
      throw new Error(result.data || 'İşlem başarısız');
    }
    
    return result;
  } catch (error) {
    console.error('API call failed:', error);
    throw error;
  }
}

/**
 * İlanları yükler
 */
async function loadListings() {
  try {
    const response = await apiCall('get_listings');
    allListings = response.data || [];
    // Listeler yüklendikten sonra sıralamayı uygula
    allListings = sortListings(allListings, currentSort);
  } catch (error) {
    console.error('Listeler yüklenirken hata oluştu:', error);
    allListings = [];
  }
}

/**
 * Yeni ilan kaydeder
 * @param {Object} listingData - İlan verisi
 */
async function saveListing(listingData) {
  const response = await apiCall('save_listing', listingData);
  return response;
}

/**
 * Mevcut ilanı günceller
 * @param {number} id - İlan ID'si
 * @param {Object} listingData - İlan verisi
 */
async function updateListing(id, listingData) {
  listingData.id = id;
  const response = await apiCall('update_listing', listingData);
  return response;
}

/**
 * İlanı siler
 * @param {number} id - İlan ID'si
 */
async function deleteListing(id) {
  const response = await apiCall('delete_listing', { id: id });
  return response;
}

/**
 * Kategori adını döndürür
 * @param {string} category - Kategori kodu
 */
function getCategoryName(category) {
  const categories = {
    'transceiver': 'Telsiz',
    'antenna': 'Anten',
    'amplifier': 'Amplifikatör',
    'accessory': 'Aksesuar',
    'other': 'Diğer'
  };
  return categories[category] || category;
}

/**
 * Para birimi sembolünü döndürür
 * @param {string} currency - Para birimi kodu
 */
function getCurrencySymbol(currency) {
  const symbols = {
    'TRY': '₺',
    'USD': '$',
    'EUR': '€'
  };
  return symbols[currency] || '';
}

/**
 * Fiyatı TRY'ye çevirir
 * @param {number} price - Fiyat
 * @param {string} currency - Para birimi
 */
function convertToTRY(price, currency, listing) {
  // Backend'ten gelen price_in_tl alanı varsa onu kullan
  if (listing && listing.price_in_tl) {
    return listing.price_in_tl;
  }
  
  if (currency === 'TRY') return price;
  // USD ve EUR için yaklaşık dönüşüm oranı (backend tarafından da kullanılıyor)
  return price * 30;
}

/**
 * E-posta doğrulama
 * @param {string} email - E-posta adresi
 */
function isValidEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// DOMContentLoaded event listener
document.addEventListener('DOMContentLoaded', initApp);
