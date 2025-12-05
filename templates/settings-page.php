<?php
/**
 * Template for Admin Settings Page
 * 
 * Variables available:
 * - $smtp_host, $smtp_port, $smtp_username, $smtp_password
 * - $smtp_from_name, $smtp_from_email
 * - $mail_template_listing_submitted, $mail_template_listing_approved, etc.
 * - $ativ_countries, $ativ_current_country
 * - $ativ_terms_text
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<div class="wrap ativ-settings-wrap">
    <style>
    .ativ-settings-wrap {
        background: #f8f9fa;
        padding: 20px 0 !important;
    }
    
    .ativ-settings-header {
        background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
        color: white;
        padding: 30px;
        border-radius: 8px;
        margin: 0 20px 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .ativ-settings-header h1 {
        color: white;
        margin: 0 0 10px 0;
        font-size: 28px;
    }
    
    .ativ-settings-tabs {
        display: flex;
        gap: 0;
        margin: 0 20px 30px;
        border-bottom: 2px solid #e0e0e0;
    }
    
    .ativ-settings-tab {
        padding: 12px 24px;
        background: white;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 600;
        color: #666;
        border-bottom: 3px solid transparent;
        transition: all 0.2s ease;
        margin-bottom: -2px;
    }
    
    .ativ-settings-tab:hover {
        color: #0073aa;
    }
    
    .ativ-settings-tab.active {
        color: #0073aa;
        border-bottom-color: #0073aa;
    }
    
    .ativ-settings-content {
        display: none;
        background: white;
        padding: 30px;
        border-radius: 8px;
        margin: 0 20px 30px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    
    .ativ-settings-content.active {
        display: block;
    }
    
    .ativ-form-group {
        margin-bottom: 25px;
    }
    
    .ativ-form-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 8px;
        color: #333;
        font-size: 14px;
    }
    
    .ativ-form-group input[type="text"],
    .ativ-form-group input[type="email"],
    .ativ-form-group input[type="number"],
    .ativ-form-group input[type="password"],
    .ativ-form-group textarea {
        width: 100%;
        padding: 10px 12px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        font-family: inherit;
        transition: all 0.2s ease;
        box-sizing: border-box;
    }
    
    .ativ-form-group input:focus,
    .ativ-form-group textarea:focus {
        outline: none;
        border-color: #0073aa;
        box-shadow: 0 0 0 4px rgba(0,115,170,0.1);
    }
    
    .ativ-form-group textarea {
        resize: vertical;
        min-height: 150px;
        font-family: 'Monaco', 'Menlo', monospace;
        font-size: 12px;
        line-height: 1.5;
    }
    
    .ativ-form-group .description {
        font-size: 12px;
        color: #999;
        margin-top: 5px;
    }
    
    .ativ-settings-section-title {
        font-size: 16px;
        font-weight: 700;
        color: #333;
        margin: 30px 0 20px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .ativ-settings-section-title:first-child {
        margin-top: 0;
    }
    
    .ativ-form-buttons {
        display: flex;
        gap: 10px;
        margin-top: 30px;
        padding-top: 20px;
        border-top: 2px solid #f0f0f0;
    }
    
    .ativ-btn-primary {
        background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
        color: white;
        border: none;
        padding: 12px 30px;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 14px;
    }
    
    .ativ-btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,115,170,0.3);
    }
    
    .ativ-info-box {
        background: #e3f2fd;
        border-left: 4px solid #0073aa;
        padding: 15px;
        border-radius: 6px;
        margin-bottom: 20px;
        font-size: 13px;
        color: #1565c0;
    }
    
    .ativ-info-box a {
        color: #0d47a1;
        font-weight: 600;
    }
    
    .ativ-template-variables {
        background: #f5f5f5;
        border: 1px solid #ddd;
        padding: 12px;
        border-radius: 6px;
        margin-bottom: 15px;
        font-size: 12px;
        color: #666;
    }
    
    .ativ-template-variables strong {
        color: #333;
    }
    
    /* Notice mesajları için metin rengi düzeltmesi */
    .notice, .notice p {
        color: #1a1a1a !important;
    }
    
    .notice-success {
        background: #ecf7ed;
        border-left-color: #46b450;
    }
    
    .notice-error {
        background: #fef7f7;
        border-left-color: #dc3232;
    }
    </style>
    
    <script>
        var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
        var ativSettingsNonce = '<?php echo wp_create_nonce('ativ_settings_nonce'); ?>';
    </script>
    
    <div class="ativ-settings-header">
        <h1>⚙️ Amatör Bitlik - Ayarlar</h1>
        <p>E-posta bildirimleri ve SMTP ayarlarını düzenleyin</p>
    </div>
    
    
    <form method="POST" action="">
        <?php wp_nonce_field('ativ_settings_nonce', 'ativ_settings_nonce'); ?>
        <input type="hidden" name="action" value="ativ_save_settings">
        <input type="hidden" name="active_tab" id="active_tab_field" value="smtp">
        
        <!-- Sekmeler -->
        <div class="ativ-settings-tabs">
            <button type="button" class="ativ-settings-tab active" onclick="switchTab(event, 'smtp')">📧 SMTP Ayarları</button>
            <button type="button" class="ativ-settings-tab" onclick="switchTab(event, 'templates')">📝 Mail Şablonları</button>
            <button type="button" class="ativ-settings-tab" onclick="switchTab(event, 'localization')">🌍 Lokalizasyon</button>
            <button type="button" class="ativ-settings-tab" onclick="switchTab(event, 'terms')">📜 Kullanıcı Sözleşmesi</button>
            <button type="button" class="ativ-settings-tab" onclick="switchTab(event, 'debug')">🔧 Debug & Cron</button>
        </div>
        
        <!-- SMTP Ayarları Sekmesi -->
        <div id="smtp" class="ativ-settings-content active">
            <h2>📧 SMTP Ayarları</h2>
            <p>E-posta göndermek için SMTP ayarlarını yapılandırın. Gmail kullanıyorsanız uygulama şifresi oluşturun.</p>
            
            <div class="ativ-info-box">
                💡 <strong>İpucu:</strong> Gmail için uygulama şifresi kullanmalısınız. <a href="https://support.google.com/accounts/answer/185833" target="_blank">Nasıl oluşturulur?</a>
            </div>
            
            <div class="ativ-form-group">
                <label for="smtp_host">SMTP Sunucusu</label>
                <input type="text" id="smtp_host" name="smtp_host" value="<?php echo esc_attr($smtp_host); ?>" placeholder="smtp.gmail.com">
                <div class="description">Örnek: smtp.gmail.com, mail.example.com</div>
            </div>
            
            <div class="ativ-form-group">
                <label for="smtp_port">SMTP Port</label>
                <input type="number" id="smtp_port" name="smtp_port" value="<?php echo esc_attr($smtp_port); ?>" placeholder="587">
                <div class="description">Gmail için: 587 (TLS) veya 465 (SSL)</div>
            </div>
            
            <div class="ativ-form-group">
                <label for="smtp_username">SMTP Kullanıcı Adı</label>
                <input type="email" id="smtp_username" name="smtp_username" value="<?php echo esc_attr($smtp_username); ?>" placeholder="your-email@gmail.com">
                <div class="description">Gmail için tam e-posta adresinizi girin</div>
            </div>
            
            <div class="ativ-form-group">
                <label for="smtp_password">SMTP Şifresi</label>
                <input type="password" id="smtp_password" name="smtp_password" value="<?php echo esc_attr($smtp_password); ?>" placeholder="••••••••">
                <div class="description">Gmail için uygulama şifresi (normal şifre değil)</div>
            </div>
            
            <div class="ativ-form-group">
                <button type="button" id="test-mail-btn" class="ativ-btn ativ-btn-primary">🧪 Test Mail Gönder</button>
                <div id="test-mail-result" class="test-mail-result"></div>
            </div>
            
            <div class="ativ-settings-section-title">Gönderen Bilgileri</div>
            
            <div class="ativ-form-group">
                <label for="smtp_from_name">Gönderen Adı</label>
                <input type="text" id="smtp_from_name" name="smtp_from_name" value="<?php echo esc_attr($smtp_from_name); ?>" placeholder="Amatör Bitlik">
                <div class="description">E-postaların "Gönderden" alanında görünecek ad</div>
            </div>
            
            <div class="ativ-form-group">
                <label for="smtp_from_email">Gönderen E-posta</label>
                <input type="email" id="smtp_from_email" name="smtp_from_email" value="<?php echo esc_attr($smtp_from_email); ?>" placeholder="noreply@example.com">
                <div class="description">E-postaları göndereceği e-posta adresi</div>
            </div>
        </div>
        
        <!-- Mail Şablonları Sekmesi -->
        <div id="templates" class="ativ-settings-content">
            <h2>📝 E-posta Şablonları</h2>
            <p>İlan gönderimi, onayı ve reddi için e-posta şablonlarını özelleştirin.</p>
            
            <div class="ativ-template-variables">
                <strong>Kullanılabilir Değişkenler:</strong><br>
                {title} - İlan başlığı<br>
                {category} - İlan kategorisi<br>
                {seller_name} - Satıcı adı<br>
                {listing_url} - İlana erişim linki<br>
                {rejection_reason} - Red nedeni (sadece red şablonunda)<br>
                {admin_email} - Yönetici e-postası
            </div>
            
            <div class="ativ-settings-section-title">İlan Gönderimi Şablonu</div>
            <div class="ativ-form-group">
                <label for="mail_template_listing_submitted">İlan gönderiildikçe kullanıcıya gönderilecek e-posta</label>
                <textarea id="mail_template_listing_submitted" name="mail_template_listing_submitted"><?php echo esc_textarea($mail_template_listing_submitted); ?></textarea>
            </div>
            
            <div class="ativ-settings-section-title">İlan Onayı Şablonu</div>
            <div class="ativ-form-group">
                <label for="mail_template_listing_approved">İlan onaylandığında kullanıcıya gönderilecek e-posta</label>
                <textarea id="mail_template_listing_approved" name="mail_template_listing_approved"><?php echo esc_textarea($mail_template_listing_approved); ?></textarea>
            </div>
            
            <div class="ativ-settings-section-title">İlan Reddi Şablonu</div>
            <div class="ativ-form-group">
                <label for="mail_template_listing_rejected">İlan reddedildiğinde kullanıcıya gönderilecek e-posta</label>
                <textarea id="mail_template_listing_rejected" name="mail_template_listing_rejected"><?php echo esc_textarea($mail_template_listing_rejected); ?></textarea>
            </div>
            
            <div class="ativ-settings-section-title">İlan Silinme Şablonları</div>
            
            <div class="ativ-form-group">
                <label for="mail_template_listing_deleted">Kullanıcı tarafından silindiğinde gönderilecek e-posta</label>
                <textarea id="mail_template_listing_deleted" name="mail_template_listing_deleted"><?php echo esc_textarea($mail_template_listing_deleted); ?></textarea>
            </div>
            
            <div class="ativ-form-group">
                <label for="mail_template_listing_deleted_by_admin">Yönetici tarafından silindiğinde gönderilecek e-posta</label>
                <textarea id="mail_template_listing_deleted_by_admin" name="mail_template_listing_deleted_by_admin"><?php echo esc_textarea($mail_template_listing_deleted_by_admin); ?></textarea>
            </div>
            
            <hr style="margin: 40px 0; border: none; border-top: 2px solid #ddd;">
            
            <h3 style="margin-top: 30px; color: #0073aa;">👮 Yönetici Bildirimleri</h3>
            <p style="color: #666; margin-bottom: 20px;">Yöneticiye gönderilen e-posta şablonlarını özelleştirin.</p>
            
            <div class="ativ-template-variables">
                <strong>Yönetici Bildirimleri için Kullanılabilir Değişkenler:</strong><br>
                {title} - İlan başlığı<br>
                {category} - İlan kategorisi<br>
                {seller_name} - Satıcı adı<br>
                {seller_email} - Satıcı e-postası<br>
                {price} - İlan fiyatı<br>
                {currency} - Para birimi<br>
                {listing_id} - İlan ID'si
            </div>
            
            <div class="ativ-settings-section-title">Yeni İlan Bildirimi Şablonu</div>
            <div class="ativ-form-group">
                <label for="mail_template_admin_new_listing">Yeni ilan gönderildiğinde yöneticiye gönderilecek e-posta</label>
                <textarea id="mail_template_admin_new_listing" name="mail_template_admin_new_listing"><?php echo esc_textarea($mail_template_admin_new_listing); ?></textarea>
            </div>
            
            <div class="ativ-settings-section-title">İlan Güncelleme Bildirimi Şablonu</div>
            <div class="ativ-form-group">
                <label for="mail_template_admin_listing_updated">Reddedilen/onaylı ilan güncellendiğinde yöneticiye gönderilecek e-posta</label>
                <textarea id="mail_template_admin_listing_updated" name="mail_template_admin_listing_updated"><?php echo esc_textarea($mail_template_admin_listing_updated); ?></textarea>
            </div>
        </div>
        
        <!-- Lokalizasyon Sekmesi -->
        <div id="localization" class="ativ-settings-content">
            <h2>🌍 Lokalizasyon</h2>
            <p>Şehir listelerinde kullanılacak ülkeyi seçin.</p>

            <div class="ativ-form-group">
                <label for="ativ_location_country">Konum Ülkesi</label>
                <select name="ativ_location_country" id="ativ_location_country" style="min-width:260px">
                    <option value="all" <?php selected($ativ_current_country, 'all'); ?>>Tüm Ülkeler</option>
                    <?php if ($ativ_countries) { foreach ($ativ_countries as $c) { if (!is_string($c) || $c === '') continue; ?>
                        <option value="<?php echo esc_attr($c); ?>" <?php selected($ativ_current_country, $c); ?>><?php echo esc_html($c); ?></option>
                    <?php } } ?>
                </select>
                <p class="description">Seçilen ülke, ilan formundaki şehir arama listesinde filtrelenir.</p>
            </div>
        </div>

        <!-- Kullanıcı Sözleşmesi Sekmesi -->
        <div id="terms" class="ativ-settings-content">
            <h2>📜 Kullanıcı Sözleşmesi</h2>
            <p>İlan formu gönderilmeden önce kullanıcıların kabul edeceği sözleşme metni.</p>

            <div class="ativ-info-box">
                💡 <strong>İpucu:</strong> Bu metin, ilan ekleme formunda onay kutusu ile gösterilir. HTML etiketleri kullanabilirsiniz.
            </div>

            <div class="ativ-form-group">
                <label for="ativ_terms_text">Sözleşme Metni</label>
                <textarea id="ativ_terms_text" name="ativ_terms_text" rows="20" style="font-family: inherit; font-size: 14px; line-height: 1.6; width: 100%; max-width: 100%;"><?php echo esc_textarea($ativ_terms_text); ?></textarea>
                <p class="description">İlan formunda gösterilecek kullanıcı sözleşmesi metni. Basit HTML etiketleri desteklenir. 
                <br><strong>Mevcut karakter sayısı:</strong> <?php echo strlen($ativ_terms_text); ?> karakter</p>
            </div>
        </div>

        <!-- Debug & Cron Sekmesi -->
        <div id="debug" class="ativ-settings-content">
            <h2>🔧 Debug & Cron Bilgileri</h2>
            <p>WordPress cron sisteminin ve döviz kurları güncelleme sisteminin durumu.</p>
            
            <div class="ativ-form-group">
                <h3>📊 Cron Jobs Durumu</h3>
                <?php
                global $wpdb;
                
                // WordPress cron jobs'larını al
                $crons = _get_cron_array();
                
                echo '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">';
                echo '<tr style="background: #f5f5f5; border-bottom: 1px solid #ddd;">';
                echo '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">🎯 Cron Job</th>';
                echo '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">⏱️ Sonraki Çalışma</th>';
                echo '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">📍 Durum</th>';
                echo '</tr>';
                
                if (!empty($crons)) {
                    foreach ($crons as $time => $cron) {
                        foreach ($cron as $hook => $details) {
                            if (strpos($hook, 'ativ') !== false) {
                                $next_run = date('Y-m-d H:i:s', $time);
                                $is_past = time() > $time;
                                $status = $is_past ? '⚠️ Beklemede' : '✅ Planlandı';
                                $status_color = $is_past ? '#ffc107' : '#28a745';
                                
                                echo '<tr style="border-bottom: 1px solid #ddd;">';
                                echo '<td style="padding: 10px; border: 1px solid #ddd;"><strong>' . esc_html($hook) . '</strong></td>';
                                echo '<td style="padding: 10px; border: 1px solid #ddd;">' . $next_run . '</td>';
                                echo '<td style="padding: 10px; border: 1px solid #ddd; background: ' . $status_color . '22; color: ' . $status_color . '; font-weight: bold;">' . $status . '</td>';
                                echo '</tr>';
                            }
                        }
                    }
                } else {
                    echo '<tr><td colspan="3" style="padding: 20px; text-align: center; color: #999;">❌ Hiç cron job bulunamadı</td></tr>';
                }
                
                echo '</table>';
                ?>
            </div>
            
            <div class="ativ-form-group">
                <h3>💱 Döviz Kurları Durumu</h3>
                <?php
                $rates_table = $wpdb->prefix . 'amator_telsiz_doviz_kurlari';
                $rates = $wpdb->get_results("SELECT currency, rate, updated_at FROM $rates_table ORDER BY updated_at DESC");
                
                echo '<table style="width: 100%; border-collapse: collapse; margin-top: 10px;">';
                echo '<tr style="background: #f5f5f5; border-bottom: 1px solid #ddd;">';
                echo '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">💱 Para Birimi</th>';
                echo '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">📈 Kur</th>';
                echo '<th style="padding: 10px; text-align: left; border: 1px solid #ddd;">🔄 Son Güncelleme</th>';
                echo '</tr>';
                
                foreach ($rates as $rate) {
                    $updated = new DateTime($rate->updated_at);
                    $now = new DateTime();
                    $diff = $now->diff($updated);
                    
                    $time_ago = '';
                    if ($diff->days > 0) $time_ago .= $diff->days . ' gün ';
                    if ($diff->h > 0) $time_ago .= $diff->h . ' saat ';
                    if ($diff->i > 0) $time_ago .= $diff->i . ' dakika ';
                    if (empty($time_ago)) $time_ago = 'Az önce';
                    
                    echo '<tr style="border-bottom: 1px solid #ddd;">';
                    echo '<td style="padding: 10px; border: 1px solid #ddd;"><strong>' . $rate->currency . '</strong></td>';
                    echo '<td style="padding: 10px; border: 1px solid #ddd;"><strong>' . number_format($rate->rate, 4) . ' ₺</strong></td>';
                    echo '<td style="padding: 10px; border: 1px solid #ddd;">' . $time_ago . 'önce (' . $rate->updated_at . ')</td>';
                    echo '</tr>';
                }
                
                echo '</table>';
                ?>
            </div>
            
            <div class="ativ-form-group">
                <h3>🧪 Test İşlemleri</h3>
                <p style="margin-bottom: 15px;">Manuel olarak döviz kurlarını güncellemek için aşağıdaki butona tıkla:</p>
                <button type="button" class="ativ-btn-primary" onclick="testExchangeRateUpdate()">🔄 Döviz Kurlarını Şimdi Güncelle</button>
                <div id="test-result" style="margin-top: 15px; padding: 15px; border-radius: 4px; display: none;"></div>
            </div>
            
            <script>
            function testExchangeRateUpdate() {
                const btn = event.target;
                btn.disabled = true;
                btn.textContent = '⏳ Güncelleniyor...';
                
                fetch(ajaxurl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=ativ_ajax&action_type=test_update_rates'
                })
                .then(r => r.json())
                .then(data => {
                    const resultDiv = document.getElementById('test-result');
                    if (data.success) {
                        resultDiv.style.background = '#d4edda';
                        resultDiv.style.color = '#155724';
                        resultDiv.style.border = '1px solid #c3e6cb';
                        resultDiv.innerHTML = '<strong>✅ Başarılı!</strong><br>' + data.data.message;
                    } else {
                        resultDiv.style.background = '#f8d7da';
                        resultDiv.style.color = '#721c24';
                        resultDiv.style.border = '1px solid #f5c6cb';
                        resultDiv.innerHTML = '<strong>❌ Hata!</strong><br>' + (data.data?.message || JSON.stringify(data.data));
                    }
                    resultDiv.style.display = 'block';
                    btn.disabled = false;
                    btn.textContent = '🔄 Döviz Kurlarını Şimdi Güncelle';
                })
                .catch(err => {
                    const resultDiv = document.getElementById('test-result');
                    resultDiv.style.background = '#f8d7da';
                    resultDiv.style.color = '#721c24';
                    resultDiv.style.border = '1px solid #f5c6cb';
                    resultDiv.innerHTML = '<strong>❌ Ağ Hatası!</strong><br>' + err.message;
                    resultDiv.style.display = 'block';
                    btn.disabled = false;
                    btn.textContent = '🔄 Döviz Kurlarını Şimdi Güncelle';
                });
            }
            </script>
        </div>
        
        <div class="ativ-form-buttons">
            <button type="submit" class="ativ-btn-primary">💾 Ayarları Kaydet</button>
        </div>
    </form>
