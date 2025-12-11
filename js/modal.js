/**
 * Modal.js - Modal, form, detay paneli ve görsel işlemleri
 */

const MAX_TITLE_LENGTH = 42;

/**
 * Ülke telefon kodları ve bayrakları
 */
const countryCodes = [
  { code: 'TR', flag: '🇹🇷', dialCode: '+90', name: 'Türkiye' },
  { code: 'US', flag: '🇺🇸', dialCode: '+1', name: 'Amerika Birleşik Devletleri' },
  { code: 'GB', flag: '🇬🇧', dialCode: '+44', name: 'Birleşik Krallık' },
  { code: 'DE', flag: '🇩🇪', dialCode: '+49', name: 'Almanya' },
  { code: 'FR', flag: '🇫🇷', dialCode: '+33', name: 'Fransa' },
  { code: 'IT', flag: '🇮🇹', dialCode: '+39', name: 'İtalya' },
  { code: 'ES', flag: '🇪🇸', dialCode: '+34', name: 'İspanya' },
  { code: 'NL', flag: '🇳🇱', dialCode: '+31', name: 'Hollanda' },
  { code: 'BE', flag: '🇧🇪', dialCode: '+32', name: 'Belçika' },
  { code: 'AT', flag: '🇦🇹', dialCode: '+43', name: 'Avusturya' },
  { code: 'CH', flag: '🇨🇭', dialCode: '+41', name: 'İsviçre' },
  { code: 'SE', flag: '🇸🇪', dialCode: '+46', name: 'İsveç' },
  { code: 'NO', flag: '🇳🇴', dialCode: '+47', name: 'Norveç' },
  { code: 'DK', flag: '🇩🇰', dialCode: '+45', name: 'Danimarka' },
  { code: 'FI', flag: '🇫🇮', dialCode: '+358', name: 'Finlandiya' },
  { code: 'GR', flag: '🇬🇷', dialCode: '+30', name: 'Yunanistan' },
  { code: 'PL', flag: '🇵🇱', dialCode: '+48', name: 'Polonya' },
  { code: 'CZ', flag: '🇨🇿', dialCode: '+420', name: 'Çekya' },
  { code: 'HU', flag: '🇭🇺', dialCode: '+36', name: 'Macaristan' },
  { code: 'RO', flag: '🇷🇴', dialCode: '+40', name: 'Romanya' },
  { code: 'BG', flag: '🇧🇬', dialCode: '+359', name: 'Bulgaristan' },
  { code: 'HR', flag: '🇭🇷', dialCode: '+385', name: 'Hırvatistan' },
  { code: 'RS', flag: '🇷🇸', dialCode: '+381', name: 'Sırbistan' },
  { code: 'UA', flag: '🇺🇦', dialCode: '+380', name: 'Ukrayna' },
  { code: 'RU', flag: '🇷🇺', dialCode: '+7', name: 'Rusya' },
  { code: 'JP', flag: '🇯🇵', dialCode: '+81', name: 'Japonya' },
  { code: 'CN', flag: '🇨🇳', dialCode: '+86', name: 'Çin' },
  { code: 'KR', flag: '🇰🇷', dialCode: '+82', name: 'Güney Kore' },
  { code: 'IN', flag: '🇮🇳', dialCode: '+91', name: 'Hindistan' },
  { code: 'AU', flag: '🇦🇺', dialCode: '+61', name: 'Avustralya' },
  { code: 'NZ', flag: '🇳🇿', dialCode: '+64', name: 'Yeni Zelanda' },
  { code: 'CA', flag: '🇨🇦', dialCode: '+1', name: 'Kanada' },
  { code: 'MX', flag: '🇲🇽', dialCode: '+52', name: 'Meksika' },
  { code: 'BR', flag: '🇧🇷', dialCode: '+55', name: 'Brezilya' },
  { code: 'AR', flag: '🇦🇷', dialCode: '+54', name: 'Arjantin' },
  { code: 'CL', flag: '🇨🇱', dialCode: '+56', name: 'Şili' },
  { code: 'ZA', flag: '🇿🇦', dialCode: '+27', name: 'Güney Afrika' },
  { code: 'EG', flag: '🇪🇬', dialCode: '+20', name: 'Mısır' },
  { code: 'SA', flag: '🇸🇦', dialCode: '+966', name: 'Suudi Arabistan' },
  { code: 'AE', flag: '🇦🇪', dialCode: '+971', name: 'Birleşik Arap Emirlikleri' },
  { code: 'IL', flag: '🇮🇱', dialCode: '+972', name: 'İsrail' },
  { code: 'IQ', flag: '🇮🇶', dialCode: '+964', name: 'Irak' },
  { code: 'IR', flag: '🇮🇷', dialCode: '+98', name: 'İran' },
  { code: 'PK', flag: '🇵🇰', dialCode: '+92', name: 'Pakistan' },
  { code: 'BD', flag: '🇧🇩', dialCode: '+880', name: 'Bangladeş' },
  { code: 'TH', flag: '🇹🇭', dialCode: '+66', name: 'Tayland' },
  { code: 'VN', flag: '🇻🇳', dialCode: '+84', name: 'Vietnam' },
  { code: 'ID', flag: '🇮🇩', dialCode: '+62', name: 'Endonezya' },
  { code: 'MY', flag: '🇲🇾', dialCode: '+60', name: 'Malezya' },
  { code: 'SG', flag: '🇸🇬', dialCode: '+65', name: 'Singapur' },
  { code: 'PH', flag: '🇵🇭', dialCode: '+63', name: 'Filipinler' }
];

/**
 * Ülke kodları dropdown'ını doldurur
 */
function populateCountryCodes(selectedDialCode = '+90') {
  const select = document.getElementById('formCountryCode');
  if (!select) return;
  
  select.innerHTML = '';
  countryCodes.forEach(country => {
    const option = document.createElement('option');
    option.value = country.dialCode;
    option.textContent = `${country.flag} ${country.dialCode}`;
    option.setAttribute('data-name', country.name);
    if (country.dialCode === selectedDialCode) {
      option.selected = true;
    }
    select.appendChild(option);
  });
}

/**
 * Telefon numarasını formatlar (detay gösterimi için: +90 548 222 99 89)
 */
function formatPhoneForDisplay(phone) {
  if (!phone) return '';
  
  // Parse phone to get country code and number
  const phoneData = parsePhoneNumber(phone);
  const dialCode = phoneData.dialCode;
  const digits = phoneData.number.replace(/\D/g, '');
  
  // Format the number part (e.g., 5482229989 -> 548 222 99 89)
  let formatted = '';
  if (digits.length <= 3) {
    formatted = digits;
  } else if (digits.length <= 6) {
    formatted = digits.substring(0, 3) + ' ' + digits.substring(3);
  } else if (digits.length <= 8) {
    formatted = digits.substring(0, 3) + ' ' + digits.substring(3, 6) + ' ' + digits.substring(6);
  } else {
    formatted = digits.substring(0, 3) + ' ' + digits.substring(3, 6) + ' ' + digits.substring(6, 8) + ' ' + digits.substring(8);
  }
  
  return dialCode + ' ' + formatted;
}

/**
 * Telefon numarasını formatlar (555 123 4567)
 */
function formatPhoneNumber(value) {
  // Sadece rakamları al
  const digits = value.replace(/\D/g, '');
  
  // Başta 0 varsa kaldır
  const cleanDigits = digits.replace(/^0+/, '');
  
  // En fazla 10 rakam
  const limited = cleanDigits.substring(0, 10);
  
  // 555 123 4567 formatı
  if (limited.length <= 3) {
    return limited;
  } else if (limited.length <= 6) {
    return limited.substring(0, 3) + ' ' + limited.substring(3);
  } else {
    return limited.substring(0, 3) + ' ' + limited.substring(3, 6) + ' ' + limited.substring(6);
  }
}

/**
 * Telefon numarasını parse eder (ülke kodu ve numara)
 */
function parsePhoneNumber(fullPhone) {
  if (!fullPhone) return { dialCode: '+90', number: '' };
  
  // Boşlukları temizle
  const cleaned = fullPhone.trim();
  
  // + ile başlıyorsa ülke kodunu ayır
  const match = cleaned.match(/^(\+\d+)\s*(.*)$/);
  if (match) {
    return {
      dialCode: match[1],
      number: match[2].replace(/\D/g, '') // Numaradaki tüm boşluk ve karakterleri temizle
    };
  }
  
  // Ülke kodu yoksa Türkiye varsayılan
  return {
    dialCode: '+90',
    number: cleaned.replace(/\D/g, '').replace(/^0+/, '') // Başındaki 0'ları da kaldır
  };
}

/**
 * Modal ayarlarını yapar
 */
function setupModal() {
  const addBtn = document.getElementById('addListingBtn');
  const closeBtn = document.getElementById('modalCloseBtn');
  const cancelBtn = document.getElementById('formCancelBtn');
  const modal = document.getElementById('addListingModal');
  
  if (addBtn) addBtn.addEventListener('click', openAddListingModal);
  if (closeBtn) closeBtn.addEventListener('click', closeAddListingModal);
  if (cancelBtn) cancelBtn.addEventListener('click', closeAddListingModal);
  if (modal) {
    modal.addEventListener('click', (e) => {
      if (e.target.id === 'addListingModal') closeAddListingModal();
    });
  }
  
  // Telefon numarası formatlaması
  const phoneInput = document.getElementById('formPhone');
  if (phoneInput) {
    phoneInput.addEventListener('input', (e) => {
      const formatted = formatPhoneNumber(e.target.value);
      e.target.value = formatted;
    });
  }
  
  // Ülke kodlarını başlangıçta doldur
  const countryCodeSelect = document.getElementById('formCountryCode');
  if (countryCodeSelect) {
    populateCountryCodes('+90');
  }
  
  // Login Required Modal ayarları
  setupLoginRequiredModal();
  
  // My-listings sayfasındaki custom dropdown filtreleri ayarla
  setupMyListingsDropdowns();
}

/**
 * My-listings dropdown'ları ayarla
 */
function setupMyListingsDropdowns() {
  // Fiyat sıralama dropdown (ucuz-pahalı)
  const priceSortButton = document.getElementById('priceSortButton');
  const priceSortMenu = document.getElementById('priceSortMenu');
  const priceSortOptions = document.querySelectorAll('#priceSortOptions .dropdown-option');
  
  // Durum dropdown
  const statusFilterButton = document.getElementById('statusFilterButton');
  const statusFilterMenu = document.getElementById('statusFilterMenu');
  const statusFilterOptions = document.querySelectorAll('#statusFilterOptions .dropdown-option');
  
  // Tarih dropdown
  const dateSortButton = document.getElementById('dateSortButton');
  const dateSortMenu = document.getElementById('dateSortMenu');
  const dateSortOptions = document.querySelectorAll('#dateSortOptions .dropdown-option');
  
  if (priceSortButton && priceSortMenu) {
    priceSortButton.addEventListener('click', (e) => {
      e.stopPropagation();
      priceSortMenu.classList.toggle('active');
      if (statusFilterMenu) statusFilterMenu.classList.remove('active');
      if (dateSortMenu) dateSortMenu.classList.remove('active');
    });
    
    priceSortOptions.forEach(option => {
      option.addEventListener('click', () => {
        const value = option.dataset.value;
        document.getElementById('priceSortButtonText').textContent = option.textContent;
        priceSortOptions.forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
        priceSortMenu.classList.remove('active');
        applyMyListingsFilters();
      });
    });
  }
  
  if (statusFilterButton && statusFilterMenu) {
    statusFilterButton.addEventListener('click', (e) => {
      e.stopPropagation();
      statusFilterMenu.classList.toggle('active');
      if (priceSortMenu) priceSortMenu.classList.remove('active');
      if (dateSortMenu) dateSortMenu.classList.remove('active');
    });
    
    statusFilterOptions.forEach(option => {
      option.addEventListener('click', () => {
        const value = option.dataset.value;
        document.getElementById('statusFilterButtonText').textContent = option.textContent;
        statusFilterOptions.forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
        statusFilterMenu.classList.remove('active');
        applyMyListingsFilters();
      });
    });
  }
  
  if (dateSortButton && dateSortMenu) {
    dateSortButton.addEventListener('click', (e) => {
      e.stopPropagation();
      dateSortMenu.classList.toggle('active');
      if (priceSortMenu) priceSortMenu.classList.remove('active');
      if (statusFilterMenu) statusFilterMenu.classList.remove('active');
    });
    
    dateSortOptions.forEach(option => {
      option.addEventListener('click', () => {
        const value = option.dataset.value;
        document.getElementById('dateSortButtonText').textContent = option.textContent;
        dateSortOptions.forEach(o => o.classList.remove('selected'));
        option.classList.add('selected');
        dateSortMenu.classList.remove('active');
        applyMyListingsFilters();
      });
    });
  }
  
  // Dropdown dışına tıklanırsa kapat
  document.addEventListener('click', () => {
    if (priceSortMenu) priceSortMenu.classList.remove('active');
    if (statusFilterMenu) statusFilterMenu.classList.remove('active');
    if (dateSortMenu) dateSortMenu.classList.remove('active');
  });
}

/**
 * Form ayarlarını yapar
 */
