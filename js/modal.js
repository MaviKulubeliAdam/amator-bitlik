/**
 * Modal.js - Modal, form, detay paneli ve görsel işlemleri
 */

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
  document.getElementById('addListingForm').addEventListener('submit', handleFormSubmit);
  document.getElementById('formImages').addEventListener('change', handleImageUpload);

  // Terms modal setup
  setupTermsModal();

  ['formTitle', 'formBrand', 'formModel', 'formSellerName', 'formLocation', 'formDescription'].forEach(id => {
    const input = document.getElementById(id);
    input.addEventListener('input', (e) => {
      const start = e.target.selectionStart;
      const value = e.target.value;
      if (value.length > 0) {
        e.target.value = value.charAt(0).toUpperCase() + value.slice(1);
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

  const phoneInput = document.getElementById('formPhone');
  phoneInput.addEventListener('input', (e) => {
    let value = e.target.value.replace(/\D/g, '');
    if (value.length > 11) value = value.slice(0, 11);
    e.target.value = value;
    e.target.setCustomValidity(value.length > 0 && value.length !== 11 ? 'Telefon numarası tam olarak 11 hane olmalıdır' : '');
  });
  phoneInput.addEventListener('blur', (e) => {
    const value = e.target.value.replace(/\D/g, '');
    if (value.length > 0 && value.length !== 11) {
      e.target.setCustomValidity('Telefon numarası tam olarak 11 hane olmalıdır');
      e.target.reportValidity();
    }
  });
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
 * Yeni ilan ekleme modalını açar
 */
function openAddListingModal() {
  // Giriş yapmamış kullanıcılar için login modalı göster
  if (!ativ_ajax.is_user_logged_in) {
    showLoginRequiredModal();
    return;
  }
  
  editingListing = null;
  document.getElementById('addListingModal').style.display = 'flex';
  document.body.style.overflow = 'hidden';
  document.querySelector('.modal-header h2').textContent = 'Yeni İlan Ekle';
  document.getElementById('formSubmitBtn').textContent = 'İlanı Yayınla';
  updatePreview();
  
  // Şehir listesini yükle
  loadCities();
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
  document.getElementById('formTitle').value = listing.title || '';
  document.getElementById('formCategory').value = listing.category || '';
  document.getElementById('formBrand').value = listing.brand || '';
  document.getElementById('formModel').value = listing.model || '';
  document.getElementById('formCondition').value = listing.condition || '';
  document.getElementById('formPrice').value = listing.price || '';
  document.getElementById('formCurrency').value = listing.currency || 'TRY';
  document.getElementById('formDescription').value = listing.description || '';
  document.getElementById('formCallsign').value = listing.callsign || '';
  document.getElementById('formSellerName').value = listing.seller_name || '';
  document.getElementById('formLocation').value = listing.location || '';
  document.getElementById('formEmail').value = listing.seller_email || '';
  document.getElementById('formPhone').value = listing.seller_phone || '';

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
  document.getElementById('addListingModal').style.display = 'none';
  document.body.style.overflow = 'auto';
  document.getElementById('addListingForm').reset();
  document.getElementById('formMessage').innerHTML = '';
  document.getElementById('formTermsCheckbox').checked = false; // Checkbox'ı temizle
  uploadedImages = [];
  featuredImageIndex = 0;
  editingListing = null;
  isEditingRejectedListing = false;
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
  
  // Sözleşme kontrolü
  if (!termsCheckbox.checked) {
    messageDiv.innerHTML = '<div class="error-message">Lütfen Kullanım Sözleşmesi ve KVKK Aydınlatma Metni\'ni kabul edin.</div>';
    termsCheckbox.focus();
    setTimeout(() => {
      messageDiv.innerHTML = '';
    }, 3000);
    return;
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
    title: document.getElementById('formTitle').value.trim(),
    category: document.getElementById('formCategory').value,
    brand: document.getElementById('formBrand').value.trim(),
    model: document.getElementById('formModel').value.trim(),
    condition: document.getElementById('formCondition').value,
    price: parseFloat(document.getElementById('formPrice').value),
    currency: document.getElementById('formCurrency').value,
    description: document.getElementById('formDescription').value.trim(),
    images: uploadedImages.length > 0 ? uploadedImages : null,
    featuredImageIndex: featuredImageIndex,
    emoji: uploadedImages.length > 0 ? null : "📻",
    callsign: callsign,
    seller_name: document.getElementById('formSellerName').value.trim(),
    location: document.getElementById('formLocation').value.trim(),
    seller_email: document.getElementById('formEmail').value.trim(),
    seller_phone: document.getElementById('formPhone').value.trim()
  };

  try {
    if (isEditing) {
      await updateListing(editingListing.id, listingData);
      if (isEditingRejectedListing) {
        messageDiv.innerHTML = '<div class="success-message">İlan başarıyla güncellendi ve tekrar onaya gönderildi!</div>';
      } else {
        messageDiv.innerHTML = '<div class="success-message">İlan başarıyla güncellendi!</div>';
      }
    } else {
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
 * Detay bölümlerini oluşturur
 */
function createDetailSections(listing) {
  // Kontakt linkleri
  const rawPhone = listing.seller_phone || '';
  const digitsOnly = String(rawPhone).replace(/\D/g, '');
  let waNumber = digitsOnly;
  // Türkiye numaralarını normalize et: +90 / 90 / 0XXXXXXXXXX / XXXXXXXXXX
  if (waNumber) {
    if (waNumber.startsWith('0') && waNumber.length === 11) {
      waNumber = '90' + waNumber.slice(1);
    } else if (waNumber.length === 10) {
      waNumber = '90' + waNumber;
    } else if (waNumber.startsWith('90') && waNumber.length >= 12) {
      // already includes country code; keep as is
    } else if (waNumber.startsWith('90') && waNumber.length === 11) {
      // edge: missing one digit; leave unchanged
    } else {
      // default: if starts with country code +90 was stripped to 90; ensure 90 prefix
      if (!waNumber.startsWith('90')) {
        waNumber = '90' + waNumber;
      }
    }
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
                  <p>Telefon: ${escapeHtml(listing.seller_phone)}</p>
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
        
        // Form alanlarını doldur
        document.getElementById('formTitle').value = listing.title || '';
        document.getElementById('formCategory').value = listing.category || '';
        document.getElementById('formBrand').value = listing.brand || '';
        document.getElementById('formModel').value = listing.model || '';
        document.getElementById('formCondition').value = listing.condition || '';
        document.getElementById('formPrice').value = listing.price || '';
        document.getElementById('formCurrency').value = listing.currency || 'TRY';
        document.getElementById('formDescription').value = listing.description || '';
        document.getElementById('formCallsign').value = listing.callsign || '';
        document.getElementById('formSellerName').value = listing.seller_name || '';
        document.getElementById('formLocation').value = listing.location || '';
        document.getElementById('formEmail').value = listing.seller_email || '';
        document.getElementById('formPhone').value = listing.seller_phone || '';
        
        // Modal başlığı ve submit butonunu özelleştir
        document.querySelector('.modal-header h2').textContent = 'Red Edilen İlanı Düzenle';
        document.getElementById('formSubmitBtn').textContent = 'Güncelle ve Tekrar Onaya Gönder';
        
        // Görselleri yükle
        uploadedImages = listing.images || [];
        featuredImageIndex = Math.max(0, parseInt(listing.featured_image_index || 0));
        renderImagePreviews();
        updatePreview();
        
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