</div>

<script>
function switchTab(e, tabName) {
    e.preventDefault();
    
    // Tüm sekmeler ve içerik gizle
    document.querySelectorAll('.ativ-settings-content').forEach(el => {
        el.classList.remove('active');
    });
    document.querySelectorAll('.ativ-settings-tab').forEach(el => {
        el.classList.remove('active');
    });
    
    // Seçili sekme ve içeriği göster
    document.getElementById(tabName).classList.add('active');
    e.target.classList.add('active');
    
    // URL hash'ini güncelle
    window.location.hash = tabName;
    
    // Hidden field'ı güncelle (form submit sonrası kullanılacak)
    document.getElementById('active_tab_field').value = tabName;
}

// Sayfa yüklendiğinde hash veya POST'tan gelen sekmeyi aç
document.addEventListener('DOMContentLoaded', function() {
    // Önce POST'tan gelen active_tab'ı kontrol et
    const postActiveTab = '<?php echo isset($_POST['active_tab']) ? esc_js($_POST['active_tab']) : ''; ?>';
    const hash = window.location.hash.substring(1);
    const targetTab = postActiveTab || hash || 'smtp';
    
    if (targetTab && document.getElementById(targetTab)) {
        // Tüm aktif sınıfları kaldır
        document.querySelectorAll('.ativ-settings-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.ativ-settings-tab').forEach(el => el.classList.remove('active'));
        
        // Seçili sekmeyi aktif yap
        document.getElementById(targetTab).classList.add('active');
        const tabButton = Array.from(document.querySelectorAll('.ativ-settings-tab')).find(btn => {
            return btn.getAttribute('onclick').includes("'" + targetTab + "'");
        });
        if (tabButton) {
            tabButton.classList.add('active');
        }
        
        // Hash'i de güncelle
        window.location.hash = targetTab;
        
        // Hidden field'ı güncelle
        document.getElementById('active_tab_field').value = targetTab;
    }
});