function setupForm() {
  // Eğer form sayfada yoksa (örn: seller-profile sayfasında) çık
  if (!document.getElementById('addListingForm')) {
    return;
  }

  document.getElementById('addListingForm').addEventListener('submit', handleFormSubmit);
  document.getElementById('formImages').addEventListener('change', handleImageUpload);
  
  // Video setup
  setupVideoHandling();

  // Terms modal setup
  setupTermsModal();

  ['formTitle', 'formBrand', 'formModel', 'formSellerName', 'formLocation', 'formDescription'].forEach(id => {
    const input = document.getElementById(id);
    input.addEventListener('input', (e) => {
      const start = e.target.selectionStart;
      let value = e.target.value;

      if (id === 'formTitle' && value.length > MAX_TITLE_LENGTH) {
        value = value.slice(0, MAX_TITLE_LENGTH);
      }

      if (value.length > 0) {
        value = value.charAt(0).toUpperCase() + value.slice(1);
      }

      if (value !== e.target.value) {
        const caret = Math.min(start, value.length);
        e.target.value = value;
        e.target.setSelectionRange(caret, caret);
      } else {
        e.target.setSelectionRange(start, start);
      }
      if (id === 'formTitle' || id === 'formCallsign' || id === 'formPrice') {
        updatePreview();
      }
    });
  });

  const callsignInput = document.getElementById('formCallsign');
  callsignInput.addEventListener('input', (e) => {
    const start = e.target.selectionStart;
    e.target.value = e.target.value.toUpperCase();
    e.target.setSelectionRange(start, start);
    updatePreview();
  });

  document.getElementById('formCurrency').addEventListener('change', updatePreview);

  const emailInput = document.getElementById('formEmail');
  emailInput.addEventListener('blur', (e) => {
    const email = e.target.value.trim();
    if (email && !isValidEmail(email)) {
      e.target.setCustomValidity('Geçerli bir e-posta adresi girin');
      e.target.reportValidity();
    } else {
      e.target.setCustomValidity('');
    }
  });
  emailInput.addEventListener('input', (e) => e.target.setCustomValidity(''));

  // Telefon numarası doğrulama (10 haneli, başında 0 yok)
  const phoneInput = document.getElementById('formPhone');
  phoneInput.addEventListener('blur', (e) => {
    const digits = e.target.value.replace(/\D/g, '');
    if (digits.length > 0 && digits.length !== 10) {
      e.target.setCustomValidity('Telefon numarası tam olarak 10 hane olmalıdır (başında 0 olmadan)');
      e.target.reportValidity();
    } else {
      e.target.setCustomValidity('');
    }
  });
  phoneInput.addEventListener('input', (e) => e.target.setCustomValidity(''));
}

/**
 * Kullanım sözleşmesi modalını ayarlar
 */
function setupTermsModal() {
  const termsModal = document.getElementById('termsModal');
  const openTermsLink = document.getElementById('openTermsLink');
  const termsModalCloseBtn = document.getElementById('termsModalCloseBtn');
  const closeTermsBtn = document.getElementById('closeTermsBtn');
  const acceptTermsBtn = document.getElementById('acceptTermsBtn');
  const termsCheckbox = document.getElementById('formTermsCheckbox');
  
  // Sözleşme linkine tıklandığında modalı aç
  if (openTermsLink) {
    openTermsLink.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      if (termsModal) {
        termsModal.style.display = 'flex';
        // Ana modalın scrollunu koru, sadece terms modal açılsın
      }
    });
  }
  
  // Modal kapatma butonları
  const closeTermsModal = () => {
    if (termsModal) {
      termsModal.style.display = 'none';
    }
  };
  
  if (termsModalCloseBtn) {
    termsModalCloseBtn.addEventListener('click', closeTermsModal);
  }
  
  if (closeTermsBtn) {
    closeTermsBtn.addEventListener('click', closeTermsModal);
  }
  
  // Kabul et butonu - checkbox'ı işaretle ve modalı kapat
  if (acceptTermsBtn) {
    acceptTermsBtn.addEventListener('click', () => {
      termsCheckbox.checked = true;
      closeTermsModal();
    });
  }
  
  // Modal dışına tıklandığında kapat
  if (termsModal) {
    termsModal.addEventListener('click', (e) => {
      if (e.target.id === 'termsModal') {
        closeTermsModal();
      }
    });
  }
}

/**
 * Login Required Modal ayarlarını yapar
 */
function setupLoginRequiredModal() {
  const loginModal = document.getElementById('loginRequiredModal');
  const loginCloseBtn = document.getElementById('loginRequiredCloseBtn');
  const loginCancelBtn = document.getElementById('loginRequiredCancelBtn');
  
  const closeLoginModal = () => {
    if (loginModal) {
      loginModal.style.display = 'none';
      document.body.style.overflow = '';
    }
  };
  
  if (loginCloseBtn) {
    loginCloseBtn.addEventListener('click', closeLoginModal);
  }
  
  if (loginCancelBtn) {
    loginCancelBtn.addEventListener('click', closeLoginModal);
  }
  
  if (loginModal) {
    loginModal.addEventListener('click', (e) => {
      if (e.target.id === 'loginRequiredModal') {
        closeLoginModal();
      }
    });
  }
}

/**
 * Login Required modalını gösterir
 */
function showLoginRequiredModal() {
  const loginModal = document.getElementById('loginRequiredModal');
  if (loginModal) {
    loginModal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }
}

/**
 * Kullanıcının çağrı işaretini veritabanından yükler ve form alanını doldurur
 */
async function loadUserCallsign() {
  try {
    const formData = new FormData();
    formData.append('action', 'get_user_callsign');
    
    const response = await fetch(ativ_ajax.url, {
      method: 'POST',
      body: formData
    });
    
    const result = await response.json();
    
    if (result.success && result.data && result.data.callsign) {
      const callsignInput = document.getElementById('formCallsign');
      if (callsignInput) {
        callsignInput.value = result.data.callsign;
      }
    } else {
      const errorMessage = result.data && result.data.message 
        ? result.data.message 
        : 'Çağrı işareti bilgisi alınamadı';
      console.warn('Çağrı işareti yükleme başarısız:', errorMessage, result);
    }
  } catch (error) {
    console.error('Çağrı işareti yükleme hatası - Ağ veya sunucu sorunu:', error);
  }
}

/**
 * Yeni ilan ekleme modalını açar
 */
