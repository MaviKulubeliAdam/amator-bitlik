<?php
/**
 * Satıcı Profili Sayfası Şablonu
 * Sadece giriş yapmış kullanıcılar görebilir
 */

if (!is_user_logged_in()) {
    wp_die('Bu sayfaya erişim için giriş yapmalısınız.');
}

$current_user = wp_get_current_user();
$user_id = $current_user->ID;

// Sözleşme içeriğini tanımla
global $ativ_terms_content;
if (!isset($ativ_terms_content) || empty($ativ_terms_content)) {
    $ativ_terms_content = '<p style="text-align: center; font-weight: 600; color: #667eea; margin-bottom: 24px;">Son Güncelleme: 1 Aralık 2025</p>
<h3>1. TARAFLAR VE KONU</h3>
<p>İşbu sözleşme, Amatör Telsiz İlan Vitrini ("Platform") üzerinden ilan yayınlayan veya Platform\'a erişen tüm kullanıcılar ("Kullanıcı") ile Platform yöneticisi arasında düzenlenmiştir.</p>
<h3>2. PLATFORMUN HUKUKİ STATÜSÜ</h3>
<p>Platform, 5651 sayılı Kanun kapsamında "yer sağlayıcı"dır. Platform; ürünlerin doğruluğunu, ürünün niteliğini, kullanıcıların kimliğini garanti etmez.</p>
<h3>3. KULLANICI YÜKÜMLÜLÜKLERİ</h3>
<p>Kullanıcı, paylaştığı tüm içeriklerden sorumludur. İlan verilen ürünün yasallığı, lisans gerektirip gerektirmediği kullanıcıya aittir.</p>
<h3>4. GİZLİLİK VE KİŞİSEL VERİLERİN KORUNMASI</h3>
<p>Platform tarafından işlenen veriler: Ad-soyad, e-posta, telefon numarası, konum, çağrı işareti, ilan içeriği.</p>
<h3>5. SORUMLULUK REDDİ</h3>
<p>Platform, kullanıcılar arasında gerçekleşen işlemlerden sorumlu değildir. Dolandırıcılık, ödeme sorunları, teslimat problemleri dahil tüm sorumluluğu kullanıcı üstlenir.</p>
<h3>6. UYUŞMAZLIK ÇÖZÜMÜ</h3>
<p>Uyuşmazlık durumunda Türkiye Cumhuriyeti kanunları uygulanır. Yetkili mahkeme: İstanbul Mahkemeleri ve İcra Daireleridir.</p>';
}
?>


<script>
window.pageType = 'profile';
window.ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
window.bitlikUserId = <?php echo intval($user_id); ?>;
window.atheneaNonce = '<?php echo wp_create_nonce('ativ_profile_nonce'); ?>';

document.addEventListener('DOMContentLoaded', function() {
    // Profil bilgilerini AJAX ile yükle
    const formData = new FormData();
    formData.append('action', 'ativ_load_profile_info');
    formData.append('_wpnonce', window.atheneaNonce);
    
    fetch(ajaxurl, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.data) {
            const profile = data.data;
            
            // Form alanlarını doldur
            document.getElementById('profileCallsign').value = profile.callsign || '';
            document.getElementById('profileName').value = profile.name || '';
            document.getElementById('profileEmail').value = profile.email || '';
            document.getElementById('profileLocation').value = profile.location || '';
            
            // Telefon numarasını parse et ve alanlara yerleştir
            if (profile.phone) {
                document.getElementById('profilePhone').value = profile.phone;
            }
            
            // Alan kodunu ayarla
            if (profile.country_code) {
                const countryCodeSelect = document.getElementById('profileCountryCode');
                if (countryCodeSelect) {
                    // Select yüklenene kadar bekle
                    const setCountryCode = () => {
                        const option = Array.from(countryCodeSelect.options).find(opt => opt.value === profile.country_code);
                        if (option) {
                            countryCodeSelect.value = profile.country_code;
                        }
                    };
                    
                    // Eğer select dolu ise hemen ayarla, değilse bekle
                    if (countryCodeSelect.options.length > 0) {
                        setCountryCode();
                    } else {
                        setTimeout(setCountryCode, 200);
                    }
                }
            }
        }
    })
    .catch(err => {
        console.error('Profil bilgileri yüklenemedi:', err);
        // Fallback: WordPress'den gelen bilgileri kullan
        document.getElementById('profileCallsign').value = '<?php echo esc_js($current_user->user_login); ?>';
        document.getElementById('profileName').value = '<?php echo esc_js($current_user->display_name); ?>';
        document.getElementById('profileEmail').value = '<?php echo esc_js($current_user->user_email); ?>';
    });
});
</script>
<script src="<?php echo plugins_url('js/profile.js', dirname(__FILE__)); ?>"></script>