// Test Mail Buton İşlemleri
function setupTestMailButton() {
    console.log('[DEBUG] setupTestMailButton() çağrıldı');
    
    const testMailBtn = document.getElementById('test-mail-btn');
    console.log('[DEBUG] testMailBtn element bulundu:', !!testMailBtn);
    
    if (!testMailBtn) {
        console.warn('[DEBUG] test-mail-btn elementi bulunamadı!');
        return;
    }
    
    testMailBtn.addEventListener('click', function(e) {
        e.preventDefault();
        console.log('[DEBUG] Test Mail butonuna tıklandı');
        sendTestMail();
    });
}

function sendTestMail() {
    console.log('[DEBUG] sendTestMail() çağrıldı');
    
    const testMailBtn = document.getElementById('test-mail-btn');
    const resultDiv = document.getElementById('test-mail-result');
    const smtpUsername = document.getElementById('smtp_username').value;
    
    console.log('[DEBUG] testMailBtn:', testMailBtn);
    console.log('[DEBUG] resultDiv:', resultDiv);
    console.log('[DEBUG] smtpUsername:', smtpUsername);
    
    // Validation
    if (!smtpUsername) {
        console.warn('[DEBUG] SMTP username boş!');
        showTestMailResult('❌ Lütfen önce SMTP e-posta adresini girin!', 'error');
        return;
    }
    
    // Loading state
    testMailBtn.disabled = true;
    testMailBtn.textContent = '⏳ Gönderiliyor...';
    resultDiv.className = 'test-mail-result loading';
    resultDiv.innerHTML = 'Test e-postası gönderiliyor...';
    
    console.log('[DEBUG] Loading state ayarlandı');
    console.log('[DEBUG] ajaxurl:', ajaxurl);
    
    // AJAX isteği
    fetch(ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'ativ_ajax',
            action_type: 'test_send_mail',
            to_email: smtpUsername,
            _wpnonce: ativSettingsNonce
        })
    })
    .then(response => {
        console.log('[DEBUG] Response alındı:', response.status);
        if (!response.ok) {
            console.error('[DEBUG] HTTP error:', response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('[DEBUG] JSON parse başarılı:', data);
        testMailBtn.disabled = false;
        testMailBtn.textContent = '🧪 Test Mail Gönder';
        
        if (data.success) {
            console.log('[DEBUG] Success:', data.data.message);
            showTestMailResult(data.data.message, 'success');
        } else {
            console.log('[DEBUG] Error:', data.data?.message || JSON.stringify(data.data));
            showTestMailResult(data.data?.message || 'Bilinmeyen hata', 'error');
        }
    })
    .catch(error => {
        console.error('[DEBUG] AJAX Error:', error);
        testMailBtn.disabled = false;
        testMailBtn.textContent = '🧪 Test Mail Gönder';
        showTestMailResult('❌ İsteğinde hata: ' + error.message, 'error');
    });
}

function showTestMailResult(message, type) {
    console.log('[DEBUG] showTestMailResult():', message, type);
    
    const resultDiv = document.getElementById('test-mail-result');
    if (!resultDiv) {
        console.warn('[DEBUG] test-mail-result elementi bulunamadı!');
        return;
    }
    
    resultDiv.className = 'test-mail-result ' + type;
    resultDiv.innerHTML = message;
    console.log('[DEBUG] Sonuç gösterildi');
}

// Settings sayfasında test mail butonunu kur
document.addEventListener('DOMContentLoaded', function() {
    console.log('[DEBUG] DOMContentLoaded event triggered');
    setupTestMailButton();
});
</script>