async function openAddListingModal() {
  // Giriş yapmamış kullanıcılar için login modalı göster
  if (!ativ_ajax.is_user_logged_in) {
    showLoginRequiredModal();
    return;
  }
  
  try {
    // Kullanıcının yasaklı olup olmadığını kontrol et
    const banData = await checkUserBanStatus();
    
    if (banData.is_banned) {
      showBannedUserModal(banData.ban_reason, banData.banned_at);
      return;
    }
    
    // Yasaklı değilse modalı aç
    editingListing = null;
    document.getElementById('addListingModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    document.querySelector('.modal-header h2').textContent = 'Yeni İlan Ekle';
    document.getElementById('formSubmitBtn').textContent = 'İlanı Yayınla';
    
    // Kullanıcının çağrı işaretini veritabanından al ve otomatik doldur
    await loadUserCallsign();
    
    updatePreview();
    
    // Ülke kodlarını doldur (varsayılan Türkiye)
    populateCountryCodes('+90');
    
    // Şehir listesini yükle
    loadCities();
    
    // Kategori ve durum dropdown'larını ayarla
    setupCategoryDropdown();
    setupConditionDropdown();
  } catch (error) {
    console.error('Ban kontrolü hatası:', error);
    // Hata olsa bile devam et
    editingListing = null;
    document.getElementById('addListingModal').style.display = 'flex';
    document.body.style.overflow = 'hidden';
    document.querySelector('.modal-header h2').textContent = 'Yeni İlan Ekle';
    document.getElementById('formSubmitBtn').textContent = 'İlanı Yayınla';
    
    // Kullanıcının çağrı işaretini veritabanından al ve otomatik doldur
    await loadUserCallsign();
    
    updatePreview();
    populateCountryCodes('+90');
    loadCities();
    setupCategoryDropdown();
    setupConditionDropdown();
  }
}

/**
 * İlan düzenleme modalını açar
 */
async function openEditListingModal(listingOrId) {
  // Accept either a listing object or an id. Always try to use the freshest listing from allListings.
  let listing = listingOrId;
  if (typeof listingOrId === 'number' || typeof listingOrId === 'string') {
    listing = allListings.find(l => l.id == listingOrId);
    if (!listing) {
      try {
        await loadListings();
        listing = allListings.find(l => l.id == listingOrId);
      } catch (e) {
        console.error('Listing yüklenemedi:', e);
      }
    }
  }

  if (!listing) {
    alert('İlan bilgisi yüklenemedi');
    return;
  }

  // Use a shallow copy to avoid accidental mutation of the global allListings item
  editingListing = Object.assign({}, listing);
  
  // Flag'i ilanın statüsüne göre ayarla
  isEditingRejectedListing = (listing.status === 'rejected' || listing.status === 'approved');

  // Populate modal fields
  const safeTitle = (listing.title || '').slice(0, MAX_TITLE_LENGTH);
  document.getElementById('formTitle').value = safeTitle;
  document.getElementById('formBrand').value = listing.brand || '';
  document.getElementById('formModel').value = listing.model || '';
  document.getElementById('formPrice').value = listing.price || '';
  document.getElementById('formCurrency').value = listing.currency || 'TRY';
  document.getElementById('formDescription').value = listing.description || '';
  
  // Çağrı işaretini veritabanından al (ilan üzerindeki değil, kullanıcı tablosundaki)
  await loadUserCallsign();
  
  document.getElementById('formSellerName').value = listing.seller_name || '';
  document.getElementById('formLocation').value = listing.location || '';
  document.getElementById('formEmail').value = listing.seller_email || '';
  
  // Kategori ve durum mapping
  const categoryMapping = {
    'transceiver': '📻 Telsiz',
    'antenna': '📡 Anten',
    'amplifier': '🔊 Amplifikatör',
    'accessory': '🔌 Aksesuar',
    'other': '📦 Diğer'
  };
  
  const conditionMapping = {
    'Sıfır': '✨ Sıfır',
    'Kullanılmış': '♻️ Kullanılmış',
    'Arızalı': '⚠️ Arızalı',
    'El Yapımı': '🔧 El Yapımı'
  };
  
  // Değerleri geçici olarak sakla
  const categoryValue = listing.category || '';
  const categoryLabel = categoryMapping[categoryValue] || '';
  const conditionValue = listing.condition || '';
  const conditionLabel = conditionMapping[conditionValue] || '';
  
  console.log('🔍 DEBUG - Edit Modal Opening:');
  console.log('Listing data:', { category: categoryValue, condition: conditionValue });
  console.log('Mapped labels:', { categoryLabel, conditionLabel });
  
  // Dropdown'ları kur
  console.log('Setting up dropdowns...');
  setupCategoryDropdown();
  setupConditionDropdown();
  console.log('Dropdowns setup complete');
  
  // Değerleri set et (dropdown kurulduktan sonra)
  const categoryInput = document.getElementById('formCategory');
  const categoryHidden = document.getElementById('formCategoryValue');
  const conditionInput = document.getElementById('formCondition');
  const conditionHidden = document.getElementById('formConditionValue');
  
  console.log('Setting values...');
  if (categoryInput) {
    categoryInput.value = categoryLabel;
    console.log('Category input set:', categoryInput.value);
  }
  if (categoryHidden) {
    categoryHidden.value = categoryValue;
    console.log('Category hidden set:', categoryHidden.value);
  }
  
  if (conditionInput) {
    conditionInput.value = conditionLabel;
    console.log('Condition input set:', conditionInput.value);
  }
  if (conditionHidden) {
    conditionHidden.value = conditionValue;
    console.log('Condition hidden set:', conditionHidden.value);
  }
  console.log('✅ Values set complete');
  
  // Telefonu parse et ve alanları doldur
  const phoneData = parsePhoneNumber(listing.seller_phone || '');
  populateCountryCodes(phoneData.dialCode);
  document.getElementById('formPhone').value = formatPhoneNumber(phoneData.number);

  // Normalize images for previews: stored images may be strings (filenames) or objects
  uploadedImages.forEach(img => { if (img && img.previewUrl) URL.revokeObjectURL(img.previewUrl); });
  uploadedImages = [];
  featuredImageIndex = 0;

  if (listing.images && Array.isArray(listing.images) && listing.images.length > 0) {
    listing.images.forEach((img, idx) => {
      if (typeof img === 'string') {
        // Existing filename stored in DB -> build URL for preview
        const src = (typeof ativ_ajax !== 'undefined' && ativ_ajax.upload_url) ? (ativ_ajax.upload_url + listing.id + '/' + img) : img;
        uploadedImages.push({ name: img, data: src });
      } else if (img && (img.data || img.name)) {
        // Already an object (legacy support)
        if (img.data && img.name) uploadedImages.push({ name: img.name, data: img.data });
        else if (img.name) uploadedImages.push({ name: img.name, data: (typeof ativ_ajax !== 'undefined' ? (ativ_ajax.upload_url + listing.id + '/' + img.name) : img.name) });
      }
    });
    featuredImageIndex = listing.featured_image_index || 0;
    renderImagePreviews();
  }
  
  // Video preview (eğer varsa)
  if (listing.video) {
    const videoPreview = document.getElementById('videoPreviewContainer');
    const videoStatus = document.getElementById('videoStatusHint');
    if (videoPreview) {
      videoPreview.innerHTML = `
        <div style="position: relative; border-radius: 8px; overflow: hidden; background: #000;">
          <video width="100%" height="200" controls style="display: block;">
            <source src="${listing.video}" type="video/mp4">
          </video>
          <button type="button" onclick="removeVideo()" style="position: absolute; top: 8px; right: 8px; background: rgba(255,0,0,0.8); color: white; border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 12px;">Kaldır</button>
        </div>
      `;
    }
    if (videoStatus) {
      videoStatus.innerHTML = '✅ Video mevcut (Yeni video seçerek değiştirebilirsiniz)';
      videoStatus.style.color = '#2e7d32';
    }
  }

  // Show modal last so previews/fields are ready
  document.getElementById('addListingModal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
  document.querySelector('.modal-header h2').textContent = 'İlanı Düzenle';
  
  // Buton textini ilanın statüsüne göre ayarla
  const submitBtnText = (listing.status === 'rejected' || listing.status === 'approved') ? 
    'Değişiklikleri Kaydet ve Tekrar Onaya Gönder' : 
    'Değişiklikleri Kaydet';
  document.getElementById('formSubmitBtn').textContent = submitBtnText;

  updatePreview();
  
  // Şehir listesini yükle
  loadCities();
}

/**
 * İlan ekleme modalını kapatır
 */
function closeAddListingModal() {
  // Temp video varsa sil
  if (uploadedVideoTempPath) {
    deleteTempVideo(uploadedVideoTempPath);
  }
  
  document.getElementById('addListingModal').style.display = 'none';
  document.body.style.overflow = 'auto';
  document.getElementById('addListingForm').reset();
  document.getElementById('formMessage').innerHTML = '';
  document.getElementById('formTermsCheckbox').checked = false; // Checkbox'ı temizle
  uploadedImages = [];
  featuredImageIndex = 0;
  editingListing = null;
  isEditingRejectedListing = false;
  
  // Video temizliği
  selectedVideoFile = null;
  uploadedVideoTempPath = null;
  videoUploadInProgress = false;
  const videoPreview = document.getElementById('videoPreviewContainer');
  const videoStatus = document.getElementById('videoStatusHint');
  if (videoPreview) videoPreview.innerHTML = '';
  if (videoStatus) videoStatus.innerHTML = '';
  
  document.querySelector('.modal-header h2').textContent = 'Yeni İlan Ekle';
  document.getElementById('formSubmitBtn').textContent = 'İlanı Yayınla';
  renderImagePreviews();
}

/**
 * Önizlemeyi günceller
 */
function updatePreview() {
  const title = document.getElementById('formTitle').value.trim();
  const callsign = document.getElementById('formCallsign').value.trim();
  const price = document.getElementById('formPrice').value;
  const currency = document.getElementById('formCurrency').value;

  const previewTitle = document.getElementById('previewTitle');
  const previewCallsign = document.getElementById('previewCallsign');
  const previewPrice = document.getElementById('previewPrice');
  const previewImage = document.getElementById('previewImage');

  previewTitle.innerHTML = title || '<span class="preview-empty-state">İlan başlığı...</span>';
  previewCallsign.innerHTML = callsign || '<span class="preview-empty-state">Çağrı işareti...</span>';

  const currencySymbol = getCurrencySymbol(currency);
  const displayPrice = price && parseFloat(price) > 0 ? parseFloat(price) : 0;
  previewPrice.innerHTML = `${currencySymbol}${displayPrice} ${currency}`;
  
  if (uploadedImages.length > 0) {
    previewImage.innerHTML = `<img src="${uploadedImages[featuredImageIndex].data}" alt="Preview">`;
  } else {
    previewImage.innerHTML = '📻';
  }
}

/**
 * Görsel yükleme işleyicisi
 */
function handleImageUpload(e) {
  const files = Array.from(e.target.files);
  const maxFiles = 5;
  
  if (uploadedImages.length + files.length > maxFiles) {
    const messageDiv = document.getElementById('formMessage');
    messageDiv.innerHTML = '<div class="error-message">Maksimum 5 görsel yükleyebilirsiniz.</div>';
    setTimeout(() => messageDiv.innerHTML = '', 3000);
    return;
  }

  files.forEach(file => {
    if (file.type.startsWith('image/')) {
      const reader = new FileReader();
      reader.onload = (event) => {
        uploadedImages.push({ data: event.target.result, name: file.name });
        renderImagePreviews();
      };
      reader.readAsDataURL(file);
    }
  });

  e.target.value = '';
}

/**
 * Görsel önizlemelerini render eder
 */
function renderImagePreviews() {
  const container = document.getElementById('imagePreviewContainer');
  container.innerHTML = '';

  uploadedImages.forEach((image, index) => {
    const previewItem = document.createElement('div');
    previewItem.className = 'image-preview-item' + (index === featuredImageIndex ? ' featured' : '');
    
    previewItem.innerHTML = `
      <img src="${image.data}" alt="Preview ${index + 1}">
      <div class="image-preview-actions">
        <button type="button" class="image-action-btn" onclick="setFeaturedImage(${index})" title="Vitrin Fotoğrafı Yap">⭐</button>
        <button type="button" class="image-action-btn" onclick="removeImage(${index})" title="Sil">🗑️</button>
      </div>
      ${index === featuredImageIndex ? '<div class="featured-badge">VİTRİN</div>' : ''}
    `;
    
    container.appendChild(previewItem);
  });
  
  updatePreview();
}

/**
 * Vitrin görselini ayarlar
 */
window.setFeaturedImage = function(index) {
  featuredImageIndex = index;
  renderImagePreviews();
};

/**
 * Görseli kaldırır
 */
window.removeImage = function(index) {
  uploadedImages.splice(index, 1);
  if (featuredImageIndex >= uploadedImages.length) {
    featuredImageIndex = Math.max(0, uploadedImages.length - 1);
  }
  renderImagePreviews();
};

/**
 * Form gönderim işleyicisi
 */
/**
 * Modal üzerinde yükleme göstergesi gösterir
 */
function showModalLoading(message = 'İşlem yapılıyor, lütfen bekleyin...') {
  const modal = document.getElementById('addListingModal');
  if (!modal) return;
  
  // Eğer zaten varsa kaldır
  const existing = modal.querySelector('.modal-loading-overlay');
  if (existing) existing.remove();
  
  const overlay = document.createElement('div');
  overlay.className = 'modal-loading-overlay';
  overlay.innerHTML = `
    <div class="modal-loading-content">
      <div class="modal-loading-spinner"></div>
      <div class="modal-loading-text">${message}</div>
    </div>
  `;
  
  modal.appendChild(overlay);
}

/**
 * Modal yükleme göstergesini kaldırır
 */
function hideModalLoading() {
  const modal = document.getElementById('addListingModal');
  if (!modal) return;
  
  const overlay = modal.querySelector('.modal-loading-overlay');
  if (overlay) {
    overlay.style.opacity = '0';
    setTimeout(() => overlay.remove(), 200);
  }
}

async function handleFormSubmit(e) {
  e.preventDefault();
  
  const submitBtn = document.getElementById('formSubmitBtn');
  const messageDiv = document.getElementById('formMessage');
  const termsCheckbox = document.getElementById('formTermsCheckbox');
  const titleInput = document.getElementById('formTitle');
  const trimmedTitle = titleInput.value.trim();
  
  // Sözleşme kontrolü
  if (!termsCheckbox.checked) {
    messageDiv.innerHTML = '<div class="error-message">Lütfen Kullanım Sözleşmesi ve KVKK Aydınlatma Metni\'ni kabul edin.</div>';
    termsCheckbox.focus();
    setTimeout(() => {
      messageDiv.innerHTML = '';
    }, 3000);
    return;
  }

  // Başlık uzunluğu kontrolü
  if (trimmedTitle.length > MAX_TITLE_LENGTH) {
    titleInput.setCustomValidity(`İlan başlığı en fazla ${MAX_TITLE_LENGTH} karakter olabilir.`);
    titleInput.reportValidity();
    messageDiv.innerHTML = `<div class="error-message">İlan başlığı en fazla ${MAX_TITLE_LENGTH} karakter olabilir.</div>`;
    setTimeout(() => {
      messageDiv.innerHTML = '';
      titleInput.setCustomValidity('');
    }, 3000);
    return;
  } else {
    titleInput.setCustomValidity('');
  }
  
  submitBtn.disabled = true;
  const isEditing = editingListing !== null;
  submitBtn.innerHTML = `<span class="loading-spinner"></span>${isEditing ? 'Kaydediliyor...' : 'Ekleniyor...'}`;
  messageDiv.innerHTML = '';
  
  // Yükleme overlay'ini göster
  showModalLoading(isEditing ? 'İlan güncelleniyor, lütfen bekleyin...' : 'İlan kaydediliyor, lütfen bekleyin...');

  const callsign = document.getElementById('formCallsign').value.trim();
  
  if (!userCallsign) {
    userCallsign = callsign;
    localStorage.setItem('userCallsign', callsign);
  }

  const listingData = {
    title: trimmedTitle,
    category: document.getElementById('formCategoryValue')?.value || document.getElementById('formCategory').value,
    brand: document.getElementById('formBrand').value.trim(),
    model: document.getElementById('formModel').value.trim(),
    condition: document.getElementById('formConditionValue')?.value || document.getElementById('formCondition').value,
    price: parseFloat(document.getElementById('formPrice').value),
    currency: document.getElementById('formCurrency').value,
    description: document.getElementById('formDescription').value.trim(),
    images: uploadedImages.length > 0 ? uploadedImages : null,
    featuredImageIndex: featuredImageIndex,
    video: isEditing && editingListing && editingListing.video ? editingListing.video : null, // Mevcut video URL'si (değiştirilmezse)
    emoji: uploadedImages.length > 0 ? null : "📻",
    callsign: callsign,
    seller_name: document.getElementById('formSellerName').value.trim(),
    location: document.getElementById('formLocation').value.trim(),
    seller_email: document.getElementById('formEmail').value.trim(),
    seller_phone: (document.getElementById('formCountryCode').value + ' ' + document.getElementById('formPhone').value.replace(/\s/g, '')).trim()
  };

  // Video zaten temp'e yüklenmiş, sadece URL'yi ekle
  if (uploadedVideoTempPath) {
    listingData.video_temp_path = uploadedVideoTempPath;
  } else if (isEditing && editingListing && editingListing.video) {
    listingData.video = editingListing.video; // Mevcut video'yu koru
  }

  try {
    if (isEditing) {
      await updateListing(editingListing.id, listingData);
      if (isEditingRejectedListing) {
        messageDiv.innerHTML = '<div class="success-message">İlan başarıyla güncellendi ve tekrar onaya gönderildi!</div>';
      } else {
        messageDiv.innerHTML = '<div class="success-message">İlan başarıyla güncellendi!</div>';
      }
    } else {
      // YENİ İLAN: Sadece ilanı kaydet (video zaten temp'te)
      await saveListing(listingData);
      messageDiv.innerHTML = '<div class="success-message">İlanınız başarıyla eklendi!</div>';
    }
    
    await loadListings();
    populateFilterOptions();
    applyFiltersAndRender();
    
    // "Benim İlanlarım" sayfası grid'ini de yenile (eğer açıksa)
    const myGrid = document.getElementById('myListingsGrid');
    if (myGrid) {
      try {
        await refreshMyListingsGrid();
      } catch (err) {
        console.error('Grid refresh hatası:', err);
      }
    }
    
    // Yükleme overlay'ini kaldır
    hideModalLoading();
    
    // Submit butonu tekrar aktif et
    submitBtn.disabled = false;
    const submitBtnText = isEditing ? 
      (isEditingRejectedListing ? 'Güncelle ve Tekrar Onaya Gönder' : 'Değişiklikleri Kaydet') : 
      'İlanı Yayınla';
    submitBtn.textContent = submitBtnText;
    
    // Başarıyı göster ve modal kapat
    setTimeout(() => {
      closeAddListingModal();
      isEditingRejectedListing = false; // Flag'i temizle
    }, 1500);
  } catch (error) {
    hideModalLoading();
    messageDiv.innerHTML = '<div class="error-message">İşlem sırasında bir hata oluştu. Lütfen tekrar deneyin.</div>';
    
    // Hata durumunda da butonu aktif et
    submitBtn.disabled = false;
    const submitBtnText = isEditing ? 
      (isEditingRejectedListing ? 'Güncelle ve Tekrar Onaya Gönder' : 'Değişiklikleri Kaydet') : 
      'İlanı Yayınla';
    submitBtn.textContent = submitBtnText;
  }
}

/**
 * İlan düzenleme fonksiyonu
 */
window.editListing = async function(id) {
  // Önce ban durumunu kontrol et (Console bypass koruması)
  try {
    const banCheckResponse = await fetch(ajaxurl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'check_user_ban',
        nonce: atheneaNonce
      })
    });
    const banStatus = await banCheckResponse.json();
    if (banStatus.is_banned) {
      showBannedUserModal(banStatus.ban_reason, banStatus.banned_at);
      return;
    }
  } catch (e) {
    console.error('Ban durumu kontrol edilemedi:', e);
  }
  
  // My-listings veya gallery'den gelmiş olabilir
  let listing = null;
  const idNum = Number(id);
  // Önce allListings'den ara (gallery)
  listing = allListings.find(l => Number(l.id) === idNum);
  
  // Yoksa loadListings'i çağır ve yeniden ara
  if (!listing) {
    try {
      await loadListings();
      listing = allListings.find(l => Number(l.id) === idNum);
    } catch (e) {
      console.error('Listing yüklenemedi:', e);
    }
  }
  
  if (listing) {
    openEditListingModal(listing);
  } else {
    alert('İlan bilgisi yüklenemedi');
  }
};

/**
 * İlan silme onayı
 */
