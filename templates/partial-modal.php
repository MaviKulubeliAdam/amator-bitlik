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
    <div class="form-group city-select-wrapper">
      <label for="formLocation">Konum *</label>
      <div class="city-select-container">
        <input type="text" id="formLocation" required placeholder="Şehir seçin veya yazın..." autocomplete="off">
        <div class="city-dropdown" id="cityDropdown"></div>
      </div>
    </div>
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
   <h2>📄 Kullanım Sözleşmesi ve KVKK Aydınlatma Metni</h2>
   <button class="modal-close" id="termsModalCloseBtn" aria-label="Kapat">×</button>
  </div>
  <div class="modal-body terms-modal-body">
   <div class="terms-content">
    
    <p style="text-align: center; font-weight: 600; color: #667eea; margin-bottom: 24px;">Son Güncelleme: 1 Aralık 2025</p>
    
    <h3>1. TARAFLAR VE KONU</h3>
    <p>İşbu sözleşme, Amatör Telsiz İlan Vitrini ("Platform") üzerinden ilan yayınlayan veya Platform'a erişen tüm kullanıcılar ("Kullanıcı") ile Platform yöneticisi arasında düzenlenmiştir.</p>
    <p>Platform'a erişen, kullanan veya ilan oluşturan her kullanıcı, işbu sözleşmenin tamamını okumuş, anlamış ve tüm hükümleri kabul etmiş sayılır.</p>
    
    <h3>2. PLATFORMUN HUKUKİ STATÜSÜ VE SORUMLULUKLARI</h3>
    <p><strong>2.1. Yer Sağlayıcı Statüsü</strong></p>
    <p>Platform, 5651 sayılı Kanun kapsamında <strong>"yer sağlayıcı"</strong>dır. Kullanıcı tarafından oluşturulan içeriklerin doğruluğunu, yasallığını veya güvenilirliğini denetleme yükümlülüğü yoktur.</p>
    
    <p><strong>2.2. Aracı Değildir</strong></p>
    <p>Platform, kullanıcılar arasında gerçekleşen satış, alış, takas, teslimat veya pazarlık süreçlerinde hiçbir şekilde taraf veya aracı değildir.</p>
    
    <p><strong>2.3. Garanti Verilmez</strong></p>
    <p>Platform; ürünlerin doğruluğunu, ürünün niteliğini, kullanıcıların kimliğini veya güvenilirliğini, ilan içeriklerinin doğruluğunu garanti etmez.</p>
    
    <p><strong>2.4. Sorumluluk Reddi</strong></p>
    <p>Platform; dolandırıcılık, sahtecilik, ödeme problemleri, ürün teslim edilmemesi, hasarlı ürün gönderimi dahil olmak üzere alıcı ve satıcı arasındaki hiçbir işlemden sorumlu değildir.</p>
    
    <p><strong>2.5. İlan Onaylama Yetkisi</strong></p>
    <p>Platform, uygun görmediği ilanları onaylama, düzenleme talep etme, reddetme veya kaldırma hakkını saklı tutar.</p>
    
    <h3>3. KULLANICI YÜKÜMLÜLÜKLERİ</h3>
    <p><strong>3.1. İlan İçeriği Kullanıcıya Aittir</strong></p>
    <p>Kullanıcı, paylaştığı tüm içeriklerden (açıklama, fotoğraf, fiyat, iletişim bilgisi, çağrı işareti vb.) bizzat sorumludur.</p>
    
    <p><strong>3.2. Ürünlerin Yasallığı</strong></p>
    <p>İlan verilen ürünün yasallığı, lisans gerektirip gerektirmediği, teknik özellikleri, kullanımında doğabilecek tüm hukuki sonuçlar yalnızca kullanıcıya aittir.</p>
    
    <p><strong>3.3. Yasal Sorumluluk</strong></p>
    <p>Kullanıcı, Platform'u kullanırken yürürlükteki tüm mevzuata uygun davranmayı kabul eder. Hukuka aykırı işlem yapılması hâlinde doğacak cezaî ve hukukî sorumluluk tamamen kullanıcıya aittir.</p>
    
    <p><strong>3.4. Yanlış veya Yanıltıcı Bilgi Paylaşmama</strong></p>
    <p>Kullanıcı, yanlış, eksik veya aldatıcı bilgi paylaşmayacağını taahhüt eder.</p>
    
    <p><strong>3.5. Üçüncü Kişi Haklarının Korunması</strong></p>
    <p>Kullanıcı, üçüncü kişilerin marka, telif, patent gibi haklarını ihlal eden içerik paylaşamaz.</p>
    
    <h3>4. ALIM-SATIM VE İŞLEM SÜREÇLERİ</h3>
    <p><strong>4.1. Platform Aracı Değildir</strong></p>
    <p>Ödeme, pazarlık, teslimat, ürün kontrolü ve iade süreçleri tamamen alıcı ve satıcı arasındadır.</p>
    
    <p><strong>4.2. Dış Kanallar Üzerinden İletişim</strong></p>
    <p>Kullanıcılar WhatsApp, telefon, e-posta veya diğer dış iletişim kanallarını kullanarak kendi aralarında iletişim kurar. Bu iletişimlerden doğan tüm riskler kullanıcıya aittir.</p>
    
    <p><strong>4.3. Güvenli Alışveriş Sorumluluğu</strong></p>
    <p>Kullanıcılar, ürün ve satıcı doğrulamasını yapmakla yükümlüdür. Platform, güvenli alışveriş garantisi vermez.</p>
    
    <h3>5. GİZLİLİK VE KİŞİSEL VERİLERİN KORUNMASI (KVKK)</h3>
    <p><strong>5.1. İşlenen Kişisel Veriler</strong></p>
    <p>Platform tarafından işlenen veriler: Ad-soyad, e-posta adresi, telefon numarası, konum bilgisi, çağrı işareti, ilan içeriği ve görseller, trafik ve log kayıtları (5651 sayılı Kanun gereği).</p>
    
    <p><strong>5.2. Veri İşleme Amaçları</strong></p>
    <p>Kişisel veriler; ilan yayınlama, kullanıcıların birbirine ulaşması, Platform hizmetlerinin sağlanması amaçlarıyla işlenmektedir.</p>
    
    <p><strong>5.3. Hukuki Sebep</strong></p>
    <p>Veriler, sözleşmenin kurulması ve ifası, meşru menfaat, 5651 sayılı Kanun gereği log tutma yükümlülüğü kapsamında işlenmektedir.</p>
    
    <p><strong>5.4. Veri Paylaşımı</strong></p>
    <p>Kişisel veriler üçüncü kişilerle paylaşılmaz, ancak yetkili kurumların talebi halinde hukuki yükümlülük kapsamında paylaşılabilir.</p>
    
    <p><strong>5.5. Kullanıcı Hakları</strong></p>
    <p>Kullanıcı; veri güncelleme, silme, erişim ve bilgi talebi haklarına sahiptir.</p>
    
    <p><strong>5.6. Açık Rıza</strong></p>
    <p>İlan veren kullanıcı, ilanında paylaştığı bilgilerin herkese açık olacağını kabul eder.</p>
    
    <h3>6. SORUMLULUK REDDİ VE TAZMİNAT</h3>
    <p><strong>6.1. Dolandırıcılık ve Suçlar</strong></p>
    <p>Platform, kullanıcılar arasında gerçekleşen dolandırıcılık, hırsızlık, sahtecilik, gasp, tehdit vb. tüm suçlardan sorumlu değildir.</p>
    
    <p><strong>6.2. Maddi ve Manevi Zararlar</strong></p>
    <p>Platform, kullanıcıların birbirine verdiği zararlardan veya Platform kullanımından doğan maddi/manevi kayıplardan sorumlu tutulamaz.</p>
    
    <p><strong>6.3. Teknik Arızalar</strong></p>
    <p>Platform; erişim hataları, sunucu arızası, veri kaybı, bakım çalışmaları vb. sebeplerle yaşanan aksaklıklardan sorumlu değildir.</p>
    
    <p><strong>6.4. Riskin Kullanıcı Tarafından Kabulü</strong></p>
    <p>Kullanıcı, Platform'u kullanmakla tüm riskleri kabul ettiğini beyan eder.</p>
    
    <p><strong>6.5. Tazminat Hükmü</strong></p>
    <p>Kullanıcı, Platform'un kullanımından doğabilecek tüm zarar, dava, şikayet ve talep durumlarında Platform işletmecisini tazminat sorumluluğundan muaf tuttuğunu kabul eder.</p>
    
    <h3>7. DELİL SÖZLEŞMESİ</h3>
    <p>Platform'un elektronik kayıtları, log kayıtları, veritabanı kayıtları, e-posta yazışmaları ve diğer dijital kayıtları kesin delil niteliğindedir.</p>
    
    <h3>8. UYUŞMAZLIK ÇÖZÜMÜ</h3>
    <p>Uyuşmazlık durumunda Türkiye Cumhuriyeti kanunları uygulanır. Yetkili mahkeme: İstanbul Mahkemeleri ve İcra Daireleridir.</p>
    
    <h3>9. SÖZLEŞME DEĞİŞİKLİKLERİ</h3>
    <p>Platform, sözleşme hükümlerini önceden bildirmeksizin güncelleme hakkını saklı tutar. Güncel sözleşmenin yayınlanmasıyla birlikte yeni hükümler yürürlüğe girer. Platform'un kullanılmaya devam edilmesi yeni hükümlerinin kabul edildiği anlamına gelir.</p>
    
    <h3>10. KABUL BEYANI</h3>
    <p>Kullanıcı, Platform'a üye olarak veya ilan vererek işbu sözleşmenin tüm hükümlerini okuduğunu, anladığını ve aynen kabul ettiğini; Platform'u kullanmanın tüm sorumluluğunu üstlendiğini beyan eder.</p>
    
    <div class="terms-footer">
     <p><strong>⚖️ Hukuki Uyarı:</strong> Bu sözleşme, 5651 sayılı İnternet Ortamında Yapılan Yayınların Düzenlenmesi ve Bu Yayınlar Yoluyla İşlenen Suçlarla Mücadele Edilmesi Hakkında Kanun ve 6698 sayılı Kişisel Verilerin Korunması Kanunu çerçevesinde düzenlenmiştir.</p>
     <p><em>📌 Bu metni dikkatlice okuyunuz. Platform kullanımı, işbu sözleşmenin tüm hükümlerini kabul ettiğiniz anlamına gelir.</em></p>
    </div>
    
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
