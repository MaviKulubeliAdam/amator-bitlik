<div id="addListingModal" class="modal-overlay" style="display: none;">
 <div class="modal-content">
  <div class="modal-header">
   <h2>Yeni İlan Ekle</h2><button class="modal-close" id="modalCloseBtn" aria-label="Kapat">×</button>
  </div>
  <div id="formMessage"></div>
  <div class="modal-body">
   <div class="preview-section">
    <div class="preview-card">
     <h3>👁️ Canlı Önizleme</h3>
     <p>İlanınız böyle görünecek</p>
     <div class="preview-listing-card">
      <div class="preview-listing-image" id="previewImage">📻</div>
      <div class="preview-listing-content">
       <h3 class="preview-listing-title" id="previewTitle"><span class="preview-empty-state">İlan başlığı...</span></h3>
       <p class="preview-listing-callsign" id="previewCallsign"><span class="preview-empty-state">Çağrı işareti...</span></p>
       <p class="preview-listing-price" id="previewPrice"><span class="preview-empty-state">₺0 TRY</span></p>
      </div>
     </div>
    </div>
   </div>
   <div class="form-section">
    <form id="addListingForm">
    <div class="form-group"><label for="formTitle">İlan Başlığı *</label> <input type="text" id="formTitle" required maxlength="42" placeholder="Örn: Yaesu FT-991A HF/VHF/UHF"></div>
     
     <div class="form-group category-select-wrapper">
      <label for="formCategory">Kategori *</label>
      <div class="category-select-container">
        <input type="text" id="formCategory" required placeholder="Kategori seçin..." autocomplete="off" readonly>
        <div class="category-dropdown" id="formCategoryDropdown"></div>
      </div>
     </div>
     
     <div class="form-group"><label for="formBrand">Marka *</label> <input type="text" id="formBrand" required placeholder="Örn: Yaesu, Icom, Kenwood"></div>
     <div class="form-group"><label for="formModel">Model *</label> <input type="text" id="formModel" required placeholder="Örn: FT-991A"></div>
     
     <div class="form-group condition-select-wrapper">
      <label for="formCondition">Durum *</label>
      <div class="condition-select-container">
        <input type="text" id="formCondition" required placeholder="Durum seçin..." autocomplete="off" readonly>
        <div class="condition-dropdown" id="formConditionDropdown"></div>
      </div>
     </div>
     
     <div class="form-group"><label for="formPrice">Fiyat *</label>
      <div style="display: flex; gap: 12px;"><input type="number" id="formPrice" required min="0" step="0.01" placeholder="0" style="flex: 2;"> <select id="formCurrency" required style="flex: 1; padding: 14px 16px; border: 2px solid #e0e0e0; border-radius: 10px; font-size: 15px;"> <option value="TRY">₺ TRY</option> <option value="USD">$ USD</option> <option value="EUR">€ EUR</option> </select></div>
     </div>
     <div class="form-group"><label for="formDescription">Açıklama *</label> <textarea id="formDescription" required placeholder="Ürün hakkında detaylı bilgi verin..."></textarea></div>
     <div class="form-group"><label>Ürün Görselleri (Maksimum 5 adet)</label>
      <div class="file-upload-wrapper"><input type="file" id="formImages" accept="image/*" multiple class="file-input"> <label for="formImages" class="file-upload-label">
        <svg width="24" height="24" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path> <polyline points="17 8 12 3 7 8"></polyline> <line x1="12" y1="3" x2="12" y2="15"></line></svg>
        <span class="file-upload-text">Görselleri seçin veya sürükleyin</span> <span class="file-upload-hint">PNG, JPG, JPEG (Max 5 dosya)</span> </label></div>
      <div id="imagePreviewContainer" style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 12px; margin-top: 16px;"></div>
     </div>
     
     <div class="form-group"><label>Ürün Videosu (Opsiyonel)</label>
      <div class="file-upload-wrapper"><input type="file" id="formVideo" accept="video/mp4,video/webm" class="file-input"> <label for="formVideo" class="file-upload-label">
        <svg width="24" height="24" viewbox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 7l-7 5 7 5V7z"></path> <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect></svg>
        <span class="file-upload-text">Videoyu seçin veya sürükleyin</span> <span class="file-upload-hint">MP4, WebM (Max 5 dakika, 150MB)</span> </label></div>
      <div id="videoPreviewContainer" style="margin-top: 12px;"></div>
      <small id="videoStatusHint" style="color: #666; display: block; margin-top: 8px;"></small>
     </div>
     