window.confirmDeleteListing = async function(id) {
  // Önce ban durumunu kontrol et (Console bypass koruması)
  try {
    const banCheckResponse = await fetch(window.ajaxurl || ajaxurl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        action: 'check_user_ban',
        nonce: atheneaNonce
      })
    });
    const banStatus = await banCheckResponse.json();
    if (banStatus.is_banned) {
      showBannedUserModal(banStatus.ban_reason, banStatus.banned_at);
      return;
    }
  } catch (e) {
    console.error('Ban durumu kontrol edilemedi:', e);
  }
  
  // My-listings sayfasından gelen silme işlemi için direkt title bul
  let listing = null;
  
  // Eğer sayfada listing varsa (my-listings) direkt HTML'den al
  const rowElement = document.querySelector(`[data-listing-id="${id}"]`);
  if (rowElement) {
    const titleElement = rowElement.querySelector('.listing-row-title');
    listing = {
      id: id,
      title: titleElement ? titleElement.textContent : 'İlan'
    };
  } else {
    // Yoksa allListings'den ara (gallery sayfası)
    listing = allListings.find(l => l.id === id);
  }
  
  if (!listing) {
    alert('İlan bulunamadı');
    return;
  }

  const modal = document.createElement('div');
  modal.className = 'modal-overlay';
  modal.id = 'deleteConfirmModal';
  
  modal.innerHTML = `
    <div class="delete-confirmation">
      <h3>İlanı Sil</h3>
      <p><strong>${listing.title}</strong> ilanını silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.</p>
      <div class="delete-confirmation-actions">
        <button class="btn-delete-cancel" id="deleteCancelBtn">İptal</button>
        <button class="btn-delete-confirm" id="deleteConfirmBtn">Sil</button>
      </div>
    </div>
  `;

  // Modal'ı #ativ-container içine ekle - CSS stilleri için gerekli
  const ativContainer = document.getElementById('ativ-container');
  if (ativContainer) {
    ativContainer.appendChild(modal);
  } else {
    document.body.appendChild(modal);
  }
  document.body.style.overflow = 'hidden';

  document.getElementById('deleteCancelBtn').addEventListener('click', () => {
    modal.remove();
    document.body.style.overflow = 'auto';
  });

  document.getElementById('deleteConfirmBtn').addEventListener('click', async () => {
    const confirmBtn = document.getElementById('deleteConfirmBtn');
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<span class="loading-spinner"></span>Siliniyor...';

    try {
      await deleteListing(id);
      
      // My-listings sayfasında ise satırı sil
      if (rowElement) {
        const wrapper = rowElement.closest('.listing-row-wrapper');
        if (wrapper) {
          wrapper.remove();
        }
      } else {
        // Gallery sayfasında ise yeniden yükle
        await loadListings();
        populateFilterOptions();
        applyFiltersAndRender();
      }
      
      modal.remove();
      document.body.style.overflow = 'auto';
    } catch (error) {
      console.error('Silme işlemi başarısız:', error);
      confirmBtn.disabled = false;
      confirmBtn.textContent = 'Sil';
    }
  });

  modal.addEventListener('click', (e) => {
    if (e.target.id === 'deleteConfirmModal') {
      modal.remove();
      document.body.style.overflow = 'auto';
    }
  });
};

// ========================================
// Lightbox Fonksiyonları
// ========================================

/**
 * Lightbox'ı açar
 */
function openLightbox(images, startIndex = 0, source = 'detail') {
  if (!images || images.length === 0) return;
  
  currentImages = images;
  currentLightboxSlide = startIndex;
  isLightboxOpen = true;
  lightboxSource = source;
  
  // Lightbox HTML'ini oluştur
  const lightboxHTML = `
    <div class="lightbox-overlay active" id="lightboxOverlay">
      <button class="lightbox-close" onclick="closeLightbox()">×</button>
      
      <div class="lightbox-nav">
        <button class="lightbox-arrow prev-arrow" onclick="changeLightboxSlide(-1)">‹</button>
        <button class="lightbox-arrow next-arrow" onclick="changeLightboxSlide(1)">›</button>
      </div>
      
      <div class="lightbox-content" id="lightboxContent">
        ${images.map((image, index) => `
          <img src="${image.data}" 
               alt="İlan görseli ${index + 1}" 
               class="lightbox-image ${index === startIndex ? 'active' : ''} ${source === 'detail' ? 'zoomable' : ''}"
               ${source === 'detail' ? 'onclick="toggleZoom(this)"' : ''}
               loading="lazy">
        `).join('')}
      </div>
      
      <div class="lightbox-counter">
        ${startIndex + 1} / ${images.length}
      </div>
      
      ${images.length > 1 ? `
        <div class="lightbox-thumbnails">
          ${images.map((image, index) => `
            <div class="lightbox-thumbnail ${index === startIndex ? 'active' : ''}" 
                 onclick="goToLightboxSlide(${index})">
              <img src="${image.data}" alt="Thumbnail ${index + 1}">
            </div>
          `).join('')}
        </div>
      ` : ''}
    </div>
  `;
  
  // Lightbox'ı body'ye ekle - fixed positioning ve z-index için gerekli
  document.body.insertAdjacentHTML('beforeend', lightboxHTML);
  document.body.style.overflow = 'hidden';
  
  // Klavye event listener'larını ekle
  document.addEventListener('keydown', handleLightboxKeydown);
}

/**
 * Lightbox'ı kapatır
 */
function closeLightbox() {
  const lightbox = document.getElementById('lightboxOverlay');
  if (lightbox) {
    lightbox.remove();
  }
  isLightboxOpen = false;
  lightboxSource = '';
  document.body.style.overflow = 'auto';
  
  // Klavye event listener'larını kaldır
  document.removeEventListener('keydown', handleLightboxKeydown);
}

/**
 * Lightbox slaytını değiştirir
 */
function changeLightboxSlide(direction) {
  if (currentImages.length === 0) return;
  
  // Mevcut slaytı gizle
  const currentImage = document.querySelector('.lightbox-image.active');
  if (currentImage) {
    currentImage.classList.remove('active');
    currentImage.classList.remove('zooming'); // Zoom'u sıfırla
    currentImage.style.transform = 'scale(1)'; // Transform'u sıfırla
  }
  
  // Thumbnail'leri güncelle
  const currentThumbnail = document.querySelector('.lightbox-thumbnail.active');
  if (currentThumbnail) {
    currentThumbnail.classList.remove('active');
  }
  
  // Yeni slaytı hesapla
  currentLightboxSlide += direction;
  
  // Sınırları kontrol et
  if (currentLightboxSlide >= currentImages.length) {
    currentLightboxSlide = 0;
  } else if (currentLightboxSlide < 0) {
    currentLightboxSlide = currentImages.length - 1;
  }
  
  // Yeni slaytı göster
  showLightboxSlide(currentLightboxSlide);
}

/**
 * Belirli bir lightbox slaytına gider
 */
function goToLightboxSlide(slideIndex) {
  if (slideIndex < 0 || slideIndex >= currentImages.length) return;
  
  // Mevcut slaytı gizle
  const currentImage = document.querySelector('.lightbox-image.active');
  if (currentImage) {
    currentImage.classList.remove('active');
    currentImage.classList.remove('zooming');
    currentImage.style.transform = 'scale(1)';
  }
  
  // Thumbnail'leri güncelle
  const currentThumbnail = document.querySelector('.lightbox-thumbnail.active');
  if (currentThumbnail) {
    currentThumbnail.classList.remove('active');
  }
  
  currentLightboxSlide = slideIndex;
  showLightboxSlide(slideIndex);
}

/**
 * Lightbox slaytını gösterir
 */
function showLightboxSlide(slideIndex) {
  const images = document.querySelectorAll('.lightbox-image');
  const thumbnails = document.querySelectorAll('.lightbox-thumbnail');
  const counter = document.querySelector('.lightbox-counter');
  
  if (images[slideIndex]) {
    images[slideIndex].classList.add('active');
  }
  
  if (thumbnails[slideIndex]) {
    thumbnails[slideIndex].classList.add('active');
  }
  
  if (counter) {
    counter.textContent = `${slideIndex + 1} / ${currentImages.length}`;
  }
}

/**
 * Lightbox klavye işleyicisi
 */
function handleLightboxKeydown(e) {
  if (!isLightboxOpen) return;
  
  switch(e.key) {
    case 'Escape':
      closeLightbox();
      break;
    case 'ArrowLeft':
      changeLightboxSlide(-1);
      break;
    case 'ArrowRight':
      changeLightboxSlide(1);
      break;
    case ' ':
      // Sadece detail sayfasından açılmışsa zoom yap
      if (lightboxSource === 'detail') {
        e.preventDefault();
        const currentImage = document.querySelector('.lightbox-image.active');
        if (currentImage) {
          toggleZoom(currentImage);
        }
      }
      break;
  }
}

/**
 * Zoom toggle fonksiyonu - sadece detail sayfasında çalışır
 */
function toggleZoom(imageElement) {
  if (lightboxSource !== 'detail') return;
  
  if (imageElement.classList.contains('zooming')) {
    // Zoom'u kapat
    imageElement.classList.remove('zooming');
    imageElement.style.transform = 'scale(1)';
    imageElement.style.cursor = 'zoom-in';
  } else {
    // Zoom'u aç
    imageElement.classList.add('zooming');
    imageElement.style.transform = 'scale(1.5)';
    imageElement.style.cursor = 'zoom-out';
  }
}

// Lightbox overlay'e tıklanınca kapatma - zoom'u da sıfırla
document.addEventListener('click', function(e) {
  if (isLightboxOpen && e.target.id === 'lightboxOverlay') {
    // Zoom açıksa kapat
    const zoomedImage = document.querySelector('.lightbox-image.zooming');
    if (zoomedImage) {
      zoomedImage.classList.remove('zooming');
      zoomedImage.style.transform = 'scale(1)';
    }
    closeLightbox();
  }
});

// ========================================
// Slider Fonksiyonları
// ========================================

/**
 * Slider'ı başlatır
 */
function initSlider(images, containerId, startIndex = 0) {
  const container = document.getElementById(containerId);
  if (!container || !images || images.length === 0) return;
  
  currentSlide = startIndex;
  
  // Slider HTML'ini oluştur
  let sliderHTML = `
    <div class="image-slider">
      <div class="slider-counter">${startIndex + 1} / ${images.length}</div>
      <div class="slider-arrows">
        <button class="slider-arrow prev-arrow" onclick="changeSlide(-1, '${containerId}')">‹</button>
        <button class="slider-arrow next-arrow" onclick="changeSlide(1, '${containerId}')">›</button>
      </div>
  `;
  
  // Görselleri ekle - tıklanabilir yap (detail source ile)
  images.forEach((image, index) => {
    const isActive = index === startIndex;
    sliderHTML += `
      <img src="${image.data}" 
           alt="İlan görseli ${index + 1}" 
           class="slider-image ${isActive ? 'active' : ''}"
           onclick="openLightbox(currentImages, ${index}, 'detail')"
           loading="lazy">
    `;
  });
  
  // Navigasyon noktalarını ekle
  sliderHTML += `<div class="slider-nav">`;
  images.forEach((_, index) => {
    sliderHTML += `
      <div class="slider-dot ${index === startIndex ? 'active' : ''}" 
           onclick="goToSlide(${index}, '${containerId}')"></div>
    `;
  });
  sliderHTML += `</div>`;
  
  sliderHTML += `</div>`;
  
  // Küçük resim önizlemeleri
  if (images.length > 1) {
    sliderHTML += `<div class="image-thumbnails">`;
    images.forEach((image, index) => {
      sliderHTML += `
        <div class="thumbnail ${index === startIndex ? 'active' : ''}" 
             onclick="goToSlide(${index}, '${containerId}')">
          <img src="${image.data}" alt="Thumbnail ${index + 1}">
        </div>
      `;
    });
    sliderHTML += `</div>`;
  }
  
  container.innerHTML = sliderHTML;
  
  // Container'a currentSlide'ı kaydet
  container.dataset.currentSlide = startIndex;
  
  // Global currentImages'i güncelle (lightbox için)
  currentImages = images;
}

/**
 * Slider slaytını değiştirir
 */
function changeSlide(direction, containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;
  
  const images = container.querySelectorAll('.slider-image');
  const dots = container.querySelectorAll('.slider-dot');
  const thumbnails = container.querySelectorAll('.thumbnail');
  const counter = container.querySelector('.slider-counter');
  
  if (images.length === 0) return;
  
  // Container'da saklı currentSlide'ı al
  let containerCurrentSlide = parseInt(container.dataset.currentSlide || 0);
  
  // Mevcut slaytı gizle
  images[containerCurrentSlide].classList.remove('active');
  dots[containerCurrentSlide].classList.remove('active');
  if (thumbnails.length > 0) {
    thumbnails[containerCurrentSlide].classList.remove('active');
  }
  
  // Yeni slaytı hesapla
  containerCurrentSlide += direction;
  
  // Sınırları kontrol et
  if (containerCurrentSlide >= images.length) {
    containerCurrentSlide = 0;
  } else if (containerCurrentSlide < 0) {
    containerCurrentSlide = images.length - 1;
  }
  
  // Yeni slaytı göster
  images[containerCurrentSlide].classList.add('active');
  dots[containerCurrentSlide].classList.add('active');
  if (thumbnails.length > 0) {
    thumbnails[containerCurrentSlide].classList.add('active');
  }
  
  // Sayacı güncelle
  if (counter) {
    counter.textContent = `${containerCurrentSlide + 1} / ${images.length}`;
  }
  
  // Container'da güncellemeyi kaydet
  container.dataset.currentSlide = containerCurrentSlide;
  currentSlide = containerCurrentSlide; // Global'i de güncelle
}

