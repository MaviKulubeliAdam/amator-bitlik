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
     <div class="form-group"><label for="formTitle">İlan Başlığı *</label> <input type="text" id="formTitle" required placeholder="Örn: Yaesu FT-991A HF/VHF/UHF"></div>
     <div class="form-group"><label for="formCategory">Kategori *</label> <select id="formCategory" required> <option value="">Kategori seçin</option> <option value="transceiver">Telsiz</option> <option value="antenna">Anten</option> <option value="amplifier">Amplifikatör</option> <option value="accessory">Aksesuar</option> <option value="other">Diğer</option> </select></div>
     <div class="form-group"><label for="formBrand">Marka *</label> <input type="text" id="formBrand" required placeholder="Örn: Yaesu, Icom, Kenwood"></div>
     <div class="form-group"><label for="formModel">Model *</label> <input type="text" id="formModel" required placeholder="Örn: FT-991A"></div>
     <div class="form-group"><label for="formCondition">Durum *</label> <select id="formCondition" required> <option value="">Durum seçin</option> <option value="Sıfır">Sıfır</option> <option value="Kullanılmış">Kullanılmış</option> <option value="Arızalı">Arızalı</option> </select></div>
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
     <div class="form-group"><label for="formCallsign">Çağrı İşareti *</label> <input type="text" id="formCallsign" required placeholder="Örn: TA1ABC"></div>
     <div class="form-group"><label for="formSellerName">Ad Soyad *</label> <input type="text" id="formSellerName" required placeholder="Adınız ve soyadınız"></div>
     <div class="form-group"><label for="formLocation">Konum *</label> <input type="text" id="formLocation" required placeholder="Örn: İstanbul, Kadıköy"></div>
     <div class="form-group"><label for="formEmail">E-posta *</label> <input type="email" id="formEmail" required placeholder="ornek@email.com"></div>
     <div class="form-group"><label for="formPhone">Telefon *</label> <input type="tel" id="formPhone" required placeholder="0532 111 22 33"></div>
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
   <h2>Kullanım Sözleşmesi ve KVKK Aydınlatma Metni</h2>
   <button class="modal-close" id="termsModalCloseBtn" aria-label="Kapat">×</button>
  </div>
  <div class="modal-body terms-modal-body">
   <div class="terms-content">
    
    <h3>1. GENEL HÜKÜMLER</h3>
    <p>İşbu sözleşme, Amatör Telsiz İlan Vitrini ("Platform") üzerinden ilan vermek isteyen kullanıcılar ile Platform yöneticisi arasında akdedilmiştir. Platform kullanımı, işbu sözleşmenin tüm hükümlerinin kabul edildiği anlamına gelir.</p>
    
    <h3>2. PLATFORMUN ROLÜ VE SORUMLULUKLARI</h3>
    <p><strong>2.1.</strong> Platform, yalnızca kullanıcıların ilan yayınlaması için bir vitrin niteliğinde olup, alım-satım işlemlerinde taraf değildir.</p>
    <p><strong>2.2.</strong> Platform, ilanların doğruluğunu, ürünlerin kalitesini, alıcı ve satıcıların güvenilirliğini garanti etmez.</p>
    <p><strong>2.3.</strong> Platform yöneticisi, alıcı ve satıcı arasında gerçekleşen işlemlerden, anlaşmazlıklardan, dolandırıcılık vakalarından, ürün teslimatından, ödeme süreçlerinden ve benzeri durumlardan hiçbir şekilde sorumlu değildir.</p>
    <p><strong>2.4.</strong> Platform, yayınlanan ilanları uygun gördüğü takdirde onaylama, reddetme veya kaldırma hakkına sahiptir.</p>
    
    <h3>3. KULLANICI SORUMLULUKLARI</h3>
    <p><strong>3.1.</strong> Kullanıcılar, yayınladıkları ilanların içeriğinden, doğruluğundan ve yasallığından tamamen sorumludur.</p>
    <p><strong>3.2.</strong> Alıcı ve satıcılar, aralarında gerçekleştirecekleri tüm işlemlerden münhasıran kendileri sorumludur.</p>
    <p><strong>3.3.</strong> Kullanıcılar, Platform üzerinden gerçekleştirdikleri iletişim ve işlemlerde yasal mevzuata uygun davranmakla yükümlüdür.</p>
    <p><strong>3.4.</strong> Kullanıcılar, yanıltıcı, yanlış veya yasadışı içerik paylaşmayacağını beyan ve taahhüt eder.</p>
    <p><strong>3.5.</strong> Kullanıcılar, üçüncü kişilerin haklarını (telif, marka, patent vb.) ihlal eden içerik paylaşmayacağını kabul eder.</p>
    
    <h3>4. ALIM-SATIM İŞLEMLERİ</h3>
    <p><strong>4.1.</strong> Platform, alıcı ve satıcı arasındaki alım-satım sürecinin hiçbir aşamasında aracı değildir.</p>
    <p><strong>4.2.</strong> Ödeme, teslimat, kargo, ürün kontrolü ve iade süreçleri tamamen alıcı ve satıcı arasında gerçekleşir.</p>
    <p><strong>4.3.</strong> Platform, ürün bedelinin ödenmemesi, ürünün teslim edilmemesi, ürün hasarları veya kalite sorunlarından sorumlu tutulamaz.</p>
    <p><strong>4.4.</strong> Kullanıcıların, güvenli alışveriş için elden teslimat veya güvenli ödeme yöntemlerini tercih etmeleri tavsiye edilir.</p>
    
    <h3>5. GİZLİLİK VE VERİ KORUMA (KVKK)</h3>
    <p><strong>5.1.</strong> Platform, kullanıcıların paylaştığı kişisel verileri 6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında işler.</p>
    <p><strong>5.2.</strong> Toplanan kişisel veriler: Ad-soyad, e-posta, telefon, konum, çağrı işareti ve ilan içeriğidir.</p>
    <p><strong>5.3.</strong> Veriler, yalnızca ilan yayınlama, iletişim kurma ve Platform hizmetlerinin sağlanması amacıyla kullanılır.</p>
    <p><strong>5.4.</strong> Kullanıcılar, verilerinin silinmesini, güncellenmesini veya kendilerine iletilmesini talep edebilir.</p>
    <p><strong>5.5.</strong> Platform, kişisel verileri üçüncü kişilerle paylaşmaz; ancak yasal zorunluluklar haricinde ifşa etmez.</p>
    <p><strong>5.6.</strong> İlan verenler, ilanlarında paylaştıkları iletişim bilgilerinin herkese açık olacağını kabul eder.</p>
    
    <h3>6. SORUMLULUK REDDİ</h3>
    <p><strong>6.1.</strong> Platform, kullanıcılar arasında yaşanan dolandırıcılık, hırsızlık, sahtecilik, gasp ve benzeri suçlardan sorumlu değildir.</p>
    <p><strong>6.2.</strong> Platform, kullanıcıların birbirine verdiği zararlardan, maddi ve manevi kayıplardan sorumlu tutulamaz.</p>
    <p><strong>6.3.</strong> Platform, teknik arıza, veri kaybı, erişim sorunu gibi durumlardan kaynaklanan zararlardan sorumlu değildir.</p>
    <p><strong>6.4.</strong> Kullanıcılar, Platform kullanımından kaynaklanan tüm riskleri kabul eder.</p>
    
    <h3>7. UYUŞMAZLIK ÇÖZÜMÜ</h3>
    <p><strong>7.1.</strong> İşbu sözleşmeden doğan uyuşmazlıklarda Türkiye Cumhuriyeti yasaları uygulanır.</p>
    <p><strong>7.2.</strong> Uyuşmazlıkların çözümünde İstanbul Mahkemeleri ve İcra Daireleri yetkilidir.</p>
    
    <h3>8. SÖZLEŞME DEĞİŞİKLİKLERİ</h3>
    <p><strong>8.1.</strong> Platform yöneticisi, işbu sözleşmeyi önceden haber vermeksizin değiştirme hakkını saklı tutar.</p>
    <p><strong>8.2.</strong> Güncel sözleşme her zaman Platform üzerinden erişilebilir durumdadır.</p>
    
    <h3>9. KABUL VE ONAY</h3>
    <p><strong>9.1.</strong> İşbu sözleşmeyi onaylayarak, yukarıdaki tüm maddeleri okuduğunuzu, anladığınızı ve kabul ettiğinizi beyan edersiniz.</p>
    <p><strong>9.2.</strong> Platform kullanımı ile tüm sorumlulukların size ait olduğunu ve Platform yöneticisini herhangi bir zarardan sorumlu tutmayacağınızı kabul edersiniz.</p>
    
    <div class="terms-footer">
     <p><strong>Son Güncelleme:</strong> 1 Aralık 2025</p>
     <p><em>Bu sözleşmeyi dikkatlice okuyunuz. Kabul etmediğiniz takdirde Platform'u kullanamazsınız.</em></p>
    </div>
    
   </div>
  </div>
  <div class="modal-footer">
   <button type="button" class="btn-submit" id="acceptTermsBtn">Kabul Ediyorum</button>
   <button type="button" class="btn-cancel" id="closeTermsBtn">Kapat</button>
  </div>
 </div>
</div>