<div class="form-group" style="display: none;"><label for="formCallsign">Çağrı İşareti</label> <input type="text" id="formCallsign" placeholder="Örn: TA1ABC"></div>
     <div class="form-group"><label for="formSellerName">Ad Soyad *</label> <input type="text" id="formSellerName" required placeholder="Adınız ve soyadınız"></div>
    <div class="form-group city-select-wrapper">
      <label for="formLocation">Konum *</label>
      <div class="city-select-container">
        <input type="text" id="formLocation" required placeholder="Şehir seçin veya yazın..." autocomplete="off">
        <div class="city-dropdown" id="cityDropdown"></div>
      </div>
    </div>
     <div class="form-group"><label for="formEmail">E-posta *</label> <input type="email" id="formEmail" required placeholder="ornek@email.com"></div>
     <div class="form-group phone-group">
      <label for="formPhone">Telefon *</label>
      <div class="phone-input-wrapper">
        <select id="formCountryCode" class="country-code-select" required>
          <!-- Will be populated by JS -->
        </select>
        <input type="tel" id="formPhone" required placeholder="555 123 4567" maxlength="13" pattern="[0-9\s]{10,13}">
      </div>
      <small class="phone-hint">10 haneli numaranızı girin (başında 0 olmadan)</small>
     </div>
     <div class="form-group terms-group">
      <label class="terms-checkbox-label">
       <input type="checkbox" id="formTermsCheckbox" required>
       <span class="terms-text">
        <a href="#" id="openTermsLink" class="terms-link">Kullanım Sözleşmesi ve KVKK Aydınlatma Metni</a>'ni okudum, kabul ediyorum. *
       </span>
      </label>
     </div>
     <div class="form-actions"><button type="button" class="btn-cancel" id="formCancelBtn">İptal</button> <button type="submit" class="btn-submit" id="formSubmitBtn">İlanı Yayınla</button></div>
    </form>
   </div>
  </div>
 </div>
</div>

<!-- Kullanım Sözleşmesi ve KVKK Modalı -->
<div id="termsModal" class="modal-overlay" style="display: none;">
 <div class="modal-content terms-modal-content">
  <div class="modal-header">
   <h2>📄 Kullanım Sözleşmesi ve KVKK Aydınlatma Metni</h2>
   <button class="modal-close" id="termsModalCloseBtn" aria-label="Kapat">×</button>
  </div>
  <div class="modal-body terms-modal-body">
   <div class="terms-content">
    <?php 
    // Kullanıcı sözleşmesi içeriğini dinamik olarak göster
    global $ativ_terms_content;
    echo isset($ativ_terms_content) && !empty($ativ_terms_content) ? wp_kses_post($ativ_terms_content) : '<p>Sözleşme metni yüklenemedi.</p>';
    ?>
   </div>
  </div>
  <div class="modal-footer">
   <button type="button" class="btn-submit" id="acceptTermsBtn">Kabul Ediyorum</button>
   <button type="button" class="btn-cancel" id="closeTermsBtn">Kapat</button>
  </div>
 </div>
</div>

<!-- Login Required Modal - Giriş Yapmamış Kullanıcılar İçin -->
<div id="loginRequiredModal" class="modal-overlay" style="display: none;">
 <div class="modal-content" style="max-width: 500px;">
  <div class="modal-header">
   <h2>🔐 Giriş Yapmanız Gerekiyor</h2>
   <button class="modal-close" id="loginRequiredCloseBtn" aria-label="Kapat">×</button>
  </div>
  <div class="modal-body" style="display: block; padding: 30px;">
   <div style="text-align: center; margin-bottom: 24px;">
    <div style="font-size: 64px; margin-bottom: 16px;">📝</div>
    <p style="font-size: 16px; color: #666; line-height: 1.6; margin: 0;">
     İlan eklemek için <strong>üye girişi</strong> yapmanız gerekmektedir.
    </p>
   </div>
   
   <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
    <p style="margin: 0; font-size: 14px; color: #856404; line-height: 1.6;">
     ⚠️ <strong>Önemli:</strong> Platform, yalnızca kayıtlı kullanıcıların ilan eklemesine izin vermektedir. 
     Bu, güvenlik ve içerik kalitesi için zorunlu bir gerekliliktir.
    </p>
   </div>
   
   <div style="display: flex; flex-direction: column; gap: 12px;">
    <a href="<?php echo home_url('/giris'); ?>" class="btn-submit" style="text-align: center; text-decoration: none; display: block; padding: 14px 24px;">
     🔑 Giriş Yap
    </a>
    <a href="<?php echo home_url('/kayit'); ?>" class="btn-cancel" style="text-align: center; text-decoration: none; display: block; padding: 14px 24px; background: #667eea; color: white;">
     ✨ Üye Ol
    </a>
    <button type="button" class="btn-cancel" id="loginRequiredCancelBtn" style="margin-top: 8px;">
     İptal
    </button>
   </div>
  </div>
 </div>
</div>