/**
 * Belirli bir slider slaytına gider
 */
function goToSlide(slideIndex, containerId) {
  const container = document.getElementById(containerId);
  if (!container) return;
  
  const images = container.querySelectorAll('.slider-image');
  const dots = container.querySelectorAll('.slider-dot');
  const thumbnails = container.querySelectorAll('.thumbnail');
  const counter = container.querySelector('.slider-counter');
  
  if (slideIndex < 0 || slideIndex >= images.length) return;
  
  // Container'da saklı currentSlide'ı al
  let containerCurrentSlide = parseInt(container.dataset.currentSlide || 0);
  
  // Mevcut slaytı gizle
  images[containerCurrentSlide].classList.remove('active');
  dots[containerCurrentSlide].classList.remove('active');
  if (thumbnails.length > 0) {
    thumbnails[containerCurrentSlide].classList.remove('active');
  }
  
  // Yeni slayta geç
  containerCurrentSlide = slideIndex;
  
  // Yeni slaytı göster
  images[containerCurrentSlide].classList.add('active');
  dots[containerCurrentSlide].classList.add('active');
  if (thumbnails.length > 0) {
    thumbnails[containerCurrentSlide].classList.add('active');
  }
  
  // Sayacı güncelle
  if (counter) {
    counter.textContent = `${containerCurrentSlide + 1} / ${images.length}`;
  }
  
  // Container'da güncellemeyi kaydet
  container.dataset.currentSlide = containerCurrentSlide;
  currentSlide = containerCurrentSlide; // Global'i de güncelle
}

// Klavye kontrolleri
document.addEventListener('keydown', function(e) {
  const detailModal = document.getElementById('detailModal');
  if (!detailModal || detailModal.style.display === 'none') return;
  
  if (e.key === 'ArrowLeft') {
    changeSlide(-1, 'detailSlider');
  } else if (e.key === 'ArrowRight') {
    changeSlide(1, 'detailSlider');
  } else if (e.key === 'Escape') {
    closeDetailPanel();
  }
});

// ========================================
// Detay Paneli Fonksiyonları
// ========================================

/**
 * Detay panelini açar
 */
function openDetailPanel(listing) {
  closeDetailPanel();
  
  selectedListing = listing;
  
  const currencySymbol = getCurrencySymbol(listing.currency || 'TRY');

  const detailModal = document.createElement('div');
  detailModal.className = 'detail-modal-overlay';
  detailModal.id = 'detailModal';
  detailModal.setAttribute('tabindex', '0');
  
  let imageSection;
  
  if (listing.images && listing.images.length > 0) {
    // Görsel varsa slider göster
    imageSection = `
      <div class="detail-left-section">
        <div class="detail-card-preview">
          <div id="detailSlider"></div>
          <div class="detail-preview-content">
            <h3 class="detail-preview-title">${escapeHtml(listing.title)}</h3>
            <p class="detail-preview-callsign">${escapeHtml(listing.callsign)}</p>
            <p class="detail-preview-price">${currencySymbol}${escapeHtml(String(listing.price))} ${escapeHtml(listing.currency || 'TRY')}</p>
          </div>
        </div>
      </div>
    `;
  } else {
    // Görsel yoksa emoji göster
    imageSection = `
      <div class="detail-left-section">
        <div class="detail-card-preview">
          <div class="detail-preview-image">
            ${escapeHtml(listing.emoji || '📻')}
          </div>
          <div class="detail-preview-content">
            <h3 class="detail-preview-title">${escapeHtml(listing.title)}</h3>
            <p class="detail-preview-callsign">${escapeHtml(listing.callsign)}</p>
            <p class="detail-preview-price">${currencySymbol}${escapeHtml(String(listing.price))} ${escapeHtml(listing.currency || 'TRY')}</p>
          </div>
        </div>
      </div>
    `;
  }
  
  detailModal.innerHTML = `
    <div class="detail-modal-content">
      ${imageSection}
      <div class="detail-right-section">
        <div class="detail-header">
          <h2>İlan Detayı</h2>
          <button class="close-btn" id="detailCloseBtn" aria-label="Kapat">×</button>
        </div>
        <div class="detail-sections">
          ${createDetailSections(listing)}
        </div>
      </div>
    </div>
  `;

  // Modal'ı body'ye ekle - fixed positioning için gerekli
  document.body.appendChild(detailModal);
  document.body.style.overflow = 'hidden';

  // Slider'ı başlat (eğer görsel varsa)
  if (listing.images && listing.images.length > 0) {
    const rawIndex = listing.featured_image_index;
    const featuredIndex = parseInt(rawIndex || 0);
    setTimeout(() => {
      initSlider(listing.images, 'detailSlider', featuredIndex);
    }, 100);
  }

  // Odağı modal'a ver
  detailModal.focus();

  document.getElementById('detailCloseBtn').addEventListener('click', closeDetailPanel);
  
  detailModal.addEventListener('click', (e) => {
    if (e.target.id === 'detailModal') {
      closeDetailPanel();
    }
  });
}

/**
 * Şehir listesini yükle ve özel dropdown'a doldur
 */
let citiesData = [];

function loadCities() {
  try {
    const ajaxUrl = (window.ajaxurl) || (window.ativAjaxUrl) || '/wp-admin/admin-ajax.php';
    fetch(`${ajaxUrl}?action=ativ_get_cities`)
      .then(r => r.json())
      .then(json => {
        if (!json || !json.success || !Array.isArray(json.data)) return;
        citiesData = json.data;
        setupCityDropdown();
      })
      .catch(() => {});
  } catch (e) {}
}

function setupCityDropdown() {
  const input = document.getElementById('formLocation');
  const dropdown = document.getElementById('cityDropdown');
  if (!input || !dropdown) return;

  // Input'a yazınca filtreleme yap
  input.addEventListener('input', function() {
    const query = this.value.toLowerCase().trim();
    if (!query) {
      dropdown.classList.remove('active');
      return;
    }

    const filtered = citiesData.filter(c => 
      c.il_adi.toLowerCase().includes(query)
    );

    if (filtered.length === 0) {
      dropdown.innerHTML = '<div class="city-dropdown-empty">Şehir bulunamadı</div>';
      dropdown.classList.add('active');
      return;
    }

    dropdown.innerHTML = filtered.map(c => 
      `<div class="city-dropdown-item" data-city="${c.il_adi}">${c.il_adi}</div>`
    ).join('');
    dropdown.classList.add('active');

    // Tıklama olaylarını ekle
    dropdown.querySelectorAll('.city-dropdown-item').forEach(item => {
      item.addEventListener('click', function() {
        input.value = this.dataset.city;
        dropdown.classList.remove('active');
      });
    });
  });

  // Input focus olunca tüm şehirleri göster
  input.addEventListener('focus', function() {
    if (citiesData.length > 0 && !this.value) {
      dropdown.innerHTML = citiesData.map(c => 
        `<div class="city-dropdown-item" data-city="${c.il_adi}">${c.il_adi}</div>`
      ).join('');
      dropdown.classList.add('active');

      dropdown.querySelectorAll('.city-dropdown-item').forEach(item => {
        item.addEventListener('click', function() {
          input.value = this.dataset.city;
          dropdown.classList.remove('active');
        });
      });
    }
  });

  // Dışarı tıklayınca kapat
  document.addEventListener('click', function(e) {
    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.remove('active');
    }
  });
}

/**
 * Kategori dropdown'ını ayarlar
 */
function setupCategoryDropdown() {
  console.log('📦 setupCategoryDropdown() called');
  const categories = [
    { value: 'transceiver', label: '📻 Telsiz' },
    { value: 'antenna', label: '📡 Anten' },
    { value: 'amplifier', label: '🔊 Amplifikatör' },
    { value: 'accessory', label: '🔌 Aksesuar' },
    { value: 'other', label: '📦 Diğer' }
  ];

  const input = document.getElementById('formCategory');
  const dropdown = document.getElementById('formCategoryDropdown');
  console.log('Elements found:', { input: !!input, dropdown: !!dropdown });
  if (!input || !dropdown) {
    console.log('❌ Category input or dropdown not found');
    return;
  }

  // Hidden input oluştur (gerçek değer için)
  let hiddenInput = document.getElementById('formCategoryValue');
  if (!hiddenInput) {
    hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.id = 'formCategoryValue';
    hiddenInput.name = 'category';
    input.parentNode.appendChild(hiddenInput);
  }

  // Dropdown içeriğini oluştur
  dropdown.innerHTML = categories.map(cat => 
    `<div class="category-dropdown-item" data-value="${cat.value}">${cat.label}</div>`
  ).join('');

  // Input'a tıklayınca dropdown'ı aç/kapat
  const inputClickHandler = function(e) {
    e.preventDefault();
    e.stopPropagation();
    dropdown.classList.toggle('active');
  };
  
  // Eski handler varsa kaldır
  if (input._categoryClickHandler) {
    input.removeEventListener('click', input._categoryClickHandler);
  }
  input._categoryClickHandler = inputClickHandler;
  input.addEventListener('click', inputClickHandler);

  // Dropdown item'lara tıklama
  dropdown.querySelectorAll('.category-dropdown-item').forEach(item => {
    item.addEventListener('click', function(e) {
      e.stopPropagation();
      const value = this.dataset.value;
      const label = this.textContent;
      input.value = label;
      hiddenInput.value = value;
      dropdown.classList.remove('active');
    });
  });

  // Dışarı tıklayınca kapat
  const outsideClickHandler = function(e) {
    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.remove('active');
    }
  };
  
  // Eski handler varsa kaldır
  if (window._categoryOutsideClickHandler) {
    document.removeEventListener('click', window._categoryOutsideClickHandler);
  }
  window._categoryOutsideClickHandler = outsideClickHandler;
  document.addEventListener('click', outsideClickHandler);
  
  console.log('✅ Category dropdown ready, current values:', {
    visible: input.value,
    hidden: hiddenInput.value
  });
}

/**
 * Durum dropdown'ını ayarlar
 */
function setupConditionDropdown() {
  console.log('🔧 setupConditionDropdown() called');
  const conditions = [
    { value: 'Sıfır', label: '✨ Sıfır' },
    { value: 'Kullanılmış', label: '♻️ Kullanılmış' },
    { value: 'Arızalı', label: '⚠️ Arızalı' },
    { value: 'El Yapımı', label: '🛠️ El Yapımı' }
  ];

  const input = document.getElementById('formCondition');
  const dropdown = document.getElementById('formConditionDropdown');
  console.log('Elements found:', { input: !!input, dropdown: !!dropdown });
  if (!input || !dropdown) {
    console.log('❌ Condition input or dropdown not found');
    return;
  }

  // Hidden input oluştur (gerçek değer için)
  let hiddenInput = document.getElementById('formConditionValue');
  if (!hiddenInput) {
    hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.id = 'formConditionValue';
    hiddenInput.name = 'condition';
    input.parentNode.appendChild(hiddenInput);
  }

  // Dropdown içeriğini oluştur
  dropdown.innerHTML = conditions.map(cond => 
    `<div class="condition-dropdown-item" data-value="${cond.value}">${cond.label}</div>`
  ).join('');

  // Input'a tıklayınca dropdown'ı aç/kapat
  const inputClickHandler = function(e) {
    e.preventDefault();
    e.stopPropagation();
    dropdown.classList.toggle('active');
  };
  
  // Eski handler varsa kaldır
  if (input._conditionClickHandler) {
    input.removeEventListener('click', input._conditionClickHandler);
  }
  input._conditionClickHandler = inputClickHandler;
  input.addEventListener('click', inputClickHandler);

  // Dropdown item'lara tıklama
  dropdown.querySelectorAll('.condition-dropdown-item').forEach(item => {
    item.addEventListener('click', function(e) {
      e.stopPropagation();
      const value = this.dataset.value;
      const label = this.textContent;
      input.value = label;
      hiddenInput.value = value;
      dropdown.classList.remove('active');
    });
  });

  // Dışarı tıklayınca kapat
  const outsideClickHandler = function(e) {
    if (!input.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.remove('active');
    }
  };
  
  // Eski handler varsa kaldır
  if (window._conditionOutsideClickHandler) {
    document.removeEventListener('click', window._conditionOutsideClickHandler);
  }
  window._conditionOutsideClickHandler = outsideClickHandler;
  document.addEventListener('click', outsideClickHandler);
  
  console.log('✅ Condition dropdown ready, current values:', {
    visible: input.value,
    hidden: hiddenInput.value
  });
}

/**
 * Detay bölümlerini oluşturur
 */