<div id="bitlik-profile-container" class="bitlik-profile-wrapper">
    <style>
        .search-alert-card {border:1px solid #e5e7eb;border-radius:12px;padding:18px 20px;box-shadow:0 2px 8px rgba(0,0,0,0.08);background:#fff;margin-bottom:14px;display:flex;justify-content:space-between;gap:16px;align-items:flex-start;}
        .search-alert-card h4 {margin:0 0 12px 0;font-size:17px;font-weight:700;color:#1e293b;border-bottom:2px solid #f1f5f9;padding-bottom:8px;}
        .search-alert-card .alert-meta {display:flex;flex-direction:column;gap:10px;color:#475569;font-size:13px;line-height:1.6;}
        .alert-detail-row {display:flex;align-items:center;gap:10px;padding:6px 0;}
        .alert-detail-row .label {font-weight:600;color:#334155;min-width:100px;flex-shrink:0;}
        .alert-detail-row .value {color:#64748b;background:#f8fafc;padding:4px 12px;border-radius:6px;border:1px solid #e2e8f0;}
        .alert-badges {display:flex;gap:8px;margin-top:4px;flex-wrap:wrap;}
        .chip {display:inline-flex;align-items:center;gap:6px;padding:6px 12px;border-radius:6px;background:#f1f5f9;color:#475569;font-size:12px;font-weight:600;border:1px solid #e2e8f0;}
        .chip.primary {background:#dbeafe;color:#1e40af;border-color:#bfdbfe;}
        .chip.accent {background:#fef3c7;color:#92400e;border-color:#fde68a;}
        .chip.muted {background:#f8fafc;color:#64748b;border-color:#e2e8f0;font-weight:500;font-size:11px;}
        .search-alerts-header {display:flex;justify-content:flex-end;margin-bottom:14px;}
        .search-alerts-items {display:flex;flex-direction:column;gap:12px;}
        .alert-actions {display:flex;flex-direction:column;gap:8px;min-width:110px;}
        @media(max-width:768px){.search-alert-card{flex-direction:column;}.alert-actions{flex-direction:row;width:100%;justify-content:flex-end;}}
    </style>
    <div class="profile-header">
        <h1>👤 Bitlik Profilim</h1>
        <p class="profile-subtitle">Profil bilgilerinizi yönetin ve tercihlerinizi ayarlayın</p>
    </div>

    <!-- Sekme Navigasyonu -->
    <div class="profile-tabs-nav">
        <button class="profile-tab-button active" data-tab="profile-info">
            <span class="tab-icon">📋</span> Profil Bilgileri
        </button>
        <button class="profile-tab-button" data-tab="search-alerts">
            <span class="tab-icon">🔔</span> İlan Arama Uyarıları
        </button>
    </div>

    <!-- Sekme İçeriği -->
    <div class="profile-tabs-content">

        <!-- 1. Profil Bilgileri Sekmesi -->
        <div id="profile-info" class="profile-tab-panel active">
            <form id="profileInfoForm" class="profile-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="profileCallsign">Çağrı İşareti</label>
                        <input type="text" id="profileCallsign" name="callsign" placeholder="Örn: TA1ABC" readonly>
                    </div>
                    <div class="form-group">
                        <label for="profileName">Ad Soyadı</label>
                        <input type="text" id="profileName" name="name" placeholder="Adınız ve soyadınız" required readonly>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="profileEmail">E-posta</label>
                        <input type="email" id="profileEmail" name="email" placeholder="E-posta adresiniz" required>
                    </div>
                    <div class="form-group">
                        <label for="profileLocation">Konum</label>
                        <input type="text" id="profileLocation" name="location" placeholder="Şehir seçiniz" required autocomplete="off">
                        <div id="cityDropdown" class="city-dropdown"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="profilePhone">Telefon</label>
                    <div class="phone-input-wrapper" style="display: flex; gap: 10px;">
                        <select id="profileCountryCode" required style="flex: 0 0 80px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px;">
                            <!-- Dinamik olarak JS tarafından doldurulacak -->
                        </select>
                        <input type="tel" id="profilePhone" placeholder="555 123 4567" maxlength="13" pattern="[0-9\s]{10,13}" style="flex: 1;">
                    </div>
                    <small style="color: #666; margin-top: 5px; display: block;">10 haneli numaranızı girin (başında 0 olmadan)</small>
                </div>

                <div class="form-group" style="margin-top: 20px;">
                    <div style="display: flex; align-items: flex-start; gap: 10px;">
                        <input type="checkbox" id="profileTermsCheckbox" required style="width: 18px; height: 18px; margin-top: 3px; cursor: pointer;">
                        <span style="line-height: 1.6;">
                            <a href="#" id="profileTermsLink" style="color: #0073aa; text-decoration: underline; cursor: pointer;">Kullanıcı Sözleşmesi</a>'ni okudum ve kabul ediyorum.
                        </span>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">💾 Değişiklikleri Kaydet</button>
                </div>

                <div id="profileInfoMessage" class="form-message"></div>
            </form>
        </div>

        <!-- 2. İlan Arama Uyarıları Sekmesi -->
        <div id="search-alerts" class="profile-tab-panel">
            <p class="section-description">Belirli kriterlere uygun ilanlar yayınlandığında e-posta uyarısı alın</p>

            <div class="search-alerts-list" id="searchAlertsList">
                <div class="empty-state">
                    <p>Henüz arama uyarısı oluşturulmamış</p>
                    <button type="button" class="btn-secondary" id="addSearchAlertBtn">➕ Yeni Arama Uyarısı Ekle</button>
                </div>
            </div>

            <!-- Yeni Arama Uyarısı Formu (Modal) -->
            <div id="searchAlertModal" class="search-alert-modal" style="display: none;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Yeni Arama Uyarısı</h3>
                        <button type="button" class="modal-close" id="closeSearchAlertModal">&times;</button>
                    </div>

                    <form id="newSearchAlertForm" class="profile-form">
                        <input type="hidden" id="alertId" name="alert_id" value="">
                        <div class="form-group">
                            <label for="alertName">Uyarı Adı</label>
                            <input type="text" id="alertName" name="alert_name" placeholder="Örn: Yüksek Güç Amplifiyatörler" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="alertCategory">Kategori</label>
                                <select id="alertCategory" name="category">
                                    <option value="">Tümü</option>
                                    <option value="transceiver">Telsiz</option>
                                    <option value="antenna">Anten</option>
                                    <option value="amplifier">Amplifikatör</option>
                                    <option value="accessory">Aksesuar</option>
                                    <option value="other">Diğer</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="alertCondition">Durum</label>
                                <select id="alertCondition" name="condition">
                                    <option value="">Tümü</option>
                                    <option value="Sıfır">Sıfır</option>
                                    <option value="Kullanılmış">Kullanılmış</option>
                                    <option value="Arızalı">Arızalı</option>
                                    <option value="El Yapımı">El Yapımı</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="alertLocation">Konum</label>
                                <select id="alertLocation" name="location">
                                    <option value="">Yükleniyor...</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="alertSeller">Satıcı Çağrı İşareti</label>
                                <select id="alertSeller" name="seller_callsign">
                                    <option value="">Yükleniyor...</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group" style="flex: 2;">
                                <label for="alertKeyword">Anahtar Kelime</label>
                                <input type="text" id="alertKeyword" name="keyword" placeholder="Örn: el telsizi, QO-100">
                            </div>
                            <div class="form-group" style="align-self: flex-end;">
                                <label class="checkbox-inline" style="display: flex; gap: 8px; align-items: center; margin-bottom: 6px;">
                                    <input type="checkbox" id="alertAllListings" name="all_listings" value="1" style="width: 16px; height: 16px;">
                                    <span>Tüm ilanlar (anahtar kelimeyi yok say)</span>
                                </label>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="alertMinPrice">Min. Fiyat (TL)</label>
                                <input type="number" id="alertMinPrice" name="min_price" min="0" placeholder="0">
                            </div>

                            <div class="form-group">
                                <label for="alertMaxPrice">Max. Fiyat (TL)</label>
                                <input type="number" id="alertMaxPrice" name="max_price" min="0" placeholder="Sınırsız">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="alertFrequency">Uyarı Sıklığı</label>
                            <select id="alertFrequency" name="frequency" required>
                                <option value="immediate">Anında (Hemen)</option>
                                <option value="daily">Günlük</option>
                                <option value="weekly">Haftalık</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-primary">✅ Uyarı Oluştur</button>
                            <button type="button" class="btn-secondary" id="cancelSearchAlertBtn">İptal</button>
                        </div>

                        <div id="searchAlertMessage" class="form-message"></div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Loading Overlay -->
<div id="profileLoadingOverlay" class="loading-overlay" style="display: none;">
    <div class="spinner"></div>
    <p>Lütfen bekleyin...</p>
</div>

<?php
// Sözleşme modalını dahil et
$plugin_dir = dirname(dirname(__FILE__));
if (file_exists($plugin_dir . '/templates/partial-modal.php')) {
    include($plugin_dir . '/templates/partial-modal.php');
}
?>

<style>
/* Bitlik Profili Sayfa Stilleri */
.bitlik-profile-wrapper {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.profile-header {
    margin-bottom: 30px;
    border-bottom: 2px solid #667eea;
    padding-bottom: 20px;
}

.profile-header h1 {
    font-size: 28px;
    color: #1a1a1a;
    margin: 0 0 8px 0;
}

.profile-subtitle {
    color: #666;
    font-size: 14px;
    margin: 0;
}

/* Sekme Navigasyonu */
.profile-tabs-nav {
    display: flex;
    gap: 10px;
    margin-bottom: 30px;
    flex-wrap: wrap;
    border-bottom: 1px solid #e0e0e0;
}

.profile-tab-button {
    padding: 12px 16px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-size: 14px;
    color: #666;
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.profile-tab-button:hover {
    color: #667eea;
    background: #f8f9ff;
    border-radius: 8px 8px 0 0;
}

.profile-tab-button.active {
    color: #667eea;
    border-bottom-color: #667eea;
    font-weight: 600;
}

.tab-icon {
    font-size: 16px;
}

/* Sekme İçeriği */
.profile-tabs-content {
    position: relative;
}

.profile-tab-panel {
    display: none;
    animation: fadeIn 0.3s ease;
}

.profile-tab-panel.active {
    display: block;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Form Stilleri */
.profile-form {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    margin-bottom: 8px;
    font-weight: 600;
    color: #1a1a1a;
    font-size: 14px;
}

.form-group input,
.form-group textarea,
.form-group select {
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    font-family: inherit;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

@media (max-width: 600px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

/* Şehir Dropdown */
.form-group {
    position: relative;
}

.city-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ddd;
    border-top: none;
    border-radius: 0 0 8px 8px;
    max-height: 300px;
    overflow-y: auto;
    display: none;
    z-index: 100;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.city-dropdown.active {
    display: block;
}

.city-dropdown-item {
    padding: 12px;
    cursor: pointer;
    transition: background 0.2s;
    border-bottom: 1px solid #f0f0f0;
    font-size: 14px;
    color: #333;
}

.city-dropdown-item:hover {
    background: #f8f9ff;
}

.city-dropdown-empty {
    padding: 12px;
    text-align: center;
    color: #999;
    font-size: 13px;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 10px;
}

.btn-primary,
.btn-secondary,
.btn-danger {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5568d3;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-secondary {
    background: #f0f0f0;
    color: #1a1a1a;
}

.btn-secondary:hover {
    background: #e0e0e0;
}

.btn-danger {
    background: #d32f2f;
    color: white;
}

.btn-danger:hover {
    background: #b71c1c;
    box-shadow: 0 4px 12px rgba(211, 47, 47, 0.3);
}

/* E-posta Uyarıları */
.alert-setting {
    padding: 16px;
    background: #f8f9ff;
    border-radius: 8px;
    margin-bottom: 12px;
    border-left: 4px solid #667eea;
}

.alert-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.alert-header input[type="checkbox"] {
    width: 20px;
    height: 20px;
    margin-top: 2px;
    cursor: pointer;
}

.alert-header label {
    margin: 0;
    cursor: pointer;
    flex: 1;
}

.alert-header strong {
    display: block;
    margin-bottom: 4px;
    color: #1a1a1a;
}

.alert-description {
    font-size: 13px;
    color: #666;
    margin: 0;
}

/* Arama Uyarıları */
.search-alerts-list {
    margin-top: 20px;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    background: #f8f9ff;
    border-radius: 8px;
    border: 2px dashed #667eea;
}

.empty-state p {
    color: #666;
    margin: 0 0 16px 0;
}

/* Modal */
.search-alert-modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 12px;
    padding: 30px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 12px;
}

.modal-header h3 {
    margin: 0;
    font-size: 20px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    cursor: pointer;
    color: #666;
}

.modal-close:hover {
    color: #1a1a1a;
}

/* Sayfalama */
.pagination-container {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    margin-top: 20px;
    padding: 16px 0;
}

.pagination-btn {
    padding: 8px 16px;
    border: 1px solid #ddd;
    background: white;
    color: #333;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
}

.pagination-btn:hover:not(:disabled) {
    background: #667eea;
    color: white;
    border-color: #667eea;
}

.pagination-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.pagination-btn.active {
    background: #667eea;
    color: white;
    border-color: #667eea;
    font-weight: 600;
}

.pagination-info {
    font-size: 14px;
    color: #666;
    margin: 0 8px;
}

/* Ayarlar */
.settings-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e0e0e0;
}

.settings-section:last-child {
    border-bottom: none;
}

.section-title-small {
    font-size: 16px;
    font-weight: 600;
    color: #d32f2f;
    margin-bottom: 16px;
}

.setting-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #f8f9ff;
    border-radius: 8px;
    margin-bottom: 12px;
}

.setting-info h4 {
    margin: 0 0 4px 0;
    color: #1a1a1a;
    font-size: 14px;
}

.setting-info p {
    margin: 0;
    color: #666;
    font-size: 13px;
}

.toggle-switch {
    position: relative;
    display: inline-block;
    width: 50px;
    height: 28px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: 0.3s;
    border-radius: 28px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 22px;
    width: 22px;
    left: 3px;
    bottom: 3px;
    background-color: white;
    transition: 0.3s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: #667eea;
}

input:checked + .toggle-slider:before {
    transform: translateX(22px);
}

.danger-zone {
    padding: 20px;
    background: #fff3e0;
    border: 2px solid #ff9800;
    border-radius: 8px;
}

.danger-info h4 {
    margin: 0 0 8px 0;
    color: #d32f2f;
}

.danger-info p {
    margin: 0 0 16px 0;
    color: #666;
    font-size: 13px;
}

/* Mesajlar */
.form-message {
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 13px;
    display: none;
}

.form-message.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
    display: block;
}

.form-message.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
    display: block;
}

/* Loading */
.loading-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 999;
}

.spinner {
    width: 50px;
    height: 50px;
    border: 4px solid rgba(255, 255, 255, 0.3);
    border-top: 4px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

<script>
// Satıcı Profili Sayfa JavaScript
document.addEventListener('DOMContentLoaded', function() {

    const currentUser = {
        id: <?php echo $user_id; ?>,
        name: '<?php echo esc_js($current_user->display_name); ?>',
        email: '<?php echo esc_js($current_user->user_email); ?>'
    };

    // Sekme Navigasyonu
    document.querySelectorAll('.profile-tab-button').forEach(button => {
        button.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            console.log('[DEBUG] Sekme tıklandı:', tabName);
            switchTab(tabName);
        });
    });

    function switchTab(tabName) {
        console.log('[DEBUG] switchTab çalıştı:', tabName);
        // Tüm tab butonlarından active sınıfını kaldır
        document.querySelectorAll('.profile-tab-button').forEach(btn => {
            btn.classList.remove('active');
        });

        // Tüm tab panellerini gizle
        document.querySelectorAll('.profile-tab-panel').forEach(panel => {
            panel.classList.remove('active');
        });

        // Seçilen tab'a active sınıfını ekle
        document.querySelector(`[data-tab="${tabName}"]`)?.classList.add('active');
        document.getElementById(tabName)?.classList.add('active');

        // Tab-specific işlemleri yap
        if (tabName === 'profile-info') {
            console.log('[DEBUG] Profil bilgileri sekmesi yükleniyor...');
            loadProfileInfo();
        } else if (tabName === 'search-alerts') {
            console.log('[DEBUG] Arama uyarıları sekmesi yükleniyor...');
            loadSearchAlerts();
        }
    }

    // Sayfa ilk yüklendiğinde otomatik olarak profil bilgileri sekmesini yükle
    console.log('[DEBUG] Sayfa yüklendi, profil sekmesi otomatik yükleniyor...');
    switchTab('profile-info');

    // Profil Bilgileri Yükleme
    function loadProfileInfo() {
        const form = document.getElementById('profileInfoForm');
        if (!form.dataset.loaded) {
            form.dataset.loaded = true;

            // Şehirler dropdown'unu yükle
            loadProfileCities();

            // WordPress'ten user meta verilerini yükle
            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'ativ_load_profile_info',
                    _wpnonce: '<?php echo wp_create_nonce('ativ_profile_nonce'); ?>'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('profileName').value = data.data.name || '';
                    document.getElementById('profileCallsign').value = data.data.callsign || '';
                    document.getElementById('profileEmail').value = data.data.email || '';
                    document.getElementById('profilePhone').value = data.data.phone || '';
                    document.getElementById('profileLocation').value = data.data.location || '';
                }
            });
        }

        // Form gönder
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData();
            formData.append('action', 'ativ_save_profile_info');
            formData.append('_wpnonce', '<?php echo wp_create_nonce('ativ_profile_nonce'); ?>');
            
            // Form alanlarını manuel olarak ekle
            formData.append('name', document.getElementById('profileName').value);
            formData.append('callsign', document.getElementById('profileCallsign').value);
            formData.append('email', document.getElementById('profileEmail').value);
            formData.append('location', document.getElementById('profileLocation').value);
            formData.append('country_code', document.getElementById('profileCountryCode').value);
            formData.append('phone', document.getElementById('profilePhone').value);

            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                const msgDiv = document.getElementById('profileInfoMessage');
                if (data.success) {
                    msgDiv.textContent = '✅ Profil bilgileri başarıyla kaydedildi!';
                    msgDiv.className = 'form-message success';
                } else {
                    msgDiv.textContent = '❌ Hata: ' + (data.data || 'Bilinmeyen hata');
                    msgDiv.className = 'form-message error';
                }
                setTimeout(() => msgDiv.className = 'form-message', 3000);
            });
        });
    }

    // Profil Şehirleri Yükle
    let profileCitiesData = [];
    function loadProfileCities() {
        const ajaxUrl = (window.ajaxurl) || (window.ativAjaxUrl) || '/wp-admin/admin-ajax.php';
        fetch(`${ajaxUrl}?action=ativ_get_cities`)
            .then(r => r.json())
            .then(json => {
                if (!json || !json.success || !Array.isArray(json.data)) return;
                profileCitiesData = json.data;
                setupProfileCityDropdown();
            })
            .catch(() => {});
    }

    function setupProfileCityDropdown() {
        const input = document.getElementById('profileLocation');
        const dropdown = document.getElementById('cityDropdown');
        if (!input || !dropdown) return;

        // Dropdown item'larına click listener ekle
        function attachDropdownListeners() {
            dropdown.querySelectorAll('.city-dropdown-item').forEach(item => {
                item.addEventListener('click', function() {
                    input.value = this.dataset.city;
                    dropdown.classList.remove('active');
                });
            });
        }

        // Input'a yazınca filtreleme yap
        input.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            if (!query) {
                dropdown.classList.remove('active');
                return;
            }

            const filtered = profileCitiesData.filter(c => 
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

            // Render edildikten sonra listener'ları ekle
            attachDropdownListeners();
        });

        // Input focus olunca tüm şehirleri göster
        input.addEventListener('focus', function() {
            if (profileCitiesData.length > 0 && !this.value) {
                dropdown.innerHTML = profileCitiesData.map(c => 
                    `<div class="city-dropdown-item" data-city="${c.il_adi}">${c.il_adi}</div>`
                ).join('');
                dropdown.classList.add('active');

                // Render edildikten sonra listener'ları ekle
                attachDropdownListeners();
            }
        });

        // Input'tan ayrılınca dropdown kapat
        input.addEventListener('blur', function() {
            setTimeout(() => {
                dropdown.classList.remove('active');
            }, 200);
        });

        // Alandaki değer boşsa dropdown'u kapat
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdown.classList.remove('active');
            }
        });
    }

    // E-posta Uyarıları Yükleme
    function loadEmailAlerts() {
        const form = document.getElementById('emailAlertsForm');
        if (!form.dataset.loaded) {
            form.dataset.loaded = true;

            fetch(ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'ativ_load_email_alerts',
                    _wpnonce: '<?php echo wp_create_nonce('ativ_profile_nonce'); ?>'
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('alertNewRequests').checked = data.data.alert_new_requests;
                    document.getElementById('alertInquiries').checked = data.data.alert_inquiries;
                    document.getElementById('alertListingApproval').checked = data.data.alert_listing_approval;
                    document.getElementById('alertSystemNotifications').checked = data.data.alert_system_notifications;
                    document.getElementById('emailFrequency').value = data.data.email_frequency || 'immediate';
                }
            });
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(form);
            formData.append('action', 'ativ_save_email_alerts');
            formData.append('_wpnonce', '<?php echo wp_create_nonce('ativ_profile_nonce'); ?>');

            fetch(ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                const msgDiv = document.getElementById('emailAlertsMessage');
                if (data.success) {
                    msgDiv.textContent = '✅ E-posta ayarları başarıyla kaydedildi!';
                    msgDiv.className = 'form-message success';
                } else {
                    msgDiv.textContent = '❌ Hata: ' + (data.data || 'Bilinmeyen hata');
                    msgDiv.className = 'form-message error';
                }
                setTimeout(() => msgDiv.className = 'form-message', 3000);
            });
        });
    }

    // Arama Uyarıları Yükleme
    function loadSearchAlerts() {
        const listContainer = document.getElementById('searchAlertsList');
        if (!listContainer.dataset.loaded) {
            listContainer.dataset.loaded = true;

            fetch(ajaxurl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json'
                },
                body: new URLSearchParams({
                    action: 'ativ_load_search_alerts',
                    _wpnonce: '<?php echo wp_create_nonce('ativ_profile_nonce'); ?>'
                })
            })
            .then(res => res.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        renderSearchAlerts(data.data || []);
                    } else {
                        console.error('Search alerts API error:', data.data || data);
                        showSearchAlertMessage('❌ ' + (data.data || 'Arama uyarıları alınamadı'));
                    }
                } catch (err) {
                    console.error('Search alerts yanıtı JSON parse edilemedi:', err, text.slice(0, 200));
                    showSearchAlertMessage('❌ Sunucudan beklenmeyen yanıt. Oturum süresi dolmuş olabilir.');
                }
            })
            .catch(err => {
                console.error('Search alerts fetch hatası:', err);
                showSearchAlertMessage('❌ Ağ hatası.');
            });
        }

        // Modal kapatma
        document.getElementById('closeSearchAlertModal').addEventListener('click', function() {
            document.getElementById('searchAlertModal').style.display = 'none';
        });

        document.getElementById('cancelSearchAlertBtn').addEventListener('click', function() {
            document.getElementById('searchAlertModal').style.display = 'none';
        });

        // Dışarı tıklanınca modal kapanması
        document.getElementById('searchAlertModal').addEventListener('click', function(e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });

        // Yeni arama uyarısı formu (çift bağlanmayı önlemek için guard)
        const allListingsCheckbox = document.getElementById('alertAllListings');
        const keywordInput = document.getElementById('alertKeyword');
        if (allListingsCheckbox && keywordInput && !allListingsCheckbox.dataset.bound) {
            allListingsCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    keywordInput.value = '';
                    keywordInput.disabled = true;
                } else {
                    keywordInput.disabled = false;
                }
            });
            allListingsCheckbox.dataset.bound = '1';
        }

        const newAlertForm = document.getElementById('newSearchAlertForm');
        if (newAlertForm && !newAlertForm.dataset.bound) {
            newAlertForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);
                const alertId = formData.get('alert_id');
                const isEdit = alertId && String(alertId).trim() !== '';
                formData.append('action', isEdit ? 'ativ_update_search_alert' : 'ativ_save_search_alert');
                formData.append('_wpnonce', '<?php echo wp_create_nonce('ativ_profile_nonce'); ?>');

                // all_listings checkbox
                const allListings = document.getElementById('alertAllListings');
                formData.set('all_listings', allListings && allListings.checked ? '1' : '0');
                if (allListings && allListings.checked) {
                    formData.set('keyword', '');
                }

                fetch(ajaxurl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    body: formData
                })
                .then(res => res.text())
                .then(text => {
                    const msgDiv = document.getElementById('searchAlertMessage');
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            msgDiv.textContent = isEdit ? '✅ Arama uyarısı güncellendi!' : '✅ Arama uyarısı başarıyla oluşturuldu!';
                            msgDiv.className = 'form-message success';
                            setTimeout(() => {
                                document.getElementById('searchAlertModal').style.display = 'none';
                                resetAlertForm();
                                const listContainer = document.getElementById('searchAlertsList');
                                if (listContainer) listContainer.dataset.loaded = '';
                                window.alertsCurrentPage = 1; // Yeni uyarı eklendiğinde ilk sayfaya dön
                                loadSearchAlerts();
                            }, 1200);
                        } else {
                            msgDiv.textContent = '❌ Hata: ' + (data.data || 'Bilinmeyen hata');
                            msgDiv.className = 'form-message error';
                        }
                    } catch (err) {
                        console.error('Arama uyarısı kaydet yanıtı JSON parse edilemedi:', err, text.slice(0, 200));
                        const msgDiv = document.getElementById('searchAlertMessage');
                        if (msgDiv) {
                            msgDiv.textContent = '❌ Sunucudan beklenmeyen yanıt. Oturumunuz düşmüş olabilir, sayfayı yenileyin.';
                            msgDiv.className = 'form-message error';
                        }
                    }
                })
                .catch(err => {
                    console.error('Arama uyarısı kaydet fetch hatası:', err);
                    const msgDiv = document.getElementById('searchAlertMessage');
                    if (msgDiv) {
                        msgDiv.textContent = '❌ Ağ hatası. İnternet bağlantınızı kontrol edin.';
                        msgDiv.className = 'form-message error';
                    }
                });
            });
            newAlertForm.dataset.bound = '1';
        }

        bindAddSearchAlertButton();
    }

    function bindAddSearchAlertButton() {
        const btn = document.getElementById('addSearchAlertBtn');
        if (!btn || btn.dataset.bound === '1') return;
        btn.addEventListener('click', function() {
            // Formu temizle ve mesajı sil
            resetAlertForm();
            const msgDiv = document.getElementById('searchAlertMessage');
            if (msgDiv) {
                msgDiv.textContent = '';
                msgDiv.className = 'form-message';
            }
            document.getElementById('searchAlertModal').style.display = 'flex';
            populateAlertCities();
            populateAlertSellers();
        });
        btn.dataset.bound = '1';
    }

    function populateAlertCities() {
        const select = document.getElementById('alertLocation');
        if (!select) return;
        // Eğer zaten doldurulduysa tekrar yükleme
        if (select.dataset.loaded === '1') return;

        fetch(ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
            body: new URLSearchParams({ action: 'ativ_get_cities' })
        })
        .then(res => res.text())
        .then(text => {
            try {
                const resp = JSON.parse(text);
                select.innerHTML = '';
                const allOpt = document.createElement('option');
                allOpt.value = '';
                allOpt.textContent = 'Tümü';
                select.appendChild(allOpt);
                const cities = resp && resp.success && Array.isArray(resp.data) ? resp.data : [];
                cities.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c.il_adi;
                    opt.textContent = c.il_adi;
                    select.appendChild(opt);
                });
                select.dataset.loaded = '1';
            } catch (err) {
                console.error('Şehir listesi yanıtı JSON parse edilemedi:', err, text.slice(0, 200));
            }
        });
    }

    function populateAlertSellers() {
        const select = document.getElementById('alertSeller');
        if (!select) return;
        if (select.dataset.loaded === '1') return;

        fetch(ajaxurl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
            body: new URLSearchParams({ action: 'ativ_get_sellers' })
        })
        .then(res => res.text())
        .then(text => {
            try {
                const resp = JSON.parse(text);
                select.innerHTML = '';
                const allOpt = document.createElement('option');
                allOpt.value = '';
                allOpt.textContent = 'Tüm Satıcılar';
                select.appendChild(allOpt);
                if (resp.success && Array.isArray(resp.data)) {
                    resp.data.forEach(s => {
                        const opt = document.createElement('option');
                        opt.value = s.callsign;
                        opt.textContent = s.callsign;
                        select.appendChild(opt);
                    });
                }
                select.dataset.loaded = '1';
            } catch (err) {
                console.error('Satıcı listesi yanıtı JSON parse edilemedi:', err, text.slice(0, 200));
            }
        });
    }

    // Sayfalama değişkenleri
    window.alertsCurrentPage = window.alertsCurrentPage || 1;
    window.alertsPerPage = 3;

    function renderSearchAlerts(alerts) {
        const listContainer = document.getElementById('searchAlertsList');
        if (alerts.length === 0) {
            listContainer.innerHTML = `
                <div class="empty-state">
                    <p>Henüz arama uyarısı oluşturulmamış</p>
                    <button type="button" class="btn-secondary" id="addSearchAlertBtn">➕ Yeni Arama Uyarısı Ekle</button>
                </div>
            `;
            bindAddSearchAlertButton();
            return;
        }

        // Cache alerts for edit usage
        window.bitlikSearchAlerts = alerts;

        // Sayfalama hesaplamaları
        const totalAlerts = alerts.length;
        const totalPages = Math.ceil(totalAlerts / window.alertsPerPage);
        const currentPage = Math.min(window.alertsCurrentPage, totalPages);
        window.alertsCurrentPage = currentPage;
        
        const startIndex = (currentPage - 1) * window.alertsPerPage;
        const endIndex = Math.min(startIndex + window.alertsPerPage, totalAlerts);
        const pageAlerts = alerts.slice(startIndex, endIndex);

        const categoryMap = {
            'transceiver': 'Telsiz',
            'antenna': 'Anten',
            'amplifier': 'Amplifikatör',
            'accessory': 'Aksesuar',
            'other': 'Diğer',
            '': 'Tümü'
        };
        const freqMap = {
            'immediate': 'Anında (Hemen)',
            'daily': 'Günlük',
            'weekly': 'Haftalık'
        };

        const chip = (label) => `<span class="chip">${label}</span>`;

        let html = '<div class="search-alerts-header"><button type="button" class="btn-secondary" id="addSearchAlertBtn">➕ Yeni Arama Uyarısı Ekle</button></div>';
        html += '<div class="search-alerts-items">';
        pageAlerts.forEach(alert => {
            const categoryLabel = categoryMap[alert.category] || alert.category || 'Tümü';
            const freqLabel = freqMap[alert.frequency] || alert.frequency || '';
            const keywordLabel = alert.all_listings == 1 ? 'Tüm ilanlar' : (alert.keyword || '-');
            const sellerLabel = alert.seller_callsign || 'Tüm satıcılar';
            const locationLabel = alert.location || 'Tüm konumlar';
            const conditionLabel = alert.condition || 'Tümü';
            const priceLabel = formatPriceRange(alert.min_price, alert.max_price) || 'Belirtilmemiş';
            const created = alert.created_at ? new Date(alert.created_at).toLocaleString('tr-TR', {dateStyle: 'short', timeStyle: 'short'}) : '';

            html += `
                <div class="search-alert-card" data-id="${alert.id}">
                    <div class="alert-content" style="flex:1;">
                        <h4>${alert.alert_name}</h4>
                        <div class="alert-badges">
                            <span class="chip primary">${categoryLabel}</span>
                            <span class="chip accent">${freqLabel}</span>
                        </div>
                        <div class="alert-meta">
                            <div class="alert-detail-row">
                                <span class="label">Anahtar Kelime:</span>
                                <span class="value">${keywordLabel}</span>
                            </div>
                            <div class="alert-detail-row">
                                <span class="label">Satıcı:</span>
                                <span class="value">${sellerLabel}</span>
                            </div>
                            <div class="alert-detail-row">
                                <span class="label">Konum:</span>
                                <span class="value">${locationLabel}</span>
                            </div>
                            <div class="alert-detail-row">
                                <span class="label">Durum:</span>
                                <span class="value">${conditionLabel}</span>
                            </div>
                            <div class="alert-detail-row">
                                <span class="label">Fiyat:</span>
                                <span class="value">${priceLabel}</span>
                            </div>
                            ${created ? `<div class="alert-detail-row"><span class="chip muted">Oluşturma: ${created}</span></div>` : ''}
                        </div>
                    </div>
                    <div class="alert-actions">
                        <button class="btn-secondary edit-alert" data-id="${alert.id}">Düzenle</button>
                        <button class="btn-secondary delete-alert" data-id="${alert.id}">Sil</button>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        // Sayfalama kontrolleri
        if (totalPages > 1) {
            html += '<div class="pagination-container">';
            html += `<button class="pagination-btn" id="prevPageBtn" ${currentPage === 1 ? 'disabled' : ''}>← Önceki</button>`;
            
            // Sayfa numaraları
            for (let i = 1; i <= totalPages; i++) {
                if (i === currentPage) {
                    html += `<button class="pagination-btn active">${i}</button>`;
                } else if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) {
                    html += `<button class="pagination-btn page-num-btn" data-page="${i}">${i}</button>`;
                } else if (i === currentPage - 2 || i === currentPage + 2) {
                    html += '<span class="pagination-info">...</span>';
                }
            }
            
            html += `<button class="pagination-btn" id="nextPageBtn" ${currentPage === totalPages ? 'disabled' : ''}>Sonraki →</button>`;
            html += '</div>';
        }
        
        listContainer.innerHTML = html;

        // Silme düğmelerini bağla
        document.querySelectorAll('.delete-alert').forEach(btn => {
            btn.addEventListener('click', function() {
                deleteSearchAlert(this.getAttribute('data-id'));
            });
        });

        // Düzenleme düğmeleri
        document.querySelectorAll('.edit-alert').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                startEditAlert(id);
            });
        });

        // Sayfalama event listeners
        const prevBtn = document.getElementById('prevPageBtn');
        const nextBtn = document.getElementById('nextPageBtn');
        
        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                if (window.alertsCurrentPage > 1) {
                    window.alertsCurrentPage--;
                    renderSearchAlerts(window.bitlikSearchAlerts);
                    document.getElementById('searchAlertsList').scrollIntoView({ behavior: 'smooth' });
                }
            });
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                const totalPages = Math.ceil(window.bitlikSearchAlerts.length / window.alertsPerPage);
                if (window.alertsCurrentPage < totalPages) {
                    window.alertsCurrentPage++;
                    renderSearchAlerts(window.bitlikSearchAlerts);
                    document.getElementById('searchAlertsList').scrollIntoView({ behavior: 'smooth' });
                }
            });
        }
        
        document.querySelectorAll('.page-num-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                window.alertsCurrentPage = parseInt(this.getAttribute('data-page'));
                renderSearchAlerts(window.bitlikSearchAlerts);
                document.getElementById('searchAlertsList').scrollIntoView({ behavior: 'smooth' });
            });
        });

        bindAddSearchAlertButton();
    }

    function showSearchAlertMessage(msg) {
        const container = document.getElementById('searchAlertsList');
        if (!container) return;
        container.insertAdjacentHTML('afterbegin', `<div class="form-message error" style="margin-bottom:10px;">${msg}</div>`);
    }

    function formatPriceRange(min, max) {
        const toTL = (v) => {
            const num = Number(v);
            if (!num || num <= 0) return null;
            return num.toLocaleString('tr-TR') + ' TL';
        };
        const minVal = toTL(min);
        const maxVal = toTL(max);
        if (minVal && maxVal) return `${minVal} - ${maxVal}`;
        if (minVal) return `${minVal} ve üzeri`;
        if (maxVal) return `${maxVal} altı`;
        return '';
    }

    function startEditAlert(id) {
        const list = window.bitlikSearchAlerts || [];
        const alert = list.find(a => String(a.id) === String(id));
        if (!alert) return;
        const modal = document.getElementById('searchAlertModal');
        modal.style.display = 'flex';
        populateAlertCities();
        populateAlertSellers();

        document.getElementById('alertId').value = alert.id;
        document.getElementById('alertName').value = alert.alert_name || '';
        document.getElementById('alertCategory').value = alert.category || '';
        document.getElementById('alertCondition').value = alert.condition || '';
        document.getElementById('alertLocation').value = alert.location || '';
        document.getElementById('alertSeller').value = alert.seller_callsign || '';
        document.getElementById('alertKeyword').value = alert.keyword || '';
        document.getElementById('alertAllListings').checked = alert.all_listings == 1;
        document.getElementById('alertAllListings').dispatchEvent(new Event('change'));
        document.getElementById('alertMinPrice').value = alert.min_price || '';
        document.getElementById('alertMaxPrice').value = alert.max_price || '';
        document.getElementById('alertFrequency').value = alert.frequency || 'immediate';

        const submitBtn = document.querySelector('#newSearchAlertForm button.btn-primary');
        if (submitBtn) submitBtn.textContent = '💾 Uyarıyı Güncelle';
    }

    function resetAlertForm() {
        const form = document.getElementById('newSearchAlertForm');
        if (!form) return;
        form.reset();
        document.getElementById('alertId').value = '';
        const submitBtn = document.querySelector('#newSearchAlertForm button.btn-primary');
        if (submitBtn) submitBtn.textContent = '✅ Uyarı Oluştur';
        const keywordInput = document.getElementById('alertKeyword');
        if (keywordInput) keywordInput.disabled = false;
    }

    function deleteSearchAlert(alertId) {
        if (!confirm('Bu arama uyarısını silmek istediğinizden emin misiniz?')) return;

        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'ativ_delete_search_alert',
                alert_id: alertId,
                _wpnonce: '<?php echo wp_create_nonce('ativ_profile_nonce'); ?>'
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Silinen uyarıyı cacheden çıkar
                const currentAlerts = window.bitlikSearchAlerts || [];
                const remainingAlerts = currentAlerts.filter(a => String(a.id) !== String(alertId));
                
                // Mevcut sayfada kaç uyarı kaldığını kontrol et
                const currentPage = window.alertsCurrentPage;
                const startIndex = (currentPage - 1) * window.alertsPerPage;
                const alertsOnCurrentPage = remainingAlerts.slice(startIndex, startIndex + window.alertsPerPage).length;
                
                // Eğer mevcut sayfada uyarı kalmadıysa ve önceki sayfa varsa, bir önceki sayfaya git
                if (alertsOnCurrentPage === 0 && currentPage > 1) {
                    window.alertsCurrentPage = currentPage - 1;
                } else if (remainingAlerts.length === 0) {
                    // Hiç uyarı kalmadıysa 1. sayfaya dön
                    window.alertsCurrentPage = 1;
                }
                
                // Liste yüklemesini zorla (cache'i temizle)
                const listContainer = document.getElementById('searchAlertsList');
                if (listContainer) {
                    listContainer.dataset.loaded = '';
                }
                
                loadSearchAlerts();
            }
        })
        .catch(err => {
            console.error('Uyarı silme hatası:', err);
            alert('Uyarı silinirken bir hata oluştu.');
        });
    }
});
</script>