function createDetailSections(listing) {
  // Kontakt linkleri
  const rawPhone = listing.seller_phone || '';
  // Telefon numarasını parse et (artık ülke kodu dahil gelecek: "+90 5551234567")
  let waNumber = '';
  if (rawPhone) {
    // Sadece rakamları al (+ işareti ve boşlukları kaldır)
    waNumber = String(rawPhone).replace(/\D/g, '');
  }
  const msg = `Merhaba ${listing.seller_name || ''}, ${listing.title || 'ilanınız'} hakkında iletişime geçmek istiyorum.`;
  const waLink = waNumber ? `https://wa.me/${waNumber}?text=${encodeURIComponent(msg)}` : '';
  const mailSubject = `İlan Hk.: ${listing.title || ''}`;
  const mailBody = `${msg}\n\nÇağrı İşareti: ${listing.callsign || ''}`;
  const mailLink = listing.seller_email ? `mailto:${listing.seller_email}?subject=${encodeURIComponent(mailSubject)}&body=${encodeURIComponent(mailBody)}` : '';

  return `
    <div class="product-details">
      <h3>Ürün Bilgileri</h3>
      <div class="detail-info">
        <div class="detail-label">Marka</div>
        <div class="detail-value">${escapeHtml(listing.brand)}</div>
      </div>
      <div class="detail-info">
        <div class="detail-label">Model</div>
        <div class="detail-value">${escapeHtml(listing.model)}</div>
      </div>
      <div class="detail-info">
        <div class="detail-label">Durum</div>
        <div class="detail-value">${escapeHtml(listing.condition)}</div>
      </div>
      <div class="detail-info">
        <div class="detail-label">Kategori</div>
        <div class="detail-value">${getCategoryName(listing.category)}</div>
      </div>
      <div class="detail-info">
        <div class="detail-label">Fiyat</div>
        <div class="detail-value">${getCurrencySymbol(listing.currency || 'TRY')}${escapeHtml(String(listing.price))} ${escapeHtml(listing.currency || 'TRY')}</div>
      </div>
    </div>
    <div class="product-details">
      <h3>Açıklama</h3>
      <div class="detail-description">
        <p>${escapeHtml(listing.description)}</p>
      </div>
    </div>
    ${listing.video ? `
    <div class="product-details">
      <h3>📹 Ürün Videosu</h3>
      <div style="margin-top: 12px; border-radius: 8px; overflow: hidden; background: #000;">
        <video width="100%" height="300" controls style="display: block;">
          <source src="${listing.video}" type="video/mp4">
          Tarayıcınız video oynatmayı desteklemiyor
        </video>
      </div>
    </div>
    ` : ''}
    <div class="seller-section">
      <h3>Satıcı Bilgileri</h3>
      <div class="detail-info">
        <div class="detail-label">Ad Soyad</div>
        <div class="detail-value">${escapeHtml(listing.seller_name)}</div>
      </div>
      <div class="detail-info">
        <div class="detail-label">Çağrı İşareti</div>
        <div class="detail-value">${escapeHtml(listing.callsign)}</div>
      </div>
      <div class="detail-info">
        <div class="detail-label">Konum</div>
        <div class="detail-value">${escapeHtml(listing.location)}</div>
      </div>
      <div class="detail-info">
        <div class="detail-label">E-posta</div>
        <div class="detail-value">${escapeHtml(listing.seller_email)}</div>
      </div>
      <div class="detail-info">
        <div class="detail-label">Telefon</div>
        <div class="detail-value">${escapeHtml(listing.seller_phone)}</div>
      </div>
      <div class="detail-actions" style="display:flex; gap:12px; margin-top:16px; flex-wrap:wrap;">
        ${waLink ? `<a href="${waLink}" target="_blank" rel="noopener" class="contact-btn whatsapp" style="display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:8px; background:#25D366; color:#fff; text-decoration:none; font-weight:600;">💬 WhatsApp'tan Yaz</a>` : ''}
        ${mailLink ? `<a href="${mailLink}" class="contact-btn email" style="display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:8px; background:#667eea; color:#fff; text-decoration:none; font-weight:600;">✉️ E-posta Gönder</a>` : ''}
        <button onclick="openShareModalForListing('${escapeHtml(listing.title)}', '${escapeHtml(String(listing.price))}', '${escapeHtml(listing.currency || 'TRY')}', '${escapeHtml(listing.category_name || '')}', ${listing.id})" class="contact-btn share" style="display:inline-flex; align-items:center; gap:8px; padding:10px 14px; border-radius:8px; background:#8b5cf6; color:#fff; border:none; cursor:pointer; font-weight:600;">🔗 İlanı Paylaş</button>
      </div>
    </div>
  `;
}

/**
 * Detay panelini kapatır
 */
function closeDetailPanel() {
  const detailModal = document.getElementById('detailModal');
  if (detailModal) {
    detailModal.remove();
    document.body.style.overflow = 'auto';
  }
  selectedListing = null;
}

// Global window atamaları - inline onclick handler'lar için gerekli
window.closeLightbox = closeLightbox;
window.changeLightboxSlide = changeLightboxSlide;
window.goToLightboxSlide = goToLightboxSlide;
window.toggleZoom = toggleZoom;
window.openLightbox = openLightbox;
window.changeSlide = changeSlide;

/**
 * "Benim İlanlarım" sayfasında silme işlemi
 */
async function handleMyListingDelete(id) {
  if (!confirm('Silmek istediğinizden emin misiniz?')) {
    return;
  }

  try {
    await deleteListing(id);
    
    // Silme başarılı, grid'i yenile
    if (typeof pageType !== 'undefined' && pageType === 'my-listings') {
      await refreshMyListingsGrid();
    }
  } catch (error) {
    console.error('İlan silinirken hata:', error);
    alert('İlan silinirken bir hata oluştu. Lütfen tekrar deneyin.');
  }
}

// My-listings filtreleri için genel depolama
let myListingsCache = [];

/**
 * My-listings filtreleri uygulanır
 */
async function applyMyListingsFilters() {
  // Eğer cache boşsa, önce refresh et
  if (myListingsCache.length === 0) {
    await refreshMyListingsGrid();
    return;
  }
  
  let filtered = [...myListingsCache];
  
  // Durum filtrelemesi - dropdown'dan al
  const statusOption = document.querySelector('#statusFilterOptions .dropdown-option.selected');
  const statusFilter = statusOption?.dataset?.value || '';
  if (statusFilter) {
    filtered = filtered.filter(l => l.status === statusFilter);
  }
  
  // Fiyat sıralaması - dropdown'dan al (TL karşılığı üzerinden sırala, display'de orijinal para birimi göster)
  const priceOption = document.querySelector('#priceSortOptions .dropdown-option.selected');
  const priceSort = priceOption?.dataset?.value || '';
  if (priceSort === 'price-asc') {
    filtered.sort((a, b) => parseFloat(a.price_in_tl || 0) - parseFloat(b.price_in_tl || 0));
  } else if (priceSort === 'price-desc') {
    filtered.sort((a, b) => parseFloat(b.price_in_tl || 0) - parseFloat(a.price_in_tl || 0));
  }
  
  // Tarih sıralaması - dropdown'dan al
  const dateOption = document.querySelector('#dateSortOptions .dropdown-option.selected');
  const dateSort = dateOption?.dataset?.value || '';
  if (dateSort === 'newest') {
    filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
  } else if (dateSort === 'oldest') {
    filtered.sort((a, b) => new Date(a.created_at) - new Date(b.created_at));
  }
  
  // HTML oluştur ve göster
  renderMyListingsGrid(filtered);
}

/**
 * My-listings grid'i render et
 */
function renderMyListingsGrid(listings) {
  const grid = document.getElementById('myListingsGrid');
  if (!grid) return;
  
  if (listings.length === 0) {
    grid.innerHTML = '<div class="no-results">Seçtiğiniz kriterlere uygun ilan bulunamadı.</div>';
  } else {
    grid.innerHTML = listings.map(listing => {
      const imageUrl = getListingImageUrl(listing);
      const statusBadge = listing.status === 'rejected' ? '❌ Reddedildi' : 
                          listing.status === 'pending' ? '⏳ Beklemede' : 
                          '✅ Onaylı';
      const statusColor = listing.status === 'rejected' ? '#dc3545' : 
                         listing.status === 'pending' ? '#ffc107' : 
                         '#28a745';
      
      return `
        <div class="listing-row-wrapper" style="display: flex; flex-direction: column;">
          <div class="listing-row" data-listing-id="${listing.id}" style="position: relative; border: 2px solid ${listing.status === 'rejected' ? '#dc3545' : (listing.status === 'pending' ? '#ffc107' : '#28a745')}; border-radius: 4px; display: flex; flex-wrap: wrap; cursor: pointer;" onclick="toggleListingDetails(this.querySelector('.listing-row-title'))">
            <div class="listing-row-image">
              ${imageUrl ? `<img src="${imageUrl}" alt="${escapeHtml(listing.title)}">` : `<div class="listing-row-image-fallback">${escapeHtml(listing.emoji || '📻')}</div>`}
            </div>
            <div class="listing-row-info" style="flex: 1; min-width: 0; overflow-wrap: break-word; word-wrap: break-word;">
              <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                <h3 class="listing-row-title" style="cursor: pointer; margin: 0;">${escapeHtml(listing.title)}</h3>
                <span style="background: ${statusColor}; color: white; font-size: 11px; padding: 4px 8px; border-radius: 12px; white-space: nowrap; font-weight: bold;">${statusBadge}</span>
              </div>
              
              ${listing.status === 'rejected' ? `
                <div style="background: #ffebee; border-left: 3px solid #dc3545; padding: 10px; margin-bottom: 8px; border-radius: 2px; word-wrap: break-word; overflow-wrap: break-word;">
                  <div style="color: #721c24; font-size: 12px; font-weight: bold; margin-bottom: 5px;">Red Nedeni:</div>
                  <div style="color: #721c24; font-size: 12px; margin-bottom: 8px; word-wrap: break-word; overflow-wrap: break-word; white-space: pre-wrap;">
                    ${escapeHtml(listing.rejection_reason || 'Neden belirtilmemiş').replace(/\n/g, '<br>')}
                  </div>
                  <div style="background: #fff3cd; border-left: 2px solid #ff9800; padding: 8px; border-radius: 2px; margin-top: 8px; word-wrap: break-word; overflow-wrap: break-word;">
                    <div style="color: #856404; font-size: 12px;">
                      💡 <strong>İlanınızı düzenleyip tekrar gönderin.</strong> Aşağıdaki "Düzenle" butonuna tıklayarak değişiklikler yapabilirsiniz.
                    </div>
                  </div>
                </div>
              ` : listing.status === 'pending' ? `
                <div style="background: #fffbf0; border-left: 3px solid #ffc107; padding: 8px 10px; margin-bottom: 8px; border-radius: 2px; word-wrap: break-word; overflow-wrap: break-word;">
                  <div style="color: #856404; font-size: 12px;">
                    ⏳ <strong>Yönetici incelemesinde...</strong> İlanınızı düzenleyebilirsiniz.
                  </div>
                </div>
              ` : ''}
              
              <p class="listing-row-category">${getCategoryName(listing.category)} • ${escapeHtml(listing.condition)}</p>
              <p class="listing-row-details">${escapeHtml(listing.brand)} ${escapeHtml(listing.model)} • ${escapeHtml(listing.callsign)}</p>
              <p class="listing-row-date">Yayınlanma: ${formatDate(listing.created_at)}</p>
            </div>
            <div class="listing-row-price">
              <div class="price-amount">${escapeHtml(String(listing.price))} ${escapeHtml(listing.currency || 'TRY')}</div>
            </div>
            <div class="listing-row-actions">
              ${listing.status === 'rejected' || listing.status === 'pending' ? `
                <button class="action-btn edit-btn" onclick="event.stopPropagation(); window.editMyListing(${parseInt(listing.id)})" title="Düzenle">✏️ Düzenle</button>
              ` : `
                <button class="action-btn edit-btn" onclick="event.stopPropagation(); window.editListing(${parseInt(listing.id)})" title="Düzenle">✏️ Düzenle</button>
              `}
              <button class="action-btn delete-btn" onclick="event.stopPropagation(); window.confirmDeleteListing(${parseInt(listing.id)})" title="Sil">🗑️ Sil</button>
            </div>
          </div>
          <div class="listing-row-details-expanded">
            <div class="listing-details-content">
              <div class="details-section">
                <h4>Ürün Açıklaması</h4>
                <p>${escapeHtml(listing.description || '').replace(/\n/g, '<br>')}</p>
              </div>
              <div class="details-grid">
                <div class="detail-item">
                  <span class="detail-label">Kategori:</span>
                  <span class="detail-value">${getCategoryName(listing.category)}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Durum:</span>
                  <span class="detail-value">${escapeHtml(listing.condition)}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Marka:</span>
                  <span class="detail-value">${escapeHtml(listing.brand)}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Model:</span>
                  <span class="detail-value">${escapeHtml(listing.model)}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Fiyat:</span>
                  <span class="detail-value">${escapeHtml(String(listing.price))} ${escapeHtml(listing.currency || 'TRY')}</span>
                </div>
                <div class="detail-item">
                  <span class="detail-label">Konum:</span>
                  <span class="detail-value">${escapeHtml(listing.location)}</span>
                </div>
              </div>
              <div class="details-section">
                <h4>Satıcı Bilgileri</h4>
                <div class="seller-info">
                  <p><strong>${escapeHtml(listing.seller_name)}</strong></p>
                  <p>Çağrı İşareti: ${escapeHtml(listing.callsign)}</p>
                  <p>E-posta: ${escapeHtml(listing.seller_email)}</p>
                  <p>Telefon: ${escapeHtml(formatPhoneForDisplay(listing.seller_phone))}</p>
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
    }).join('');
  }
}

/**
 * "Benim İlanlarım" sayfasını yenile
 */
async function refreshMyListingsGrid() {
  try {
    const gridElement = document.getElementById('myListingsGrid');
    
    if (!gridElement) {
      return;
    }

    const data = new FormData();
    data.append('action', 'ativ_ajax');
    data.append('action_type', 'get_user_listings');
    data.append('nonce', ativ_ajax.nonce);

    const response = await fetch(ativ_ajax.url, {
      method: 'POST',
      body: data
    });

    const result = await response.json();

    if (result.success && result.data) {
      const listings = result.data;
      // Cache'e kaydet
      myListingsCache = listings;
      // Filtreleme ve sıralama uygulanarak render et
      applyMyListingsFilters();
    } else {
      console.error('İlanlar yenilenirken hata - success false:', result);
    }
  } catch (error) {
    console.error('İlanlar yenilenirken AJAX hatası:', error);
  }
}

/**
 * Tarihi formatla
 */
function formatDate(dateString) {
  const date = new Date(dateString);
  return date.toLocaleDateString('tr-TR', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
    hour: '2-digit',
    minute: '2-digit'
  });
}

/**
 * İlan resim URL'sini döndür
 */
function getListingImageUrl(listing) {
  if (!listing.images || listing.images.length === 0) {
    return '';
  }
  
  const featuredIndex = parseInt(listing.featured_image_index || 0);
  const image = listing.images[featuredIndex] || listing.images[0];
  
  if (image.data) {
    return image.data;
  } else if (image.name) {
    return ativ_ajax.upload_url + listing.id + '/' + image.name;
  }
  
  return '';
}

// escapeHtml fonksiyonu core.js'de tanımlı - XSS koruması için

window.goToSlide = goToSlide;
window.initSlider = initSlider;
window.openDetailPanel = openDetailPanel;
window.closeDetailPanel = closeDetailPanel;
window.handleMyListingDelete = handleMyListingDelete;

// My-listings sayfası için red edilen ilanı düzenle
window.editMyListing = async function(id) {
  try {
    const response = await fetch(ativ_ajax.url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: 'action=ativ_ajax&action_type=get_user_listings&nonce=' + ativ_ajax.nonce
    });
    
    const data = await response.json();
    
    if (data.success && Array.isArray(data.data)) {
      const listing = data.data.find(l => Number(l.id) === Number(id));
      
      if (listing) {
        // Görselleri formatla
        if (listing.images) {
          listing.images = listing.images.map(img => ({
            data: img.data || img.name,
            name: img.name
          }));
        }
        
        // Düzenleme modu ve red/onaylı flag'ini ayarla
        editingListing = Object.assign({}, listing);
        isEditingRejectedListing = (listing.status === 'rejected' || listing.status === 'approved');
        
        // Kategori ve durum mapping
        const categoryMapping = {
          'transceiver': '📻 Telsiz',
          'antenna': '📡 Anten',
          'amplifier': '🔊 Amplifikatör',
          'accessory': '🔌 Aksesuar',
          'other': '📦 Diğer'
        };
        
        const conditionMapping = {
          'Sıfır': '✨ Sıfır',
          'Kullanılmış': '♻️ Kullanılmış',
          'Arızalı': '⚠️ Arızalı',
          'El Yapımı': '🔧 El Yapımı'
        };
        
        const categoryValue = listing.category || '';
        const categoryLabel = categoryMapping[categoryValue] || categoryValue;
        const conditionValue = listing.condition || '';
        const conditionLabel = conditionMapping[conditionValue] || conditionValue;
        
        // Form alanlarını doldur
        document.getElementById('formTitle').value = listing.title || '';
        document.getElementById('formBrand').value = listing.brand || '';
        document.getElementById('formModel').value = listing.model || '';
        document.getElementById('formPrice').value = listing.price || '';
        document.getElementById('formCurrency').value = listing.currency || 'TRY';
        document.getElementById('formDescription').value = listing.description || '';
        document.getElementById('formCallsign').value = listing.callsign || '';
        document.getElementById('formSellerName').value = listing.seller_name || '';
        document.getElementById('formLocation').value = listing.location || '';
        document.getElementById('formEmail').value = listing.seller_email || '';
        
        // Dropdown'ları kur
        setupCategoryDropdown();
        setupConditionDropdown();
        
        // Kategori ve durum değerlerini set et (dropdown kurulduktan sonra)
        const categoryInput = document.getElementById('formCategory');
        const categoryHidden = document.getElementById('formCategoryValue');
        const conditionInput = document.getElementById('formCondition');
        const conditionHidden = document.getElementById('formConditionValue');
        
        if (categoryInput) categoryInput.value = categoryLabel;
        if (categoryHidden) categoryHidden.value = categoryValue;
        if (conditionInput) conditionInput.value = conditionLabel;
        if (conditionHidden) conditionHidden.value = conditionValue;
        
        // Telefonu parse et ve alanları doldur
        const phoneData = parsePhoneNumber(listing.seller_phone || '');
        populateCountryCodes(phoneData.dialCode);
        document.getElementById('formPhone').value = formatPhoneNumber(phoneData.number);
        
        // Modal başlığı ve submit butonunu özelleştir
        document.querySelector('.modal-header h2').textContent = 'Red Edilen İlanı Düzenle';
        document.getElementById('formSubmitBtn').textContent = 'Güncelle ve Tekrar Onaya Gönder';
        
        // Görselleri yükle
        uploadedImages = listing.images || [];
        featuredImageIndex = Math.max(0, parseInt(listing.featured_image_index || 0));
        renderImagePreviews();
        updatePreview();
        
        // Video preview (eğer varsa)
        if (listing.video) {
          const videoPreview = document.getElementById('videoPreviewContainer');
          const videoStatus = document.getElementById('videoStatusHint');
          if (videoPreview) {
            videoPreview.innerHTML = `
              <div style="position: relative; border-radius: 8px; overflow: hidden; background: #000;">
                <video width="100%" height="200" controls style="display: block;">
                  <source src="${listing.video}" type="video/mp4">
                </video>
                <button type="button" onclick="removeVideo()" style="position: absolute; top: 8px; right: 8px; background: rgba(255,0,0,0.8); color: white; border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 12px;">Kaldır</button>
              </div>
            `;
          }
          if (videoStatus) {
            videoStatus.innerHTML = '✅ Video mevcut (Yeni video seçerek değiştirebilirsiniz)';
            videoStatus.style.color = '#2e7d32';
          }
        }
        
        // Modal aç ve buton textini statüye göre ayarla
        document.getElementById('addListingModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
        document.querySelector('.modal-header h2').textContent = 'İlanı Düzenle';
        
        // Buton textini ilanın statüsüne göre ayarla
        const submitBtnText = (listing.status === 'rejected' || listing.status === 'approved') ? 
          'Değişiklikleri Kaydet ve Tekrar Onaya Gönder' : 
          'Değişiklikleri Kaydet';
        document.getElementById('formSubmitBtn').textContent = submitBtnText;
        
        return;
      }
    }
    alert('İlan yüklenemedi');
  } catch (e) {
    console.error('İlan yüklenirken hata:', e);
    alert('İlan yüklenirken hata oluştu');
  }
};
window.refreshMyListingsGrid = refreshMyListingsGrid;

/**
 * Video handling - Format ve süre validasyonu
 */
const videoFormats = ['video/mp4', 'video/webm'];
const videoExtensions = ['mp4', 'webm'];
const maxVideoDuration = 300; // 5 dakika (saniye)
const maxVideoSize = 150 * 1024 * 1024; // 150MB

let selectedVideoFile = null;
let uploadedVideoTempPath = null; // Temp klasörde yüklenen video URL'si
let videoUploadInProgress = false;

function setupVideoHandling() {
  const videoInput = document.getElementById('formVideo');
  if (!videoInput) return;
  
  videoInput.addEventListener('change', handleVideoSelect);
  
  // Drag and drop için
  const videoLabel = videoInput.parentElement;
  if (videoLabel) {
    videoLabel.addEventListener('dragover', (e) => {
      e.preventDefault();
      videoLabel.classList.add('dragover');
    });
    videoLabel.addEventListener('dragleave', () => {
      videoLabel.classList.remove('dragover');
    });
    videoLabel.addEventListener('drop', (e) => {
      e.preventDefault();
      videoLabel.classList.remove('dragover');
      if (e.dataTransfer.files.length > 0) {
        videoInput.files = e.dataTransfer.files;
        handleVideoSelect({ target: videoInput });
      }
    });
  }
}

async function handleVideoSelect(e) {
  const file = e.target.files[0];
  const statusHint = document.getElementById('videoStatusHint');
  const previewContainer = document.getElementById('videoPreviewContainer');
  const submitBtn = document.getElementById('submitListingBtn');
  
  if (!file) {
    selectedVideoFile = null;
    uploadedVideoTempPath = null;
    videoUploadInProgress = false;
    previewContainer.innerHTML = '';
    if (statusHint) statusHint.innerHTML = '';
    if (submitBtn) submitBtn.disabled = false;
    return;
  }
  
  // Dosya adı kontrolü
  const fileName = file.name.toLowerCase();
  const fileExt = fileName.split('.').pop();
  
  if (!videoExtensions.includes(fileExt)) {
    statusHint.innerHTML = '❌ Hata: Sadece .mp4 ve .webm uzantıları desteklenir';
    statusHint.style.color = '#d32f2f';
    previewContainer.innerHTML = '';
    selectedVideoFile = null;
    uploadedVideoTempPath = null;
    e.target.value = '';
    return;
  }
  
  // MIME Type kontrolü
  if (!videoFormats.includes(file.type)) {
    statusHint.innerHTML = '❌ Hata: Sadece MP4 ve WebM formatları desteklenir';
    statusHint.style.color = '#d32f2f';
    previewContainer.innerHTML = '';
    selectedVideoFile = null;
    uploadedVideoTempPath = null;
    e.target.value = '';
    return;
  }
  
  // Boyut kontrol
  if (file.size > maxVideoSize) {
    statusHint.innerHTML = `❌ Hata: Dosya çok büyük (Max 150MB, Seçilen: ${(file.size / 1024 / 1024).toFixed(2)}MB)`;
    statusHint.style.color = '#d32f2f';
    previewContainer.innerHTML = '';
    selectedVideoFile = null;
    uploadedVideoTempPath = null;
    e.target.value = '';
    return;
  }
  
  // Süre kontrol - video metadata'sı yükleneceğini bekle
  const video = document.createElement('video');
  video.preload = 'metadata';
  
  video.onloadedmetadata = async () => {
    const duration = Math.floor(video.duration);
    
    if (duration > maxVideoDuration) {
      statusHint.innerHTML = `❌ Hata: Video çok uzun (Max 5 dakika, Seçilen: ${formatDuration(duration)})`;
      statusHint.style.color = '#d32f2f';
      previewContainer.innerHTML = '';
      selectedVideoFile = null;
      uploadedVideoTempPath = null;
      URL.revokeObjectURL(video.src);
      return;
    }
    
    // Tüm kontroller passed - Önce local preview göster, sonra arka planda yükle
    selectedVideoFile = file;
    videoUploadInProgress = true;
    
    // Submit butonunu devre dışı bırak
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="loading-spinner"></span>Video yükleniyor...';
    }
    
    // Progress bar göster
    statusHint.innerHTML = '📤 Video yükleniyor... 0%';
    statusHint.style.color = '#1976d2';
    
    // Önce local blob URL ile preview göster (anında görünür)
    const blobUrl = URL.createObjectURL(file);
    
    previewContainer.innerHTML = `
      <div style="position: relative; border-radius: 8px; overflow: hidden; background: #000;">
        <video width="100%" height="200" controls style="display: block;">
          <source src="${blobUrl}" type="${file.type}">
        </video>
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: rgba(0,0,0,0.7); padding: 12px 20px; border-radius: 8px; pointer-events: none;">
          <div class="upload-progress-bar" style="width: 200px; margin-bottom: 8px;">
            <div class="upload-progress-fill" id="videoProgressFill" style="width: 0%"></div>
          </div>
          <p id="videoUploadStatus" style="color: white; font-size: 13px; margin: 0; text-align: center;">Yükleniyor... 0%</p>
        </div>
      </div>
    `;
    
    try {
      // Video'yu arka planda temp klasöre yükle
      const tempUrl = await uploadVideoToTemp(file);
      uploadedVideoTempPath = tempUrl;
      videoUploadInProgress = false;
      
      // Başarılı upload - progress göstergesini kaldır
      statusHint.innerHTML = `✅ Video hazır: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)}MB, ${formatDuration(duration)})`;
      statusHint.style.color = '#2e7d32';
      
      // Preview'ı güncelle - progress göstergesini kaldır
      previewContainer.innerHTML = `
        <div style="position: relative; border-radius: 8px; overflow: hidden; background: #000;">
          <video width="100%" height="200" controls style="display: block;">
            <source src="${blobUrl}" type="${file.type}">
          </video>
          <button type="button" onclick="removeVideo()" style="position: absolute; top: 8px; right: 8px; background: rgba(255,0,0,0.8); color: white; border: none; border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 12px;">Kaldır</button>
        </div>
      `;
      
      // Submit butonunu aktif et
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = editingListing ? 'Değişiklikleri Kaydet' : 'İlanı Yayınla';
      }
      
    } catch (error) {
      console.error('Video yükleme hatası:', error);
      videoUploadInProgress = false;
      uploadedVideoTempPath = null;
      selectedVideoFile = null;
      URL.revokeObjectURL(blobUrl);
      
      statusHint.innerHTML = `❌ Video yüklenemedi: ${error.message}`;
      statusHint.style.color = '#d32f2f';
      previewContainer.innerHTML = '';
      
      // Submit butonunu aktif et
      if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.textContent = editingListing ? 'Değişiklikleri Kaydet' : 'İlanı Yayınla';
      }
    }
  };
  
  video.onerror = () => {
    statusHint.innerHTML = '❌ Hata: Video dosyası okunurken hata oluştu';
    statusHint.style.color = '#d32f2f';
    previewContainer.innerHTML = '';
    selectedVideoFile = null;
    uploadedVideoTempPath = null;
  };
  
  video.src = URL.createObjectURL(file);
}

function formatDuration(seconds) {
  const m = Math.floor(seconds / 60);
  const s = seconds % 60;
  return `${m}:${String(s).padStart(2, '0')}`;
}

function removeVideo() {
  const videoInput = document.getElementById('formVideo');
  const previewContainer = document.getElementById('videoPreviewContainer');
  const statusHint = document.getElementById('videoStatusHint');
  
  // Temp video varsa sil
  if (uploadedVideoTempPath) {
    deleteTempVideo(uploadedVideoTempPath);
  }
  
  videoInput.value = '';
  selectedVideoFile = null;
  uploadedVideoTempPath = null;
  videoUploadInProgress = false;
  previewContainer.innerHTML = '';
  if (statusHint) statusHint.innerHTML = '';
}

window.removeVideo = removeVideo;

/**
 * Temp klasördeki videoyu sil
 */
async function deleteTempVideo(tempUrl) {
  if (!tempUrl) return;
  
  try {
    const response = await fetch(ativ_ajax.url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({
        action: 'ativ_ajax',
        action_type: 'delete_video_temp',
        nonce: ativ_ajax.nonce,
        temp_url: tempUrl
      })
    });
    
    const data = await response.json();
    if (data.success) {
      console.log('✅ Temp video silindi');
    }
  } catch (error) {
    console.error('Temp video silme hatası:', error);
  }
}

/**
 * Sayfa kapatılırken veya yenilenirken temp video'yu temizle
 */
window.addEventListener('beforeunload', (e) => {
  if (uploadedVideoTempPath) {
    // Navigator.sendBeacon ile async istek gönder (sayfa kapanırken çalışır)
    const formData = new FormData();
    formData.append('action', 'ativ_ajax');
    formData.append('action_type', 'delete_video_temp');
    formData.append('nonce', ativ_ajax.nonce);
    formData.append('temp_url', uploadedVideoTempPath);
    
    navigator.sendBeacon(ativ_ajax.url, formData);
  }
});

/**
 * Video dosyasını TEMP klasörüne yükle (progress bar ile)
 */
async function uploadVideoToTemp(file) {
  return new Promise((resolve, reject) => {
    const formData = new FormData();
    formData.append('action', 'ativ_ajax');
    formData.append('action_type', 'upload_video_temp');
    formData.append('nonce', ativ_ajax.nonce);
    formData.append('video', file);
    
    const xhr = new XMLHttpRequest();
    
    // Progress event - yükleme ilerlemesi
    xhr.upload.addEventListener('progress', (e) => {
      if (e.lengthComputable) {
        const percentComplete = Math.round((e.loaded / e.total) * 100);
        updateVideoUploadProgressInline(percentComplete);
      }
    });
    
    // Load event - işlem tamamlandı
    xhr.addEventListener('load', () => {
      if (xhr.status === 200) {
        try {
          const data = JSON.parse(xhr.responseText);
          
          if (!data.success) {
            reject(new Error(data.data || 'Video yüklenirken hata oluştu'));
          } else {
            console.log('✅ Video temp klasöre yüklendi:', data.data.temp_url);
            resolve(data.data.temp_url);
          }
        } catch (error) {
          reject(new Error('Sunucu yanıtı işlenemedi'));
        }
      } else {
        reject(new Error('Sunucu hatası: ' + xhr.status));
      }
    });
    
    // Error event
    xhr.addEventListener('error', () => {
      reject(new Error('Ağ hatası - video yüklenemedi'));
    });
    
    // Abort event
    xhr.addEventListener('abort', () => {
      reject(new Error('Video yükleme iptal edildi'));
    });
    
    xhr.open('POST', ativ_ajax.url);
    xhr.send(formData);
  });
}

/**
 * Inline video yükleme progress'ini göster (form içinde)
 */
function updateVideoUploadProgressInline(percent) {
  const statusHint = document.getElementById('videoStatusHint');
  const progressFill = document.getElementById('videoProgressFill');
  const uploadStatus = document.getElementById('videoUploadStatus');
  
  if (statusHint) {
    statusHint.innerHTML = `📤 Video yükleniyor... ${percent}%`;
  }
  
  if (progressFill) {
    progressFill.style.width = percent + '%';
  }
  
  if (uploadStatus) {
    uploadStatus.textContent = `Yükleniyor... ${percent}%`;
  }
}

/**
 * Video dosyasını upload et (progress bar ile)
 */
async function uploadVideo(file, listingId) {
  return new Promise((resolve, reject) => {
    const formData = new FormData();
    formData.append('action', 'ativ_ajax');
    formData.append('action_type', 'upload_video');
    formData.append('nonce', ativ_ajax.nonce);
    formData.append('video', file);
    
    if (listingId) {
      formData.append('listing_id', listingId);
    }
    
    const xhr = new XMLHttpRequest();
    
    // Progress event - yükleme ilerlemesi
    xhr.upload.addEventListener('progress', (e) => {
      if (e.lengthComputable) {
        const percentComplete = (e.loaded / e.total) * 100;
        updateVideoUploadProgress(percentComplete);
      }
    });
    
    // Load event - işlem tamamlandı
    xhr.addEventListener('load', () => {
      if (xhr.status === 200) {
        try {
          const data = JSON.parse(xhr.responseText);
          
          if (!data.success) {
            reject(new Error(data.data || 'Video yüklenirken hata oluştu'));
          } else {
            console.log('✅ Video başarıyla yüklendi');
            resolve(data);
          }
        } catch (error) {
          reject(new Error('Sunucu yanıtı işlenemedi'));
        }
      } else {
        reject(new Error('Sunucu hatası: ' + xhr.status));
      }
    });
    
    // Error event
    xhr.addEventListener('error', () => {
      reject(new Error('Ağ hatası - video yüklenemedi'));
    });
    
    // Abort event
    xhr.addEventListener('abort', () => {
      reject(new Error('Video yükleme iptal edildi'));
    });
    
    xhr.open('POST', ativ_ajax.url);
    xhr.send(formData);
  });
}

/**
 * Video yükleme progress'ini göster
 */
function updateVideoUploadProgress(percent) {
  const loadingOverlay = document.querySelector('.modal-loading-overlay');
  if (loadingOverlay) {
    const messageDiv = loadingOverlay.querySelector('.loading-message');
    if (messageDiv) {
      messageDiv.innerHTML = `
        <div class="loading-spinner"></div>
        <p>Video yükleniyor... ${Math.round(percent)}%</p>
        <div class="upload-progress-bar">
          <div class="upload-progress-fill" style="width: ${percent}%"></div>
        </div>
      `;
    }
  }
}

/**
 * Kullanıcının yasaklı olup olmadığını kontrol et
 */
function checkUserBanStatus() {
  console.log('[DEBUG] checkUserBanStatus çağrıldı');
  return new Promise((resolve, reject) => {
    const formData = new FormData();
    formData.append('action', 'check_user_ban');
    
    console.log('[DEBUG] Ban kontrolü AJAX başlatılıyor...');
    
    fetch(ativ_ajax.url, {
      method: 'POST',
      body: formData
    })
    .then(res => {
      console.log('[DEBUG] Ban kontrolü response status:', res.status);
      return res.json();
    })
    .then(data => {
      console.log('[DEBUG] Ban kontrolü response data:', data);
      if (data.success) {
        console.log('[DEBUG] Ban kontrolü başarılı, data:', data.data);
        resolve(data.data);
      } else {
        console.error('[DEBUG] Ban kontrolü başarısız:', data.data);
        reject(new Error(data.data || 'Ban kontrolü başarısız'));
      }
    })
    .catch(error => {
      console.error('[DEBUG] Ban kontrolü fetch hatası:', error);
      reject(error);
    });
  });
}

/**
 * Yasaklı kullanıcı modalını göster
 */
function showBannedUserModal(banReason, bannedAt) {
  console.log('[DEBUG] showBannedUserModal çağrıldı');
  console.log('[DEBUG] banReason:', banReason);
  console.log('[DEBUG] bannedAt:', bannedAt);
  
  const date = bannedAt ? new Date(bannedAt).toLocaleDateString('tr-TR') : 'Bilinmiyor';
  
  // escapeHtml fonksiyonu tanımlı mı kontrol et
  const safeEscape = typeof escapeHtml === 'function' ? escapeHtml : (text => {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  });
  
  const modal = document.createElement('div');
  modal.className = 'login-required-modal-overlay';
  modal.style.cssText = `
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    padding: 20px;
  `;
  
  modal.innerHTML = `
    <div class="login-required-modal-content" style="
      background: white;
      border-radius: 12px;
      padding: 30px;
      max-width: 500px;
      width: 100%;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
      text-align: center;
      animation: modalFadeIn 0.3s ease;
    ">
      <div class="login-required-icon" style="font-size: 64px; margin-bottom: 20px;">🚫</div>
      <h2 style="color: #dc3545; margin-bottom: 10px; font-size: 24px;">Hesabınız Yasaklanmış</h2>
      <p style="color: #666; margin-bottom: 20px; font-size: 14px;">Yasaklanma Tarihi: ${date}</p>
      <div style="background: #f8d7da; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545; margin-bottom: 20px; text-align: left;">
        <strong style="color: #721c24; display: block; margin-bottom: 5px;">Yasaklanma Nedeni:</strong>
        <p style="color: #721c24; margin: 0; white-space: pre-wrap;">${safeEscape(banReason || 'Belirtilmemiş')}</p>
      </div>
      <p style="color: #666; font-size: 14px; margin-bottom: 20px;">
        İlan ekleme yetkiniz kaldırılmıştır. Daha fazla bilgi için site yöneticisi ile iletişime geçiniz.
      </p>
      <button 
        onclick="this.closest('.login-required-modal-overlay').remove(); document.body.style.overflow = 'auto';" 
        style="width: 100%; padding: 12px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 16px; transition: background 0.3s;">
        Anladım
      </button>
    </div>
  `;
  
  console.log('[DEBUG] Modal DOM\'a ekleniyor...');
  document.body.appendChild(modal);
  document.body.style.overflow = 'hidden';
  console.log('[DEBUG] Modal gösterildi');
  console.log('[DEBUG] Modal element:', modal);
  console.log('[DEBUG] Modal computed style:', window.getComputedStyle(modal).display);
}

/**
 * İlan paylaşım modalını açar (galeri modalından)
 */
window.openShareModalForListing = function(title, price, currency, category, listingId) {
  // Modal varsa kaldır
  const existingModal = document.getElementById('shareModalGallery');
  if (existingModal) {
    existingModal.remove();
  }

  const shareUrl = `${window.location.origin}/ilan/${listingId}`;
  const shareText = `${title} - ${price} ${currency}${category ? ' | ' + category : ''}`;
  
  const modal = document.createElement('div');
  modal.id = 'shareModalGallery';
  modal.className = 'share-modal active';
  modal.style.zIndex = '9999999'; // Detay modalının üstünde
  
  modal.innerHTML = `
    <div class="share-modal-content">
      <div class="share-modal-header">
        <h3 class="share-modal-title">İlanı Paylaş</h3>
        <button class="share-modal-close" onclick="closeShareModalGallery()">&times;</button>
      </div>
      <div class="share-buttons">
        <button class="share-btn share-btn-whatsapp" onclick="shareToWhatsAppGallery('${shareText.replace(/'/g, "\\'")}', '${shareUrl}')">
          📱 WhatsApp
        </button>
        <button class="share-btn share-btn-telegram" onclick="shareToTelegramGallery('${shareText.replace(/'/g, "\\'")}', '${shareUrl}')">
          ✈️ Telegram
        </button>
        <button class="share-btn share-btn-messenger" onclick="shareToMessengerGallery('${shareUrl}')">
          💬 Messenger
        </button>
        <button class="share-btn share-btn-copy" onclick="copyListingUrlGallery('${shareText.replace(/'/g, "\\'")}', '${shareUrl}')">
          🔗 URL Kopyala
        </button>
      </div>
      <div class="share-url-box">
        ${shareText}<br>
        ${shareUrl}
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
  
  // Overlay'e tıklandığında kapat
  modal.addEventListener('click', function(e) {
    if (e.target === modal) {
      closeShareModalGallery();
    }
  });
};

window.closeShareModalGallery = function() {
  const modal = document.getElementById('shareModalGallery');
  if (modal) {
    modal.remove();
  }
};

window.shareToWhatsAppGallery = function(text, url) {
  const whatsappUrl = 'https://wa.me/?text=' + encodeURIComponent(text + '\n' + url);
  window.open(whatsappUrl, '_blank');
};

window.shareToTelegramGallery = function(text, url) {
  const telegramUrl = 'https://t.me/share/url?url=' + encodeURIComponent(url) + '&text=' + encodeURIComponent(text);
  window.open(telegramUrl, '_blank');
};

window.shareToMessengerGallery = function(url) {
  const messengerUrl = 'fb-messenger://share?link=' + encodeURIComponent(url);
  window.open(messengerUrl, '_blank');
};

window.copyListingUrlGallery = function(text, url) {
  const fullText = text + '\n' + url;
  
  navigator.clipboard.writeText(fullText).then(() => {
    const copyBtn = document.querySelector('#shareModalGallery .share-btn-copy');
    if (copyBtn) {
      const originalText = copyBtn.innerHTML;
      copyBtn.innerHTML = '✓ Kopyalandı!';
      copyBtn.style.background = '#10b981';
      setTimeout(() => {
        copyBtn.innerHTML = originalText;
        copyBtn.style.background = '#6b7280';
      }, 2000);
    }
  });
};
