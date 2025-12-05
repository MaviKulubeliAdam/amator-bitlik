<?php
/**
 * Plugin Name: Amatör Telsiz İlan Vitrini
 * Description: Amatör telsiz ekipmanları için ilan vitrini
 * Version: 1.0
 * Author: Your Name
 */

// Kullanıcılar tablosu oluşturma
register_activation_hook(__FILE__, function() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'amator_bitlik_kullanıcılar';
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        callsign VARCHAR(50) NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        location VARCHAR(100) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        PRIMARY KEY (id),
        KEY user_id (user_id)
    ) $charset_collate;";
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
});

// AJAX ile kullanıcı kaydı ekleme
add_action('wp_ajax_amator_bitlik_add_user', function() {
    $required = ['user_id', 'callsign', 'name', 'email', 'location', 'phone'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            wp_send_json_error(['message' => 'Tüm alanlar zorunludur.']);
            wp_die();
        }
    }
    global $wpdb;
    $table_name = $wpdb->prefix . 'amator_bitlik_kullanıcılar';
    $result = $wpdb->insert($table_name, [
        'user_id'  => intval($_POST['user_id']),
        'callsign' => strtoupper(str_replace(' ', '', sanitize_text_field($_POST['callsign']))),
        'name'     => sanitize_text_field($_POST['name']),
        'email'    => sanitize_email($_POST['email']),
        'location' => sanitize_text_field($_POST['location']),
        'phone'    => sanitize_text_field($_POST['phone']),
    ], ['%d', '%s', '%s', '%s', '%s', '%s']);
    if ($result) {
        wp_send_json_success(['message' => 'Kayıt başarıyla eklendi.']);
    } else {
        wp_send_json_error(['message' => 'Kayıt eklenemedi.']);
    }
    wp_die();
});

// Şehirler tablosu oluşturma ve doldurma
register_activation_hook(__FILE__, function() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'amator_bitlik_sehirler';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) unsigned NOT NULL AUTO_INCREMENT,
        il_adi VARCHAR(100) NOT NULL,
        ulke VARCHAR(100) NOT NULL DEFAULT 'Türkiye',
        PRIMARY KEY (id),
        UNIQUE KEY il_unique (il_adi)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);

    // Eğer tablo boşsa 81 ili ekle
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    if ((int)$count === 0) {
        $cities = [
            'Adana','Adıyaman','Afyon','Ağrı','Aksaray','Amasya','Ankara','Antalya','Ardahan','Artvin','Aydın','Balıkesir','Bartın','Batman','Bayburt','Bilecik','Bingöl','Bitlis','Bolu','Burdur','Bursa','Çanakkale','Çankırı','Çorum','Denizli','Diyarbakır','Düzce','Edirne','Elazığ','Erzincan','Erzurum','Eskişehir','Gaziantep','Giresun','Gümüşhane','Hakkari','Hatay','Iğdır','Isparta','İstanbul','İzmir','Kahramanmaraş','Karabük','Karaman','Kars','Kastamonu','Kayseri','Kilis','Kırıkkale','Kırklareli','Kırşehir','Kocaeli','Konya','Kütahya','Malatya','Manisa','Mardin','Mersin','Muğla','Muş','Nevşehir','Niğde','Ordu','Osmaniye','Rize','Sakarya','Samsun','Şanlıurfa','Siirt','Sinop','Sivas','Şırnak','Tekirdağ','Tokat','Trabzon','Tunceli','Uşak','Van','Yalova','Yozgat','Zonguldak'
        ];
        foreach ($cities as $city) {
            $wpdb->insert($table_name, [
                'il_adi' => $city,
                'ulke' => 'Türkiye'
            ]);
        }
    }
});

// Şehirleri JSON dönen AJAX endpoint
add_action('wp_ajax_ativ_get_cities', function() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'amator_bitlik_sehirler';
    $selected_country = get_option('ativ_location_country', 'all');
    if ($selected_country && $selected_country !== 'all') {
        $rows = $wpdb->get_results($wpdb->prepare("SELECT il_adi, ulke FROM $table_name WHERE ulke = %s ORDER BY il_adi ASC", $selected_country), ARRAY_A);
    } else {
        $rows = $wpdb->get_results("SELECT il_adi, ulke FROM $table_name ORDER BY il_adi ASC", ARRAY_A);
    }
    wp_send_json_success($rows ?: []);
});
add_action('wp_ajax_nopriv_ativ_get_cities', function() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'amator_bitlik_sehirler';
    $selected_country = get_option('ativ_location_country', 'all');
    if ($selected_country && $selected_country !== 'all') {
        $rows = $wpdb->get_results($wpdb->prepare("SELECT il_adi, ulke FROM $table_name WHERE ulke = %s ORDER BY il_adi ASC", $selected_country), ARRAY_A);
    } else {
        $rows = $wpdb->get_results("SELECT il_adi, ulke FROM $table_name ORDER BY il_adi ASC", ARRAY_A);
    }
    wp_send_json_success($rows ?: []);
});

// (Lokalizasyon sayfası eklenti menüsüne taşındı - add_admin_menu üzerinden ekleniyor)

/**
 * Plugin Name: Amatör Bitlik
 * Description: Amatör telsiz ekipmanları için ilan panosu yönetim sistemi
 * Version: 1.1
 * Author: TA4AQG - Erkin Mercan
 * Text Domain: amator-bitlik
 * Domain Path: /languages
 */

// Güvenlik kontrolü
if (!defined('ABSPATH')) {
    exit;
}

// Eklenti sabitleri
define('ATIV_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ATIV_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('ATIV_UPLOAD_DIR', WP_CONTENT_DIR . '/plugins/amator-bitlik/uploads/');
define('ATIV_UPLOAD_URL', content_url() . '/plugins/amator-bitlik/uploads/');

class AmateurTelsizIlanVitrini {
    
    public function __construct() {
        add_action('plugins_loaded', array($this, 'load_textdomain'));
        add_action('init', array($this, 'init'));
        // CSS ve JS'i sadece shortcode kullanıldığında yükle
        add_shortcode('amator_telsiz_ilan', array($this, 'display_listings'));
        // Kullanıcının kendi ilanlarını gösteren shortcode
        add_shortcode('amator_my_listings', array($this, 'display_my_listings'));
        // Satıcı profil sayfası shortcode'u
        add_shortcode('amator_seller_profile', array($this, 'display_seller_profile'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Admin menüsü
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Döviz kuru güncelleme hook'u
        add_action('ativ_update_exchange_rates', array($this, 'update_exchange_rates_from_api'));
    }
    
    /**
     * Çeviri dosyalarını yükle
     */
    public function load_textdomain() {
        load_plugin_textdomain('amator-bitlik', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function init() {
        // AJAX işleyicileri kaydet
        add_action('wp_ajax_ativ_ajax', array($this, 'handle_ajax'));
        add_action('wp_ajax_nopriv_ativ_ajax', array($this, 'handle_ajax'));
        
        // Satıcı Profili AJAX Actions
        add_action('wp_ajax_ativ_load_profile_info', array($this, 'ajax_load_profile_info'));
        add_action('wp_ajax_ativ_save_profile_info', array($this, 'ajax_save_profile_info'));
        add_action('wp_ajax_ativ_load_email_alerts', array($this, 'ajax_load_email_alerts'));
        add_action('wp_ajax_ativ_save_email_alerts', array($this, 'ajax_save_email_alerts'));
        add_action('wp_ajax_ativ_load_search_alerts', array($this, 'ajax_load_search_alerts'));
        add_action('wp_ajax_ativ_save_search_alert', array($this, 'ajax_save_search_alert'));
        add_action('wp_ajax_ativ_delete_search_alert', array($this, 'ajax_delete_search_alert'));
        add_action('wp_ajax_get_user_listings', array($this, 'ajax_get_user_listings'));
        
        // Custom cron interval'ı tanımla (6 saat)
        add_filter('cron_schedules', array($this, 'add_custom_cron_schedules'));
        
        // Her 6 saatte bir döviz kurlarını güncelle (cron job)
        if (!wp_next_scheduled('ativ_update_exchange_rates')) {
            wp_schedule_event(time(), 'sixhours', 'ativ_update_exchange_rates');
        }
        
        // Her 1 saatte bir temp videoları temizle (cron job)
        if (!wp_next_scheduled('ativ_cleanup_temp_videos')) {
            wp_schedule_event(time(), 'hourly', 'ativ_cleanup_temp_videos');
        }
        
        // Cron job hook'ları
        add_action('ativ_cleanup_temp_videos', array($this, 'cleanup_old_temp_videos'));
        
        // İlk kez açılışta kur güncelle
        $last_update = get_transient('ativ_exchange_rates_updated');
        if (!$last_update) {
            $this->update_exchange_rates_from_api();
        }
    }
    
    /**
     * Custom cron interval'ları ekle
     */
    public function add_custom_cron_schedules($schedules) {
        if (!isset($schedules['sixhours'])) {
            $schedules['sixhours'] = array(
                'interval' => 6 * 3600, // 6 saat (saniye cinsinden)
                'display'  => esc_html__('Her 6 Saat'),
            );
        }
        return $schedules;
    }
    
    public function activate() {
        $this->create_tables();
        $this->create_upload_dir();
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        // Cron job'ları temizle
        $timestamp = wp_next_scheduled('ativ_update_exchange_rates');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ativ_update_exchange_rates');
        }
        
        $timestamp2 = wp_next_scheduled('ativ_cleanup_temp_videos');
        if ($timestamp2) {
            wp_unschedule_event($timestamp2, 'ativ_cleanup_temp_videos');
        }
        
        flush_rewrite_rules();
    }
    
    private function create_upload_dir() {
        if (!file_exists(ATIV_UPLOAD_DIR)) {
            wp_mkdir_p(ATIV_UPLOAD_DIR);
        }
        
        // Güvenlik için .htaccess dosyası oluştur - Görsel ve video dosyalarına izin ver
        $htaccess_file = ATIV_UPLOAD_DIR . '.htaccess';
        $htaccess_content = 'Options -Indexes' . PHP_EOL .
                            'RewriteEngine On' . PHP_EOL .
                            PHP_EOL .
                            '# Görsel ve video dosyalarına erişime izin ver' . PHP_EOL .
                            '<FilesMatch "\.(jpg|jpeg|png|gif|webp|mp4|webm|JPG|JPEG|PNG|GIF|WEBP|MP4|WEBM)$">' . PHP_EOL .
                            '    Order allow,deny' . PHP_EOL .
                            '    Allow from all' . PHP_EOL .
                            '</FilesMatch>' . PHP_EOL .
                            PHP_EOL .
                            '# Tehlikeli dosya türlerini engelle' . PHP_EOL .
                            '<FilesMatch "\.(php|phtml|php3|php4|php5|php7|phps|cgi|pl|asp|aspx|shtml|shtm|fcgi|exe|com|bat|sh|py|rb|htaccess|htpasswd|ini|log|sql)$">' . PHP_EOL .
                            '    Order deny,allow' . PHP_EOL .
                            '    Deny from all' . PHP_EOL .
                            '</FilesMatch>' . PHP_EOL .
                            PHP_EOL .
                            '# Varsayılan olarak diğer dosya türlerini engelle' . PHP_EOL .
                            '<FilesMatch "^.*$">' . PHP_EOL .
                            '    Order deny,allow' . PHP_EOL .
                            '    Deny from all' . PHP_EOL .
                            '</FilesMatch>' . PHP_EOL .
                            PHP_EOL .
                            '# Tekrar görsel ve video dosyalarına izin ver (üstteki kural için override)' . PHP_EOL .
                            '<FilesMatch "\.(jpg|jpeg|png|gif|webp|mp4|webm|JPG|JPEG|PNG|GIF|WEBP|MP4|WEBM)$">' . PHP_EOL .
                            '    Order allow,deny' . PHP_EOL .
                            '    Allow from all' . PHP_EOL .
                            '</FilesMatch>';
        
        // Mevcut .htaccess'i güncelle veya yeni oluştur
        file_put_contents($htaccess_file, $htaccess_content);
    
        // Güvenlik için index.html dosyası oluştur
        $index_file = ATIV_UPLOAD_DIR . 'index.html';
        if (!file_exists($index_file)) {
            file_put_contents($index_file, '<!-- Silence is golden -->');
        }
        
        // Temp klasörü oluştur
        $temp_dir = ATIV_UPLOAD_DIR . 'temp/';
        if (!file_exists($temp_dir)) {
            wp_mkdir_p($temp_dir);
        }
    }
    
    private function create_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    // Tablo 1: İlanlar tablosu
    $table_name = $wpdb->prefix . 'amator_ilanlar';
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        title varchar(255) NOT NULL,
        category enum('transceiver', 'antenna', 'amplifier', 'accessory', 'other') NOT NULL,
        brand varchar(100) NOT NULL,
        model varchar(100) NOT NULL,
        `condition` enum('Sıfır', 'Kullanılmış', 'Arızalı', 'El Yapımı') NOT NULL,
        price decimal(10,2) NOT NULL,
        old_price decimal(10,2) DEFAULT NULL,
        currency enum('TRY', 'USD', 'EUR') DEFAULT 'TRY',
        description longtext NOT NULL,
        images longtext,
        featured_image_index int(11) DEFAULT 0,
        video longtext,
        emoji varchar(10),
        callsign varchar(20) NOT NULL,
        seller_name varchar(100) NOT NULL,
        location varchar(100) NOT NULL,
        seller_email varchar(100) NOT NULL,
        seller_phone varchar(20) NOT NULL,
        status enum('pending', 'approved', 'rejected') DEFAULT 'pending',
        rejection_reason longtext,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        INDEX user_id (user_id),
        INDEX status (status)
    ) $charset_collate;";
    
    dbDelta($sql);
    
    if (!empty($wpdb->last_error)) {
        error_log('ATIV İlanlar tablosu oluşturma hatası: ' . $wpdb->last_error);
    }
    
    // Tablo 2: SMTP Ayarları tablosu
    $settings_table = $wpdb->prefix . 'amator_telsiz_ayarlar';
    
    $sql_settings = "CREATE TABLE $settings_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        smtp_host varchar(255) DEFAULT 'smtp.gmail.com',
        smtp_port int(11) DEFAULT 587,
        smtp_username varchar(255),
        smtp_password varchar(255),
        smtp_from_name varchar(255) DEFAULT 'Amatör Bitlik',
        smtp_from_email varchar(255),
        enable_notifications tinyint(1) DEFAULT 1,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    dbDelta($sql_settings);
    
    if (!empty($wpdb->last_error)) {
        error_log('ATIV Ayarlar tablosu oluşturma hatası: ' . $wpdb->last_error);
    }
    
    // Tablo 3: Mail Şablonları tablosu
    $templates_table = $wpdb->prefix . 'amator_telsiz_sablonlar';
    
    $sql_templates = "CREATE TABLE $templates_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        template_key varchar(100) NOT NULL,
        template_name varchar(255) NOT NULL,
        template_subject varchar(255) NOT NULL,
        template_body longtext NOT NULL,
        template_description varchar(500),
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_template_key (template_key)
    ) $charset_collate;";
    
    
    dbDelta($sql_templates);
    
    if (!empty($wpdb->last_error)) {
        error_log('ATIV Şablonlar tablosu oluşturma hatası: ' . $wpdb->last_error);
    }
    
    // Tablo 4: Döviz Kurları tablosu
    $exchange_rates_table = $wpdb->prefix . 'amator_telsiz_doviz_kurlari';
    
    $sql_exchange_rates = "CREATE TABLE $exchange_rates_table (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        currency varchar(10) NOT NULL,
        rate decimal(10,4) NOT NULL DEFAULT 1,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_currency (currency)
    ) $charset_collate;";
    
    dbDelta($sql_exchange_rates);
    
    if (!empty($wpdb->last_error)) {
        error_log('ATIV Döviz Kurları tablosu oluşturma hatası: ' . $wpdb->last_error);
    }
    
    // Varsayılan döviz kurlarını ekle
    $this->insert_default_exchange_rates();
    
    // Varsayılan şablonları ekle (eğer yoksa)
    $this->insert_default_templates();
}
    
   private function enqueue_scripts() {
    // CSS dosyalarını kaydet ve yükle
    wp_enqueue_style('ativ-base', ATIV_PLUGIN_URL . 'css/base.css', array(), '1.2');
    wp_enqueue_style('ativ-components', ATIV_PLUGIN_URL . 'css/components.css', array('ativ-base'), '1.2');
    wp_enqueue_style('ativ-forms', ATIV_PLUGIN_URL . 'css/forms.css', array('ativ-components'), '1.2');
    
    // JS dosyalarını kaydet ve yükle (sıralama önemli)
    wp_enqueue_script('ativ-core', ATIV_PLUGIN_URL . 'js/core.js', array('jquery'), '1.2', true);
    wp_enqueue_script('ativ-ui', ATIV_PLUGIN_URL . 'js/ui.js', array('ativ-core'), '1.2', true);
    wp_enqueue_script('ativ-modal', ATIV_PLUGIN_URL . 'js/modal.js', array('ativ-ui'), '1.2', true);
    wp_enqueue_script('ativ-terms', ATIV_PLUGIN_URL . 'js/terms.js', array('ativ-modal'), '1.2', true);
    
    // AJAX parametrelerini JavaScript'e aktar
    $current_user_id = get_current_user_id();
    
    wp_localize_script('ativ-core', 'ativ_ajax', array(
        'url' => admin_url('admin-ajax.php'),
        'nonce' => $current_user_id ? wp_create_nonce('ativ_nonce_' . $current_user_id) : wp_create_nonce('ativ_public_nonce'),
        'public_nonce' => wp_create_nonce('ativ_public_nonce'),
        'upload_url' => ATIV_UPLOAD_URL,
        'is_user_logged_in' => is_user_logged_in(),
        'user_id' => $current_user_id
    ));
}
    
    public function display_listings() {
        // Script ve style'ları yükle
        $this->enqueue_scripts();
        
        // Kullanıcı sözleşmesi metnini veritabanından çek
        global $ativ_terms_content;
        $ativ_terms_content = $this->get_template_body('user_terms', 'user_terms');
        
        ob_start();
        ?>
        <div id="ativ-container">
            <?php 
            // Sadece oturum açmış kullanıcılar için ilan ekleme butonunu göster
            $show_add_button = is_user_logged_in();
            include ATIV_PLUGIN_PATH . 'templates/index.php'; 
            ?>
        </div>
        <?php
        return ob_get_clean();
    }

    // Kullanıcının kendi ilanlarını gösteren sayfa (shortcode)
    public function display_my_listings() {
        if (!is_user_logged_in()) {
            return '<div class="ativ-my-listings-message">Bu sayfaya erişmek için <a href="' . wp_login_url(get_permalink()) . '">giriş yapmalısınız</a>.</div>';
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'amator_ilanlar';
        $user_id = get_current_user_id();

        $my_listings = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at DESC", $user_id), ARRAY_A);

        if ($wpdb->last_error) {
            return '<div class="ativ-error">Veritabanı hatası: ' . esc_html($wpdb->last_error) . '</div>';
        }

        // Görselleri URL formatına çevir
        foreach ($my_listings as &$listing) {
            $listing['images'] = $this->get_listing_images($listing['id'], $listing['images']);
        }
        unset($listing); // Reference'i temizle

        // Script ve style'ları yükle
        $this->enqueue_scripts();

        // Kullanıcı sözleşmesi metnini veritabanından çek
        global $ativ_terms_content;
        $ativ_terms_content = $this->get_template_body('user_terms', 'user_terms');

        ob_start();
        include ATIV_PLUGIN_PATH . 'templates/my-listings.php';
        return ob_get_clean();
    }

    public function display_seller_profile() {
        if (!is_user_logged_in()) {
            return '<div class="ativ-message">Erişim için <a href="' . wp_login_url(get_permalink()) . '">giriş yapmalısınız</a>.</div>';
        }

        // Script ve style'ları yükle
        $this->enqueue_scripts();

        ob_start();
        include ATIV_PLUGIN_PATH . 'templates/seller-profile.php';
        return ob_get_clean();
    }

    // ========== SATICI PROFİLİ AJAX HANDLERS ==========

    public function ajax_load_profile_info() {
        if (!is_user_logged_in()) {
            wp_send_json_error('Giriş yapmalısınız');
        }

        check_ajax_referer('ativ_profile_nonce', '_wpnonce');

        $user_id = get_current_user_id();
        $current_user = get_user_by('id', $user_id);

        // Çağrı işareti: meta varsa onu, yoksa username'i kullan
        $callsign_meta = get_user_meta($user_id, 'ativ_profile_callsign', true);
        $callsign = $callsign_meta;
        if (empty($callsign)) {
            $callsign = strtoupper(str_replace(' ', '', $current_user->user_login));
        } else {
            $callsign = strtoupper(str_replace(' ', '', $callsign));
        }

        $profile_data = array(
            'name' => $current_user->display_name,
            'email' => $current_user->user_email,
            'callsign' => $callsign,
            'phone' => get_user_meta($user_id, 'ativ_profile_phone', true),
            'location' => get_user_meta($user_id, 'ativ_profile_location', true)
        );

        wp_send_json_success($profile_data);
    }

    public function ajax_save_profile_info() {
        if (!is_user_logged_in()) {
            wp_send_json_error('Giriş yapmalısınız');
        }

        check_ajax_referer('ativ_profile_nonce', '_wpnonce');

        $user_id = get_current_user_id();

        // Yalnızca güvenli alanları güncelle
        if (isset($_POST['name']) && !empty($_POST['name'])) {
            wp_update_user(array(
                'ID' => $user_id,
                'display_name' => sanitize_text_field($_POST['name'])
            ));
        }

        // User meta'yı güncelle
        update_user_meta($user_id, 'ativ_profile_callsign', sanitize_text_field($_POST['callsign'] ?? ''));
        update_user_meta($user_id, 'ativ_profile_phone', sanitize_text_field($_POST['phone'] ?? ''));
        update_user_meta($user_id, 'ativ_profile_location', sanitize_text_field($_POST['location'] ?? ''));

        wp_send_json_success('Profil güncellendi');
    }

    public function ajax_load_email_alerts() {
        if (!is_user_logged_in()) {
            wp_send_json_error('Giriş yapmalısınız');
        }

        check_ajax_referer('ativ_profile_nonce', '_wpnonce');

        $user_id = get_current_user_id();

        $alerts_data = array(
            'alert_new_requests' => (bool)get_user_meta($user_id, 'ativ_alert_new_requests', true) ?: true,
            'alert_inquiries' => (bool)get_user_meta($user_id, 'ativ_alert_inquiries', true) ?: true,
            'alert_listing_approval' => (bool)get_user_meta($user_id, 'ativ_alert_listing_approval', true) ?: true,
            'alert_system_notifications' => (bool)get_user_meta($user_id, 'ativ_alert_system_notifications', true) ?: true,
            'email_frequency' => get_user_meta($user_id, 'ativ_email_frequency', true) ?: 'immediate'
        );

        wp_send_json_success($alerts_data);
    }

    public function ajax_save_email_alerts() {
        if (!is_user_logged_in()) {
            wp_send_json_error('Giriş yapmalısınız');
        }

        check_ajax_referer('ativ_profile_nonce', '_wpnonce');

        $user_id = get_current_user_id();

        update_user_meta($user_id, 'ativ_alert_new_requests', isset($_POST['alert_new_requests']) ? 1 : 0);
        update_user_meta($user_id, 'ativ_alert_inquiries', isset($_POST['alert_inquiries']) ? 1 : 0);
        update_user_meta($user_id, 'ativ_alert_listing_approval', isset($_POST['alert_listing_approval']) ? 1 : 0);
        update_user_meta($user_id, 'ativ_alert_system_notifications', isset($_POST['alert_system_notifications']) ? 1 : 0);
        update_user_meta($user_id, 'ativ_email_frequency', sanitize_text_field($_POST['email_frequency'] ?? 'immediate'));

        wp_send_json_success('Ayarlar güncellendi');
    }

    public function ajax_load_search_alerts() {
        if (!is_user_logged_in()) {
            wp_send_json_error('Giriş yapmalısınız');
        }

        check_ajax_referer('ativ_profile_nonce', '_wpnonce');

        global $wpdb;
        $user_id = get_current_user_id();
        $table_name = $wpdb->prefix . 'amator_search_alerts';

        // Tablo yoksa boş döndür
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
            wp_send_json_success(array());
        }

        $alerts = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at DESC",
            $user_id
        ), ARRAY_A);

        wp_send_json_success($alerts ?: array());
    }

    public function ajax_save_search_alert() {
        if (!is_user_logged_in()) {
            wp_send_json_error('Giriş yapmalısınız');
        }

        check_ajax_referer('ativ_profile_nonce', '_wpnonce');

        global $wpdb;
        $user_id = get_current_user_id();
        $table_name = $wpdb->prefix . 'amator_search_alerts';

        // Tablo yoksa oluştur
        $this->create_search_alerts_table();

        $result = $wpdb->insert($table_name, array(
            'user_id' => $user_id,
            'alert_name' => sanitize_text_field($_POST['alert_name'] ?? ''),
            'category' => sanitize_text_field($_POST['category'] ?? ''),
            'condition' => sanitize_text_field($_POST['condition'] ?? ''),
            'brand' => sanitize_text_field($_POST['brand'] ?? ''),
            'location' => sanitize_text_field($_POST['location'] ?? ''),
            'min_price' => intval($_POST['min_price'] ?? 0),
            'max_price' => intval($_POST['max_price'] ?? 0),
            'frequency' => sanitize_text_field($_POST['frequency'] ?? 'immediate'),
            'created_at' => current_time('mysql')
        ), array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s'));

        if ($result) {
            wp_send_json_success('Arama uyarısı oluşturuldu');
        } else {
            wp_send_json_error('Veritabanı hatası: ' . $wpdb->last_error);
        }
    }

    public function ajax_delete_search_alert() {
        if (!is_user_logged_in()) {
            wp_send_json_error('Giriş yapmalısınız');
        }

        check_ajax_referer('ativ_profile_nonce', '_wpnonce');

        global $wpdb;
        $user_id = get_current_user_id();
        $alert_id = intval($_POST['alert_id'] ?? 0);
        $table_name = $wpdb->prefix . 'amator_search_alerts';

        $wpdb->delete($table_name, array(
            'id' => $alert_id,
            'user_id' => $user_id
        ), array('%d', '%d'));

        wp_send_json_success('Arama uyarısı silindi');
    }

    /**
     * Kullanıcının ilanlarını getir (Admin için)
     */
    public function ajax_get_user_listings() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Yetkiniz yok');
        }

        global $wpdb;
        $user_id = intval($_GET['user_id'] ?? 0);
        $table_name = $wpdb->prefix . 'amator_ilanlar';

        $listings = $wpdb->get_results($wpdb->prepare(
            "SELECT id, title, category, price, currency, status, created_at 
             FROM $table_name 
             WHERE user_id = %d 
             ORDER BY created_at DESC",
            $user_id
        ));

        wp_send_json_success($listings);
    }

    private function create_search_alerts_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'amator_search_alerts';
        $charset_collate = $wpdb->get_charset_collate();

        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
            return;
        }

        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            alert_name varchar(255) NOT NULL,
            category varchar(100),
            condition varchar(100),
            brand varchar(255),
            location varchar(255),
            min_price int(11) DEFAULT 0,
            max_price int(11) DEFAULT 0,
            frequency varchar(20) DEFAULT 'immediate',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    public function handle_ajax() {
        $action = $_POST['action_type'] ?? $_REQUEST['action'] ?? '';
        
        // Admin edit modal için AJAX
        if ($action === 'ativ_get_listing_for_admin') {
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Yetkisiz erişim');
            }
            
            // GÜVENLİK: Admin işlemleri için nonce kontrolü
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'ativ_admin_nonce')) {
                wp_send_json_error('Güvenlik doğrulaması başarısız');
            }
            
            $id = intval($_POST['id'] ?? 0);
            $listing = $this->get_listing_by_id($id);
            
            if (!$listing) {
                wp_send_json_error('İlan bulunamadı');
            }
            
            // Form HTML'i oluştur
            $form_html = $this->generate_admin_edit_form($listing);
            wp_send_json_success($form_html);
        }
        
        // Admin tarafından ilanı güncelle
        if ($action === 'ativ_update_listing_admin') {
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Yetkisiz erişim');
            }
            
            // GÜVENLİK: Admin işlemleri için nonce kontrolü
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'ativ_admin_nonce')) {
                wp_send_json_error('Güvenlik doğrulaması başarısız');
            }
            
            $id = intval($_POST['id'] ?? 0);
            $this->update_listing_admin($id, $_POST);
            wp_send_json_success('İlan güncellendi');
        }
        
        // İlan durumunu değiştir (onay/reddet)
        if ($action === 'ativ_change_listing_status') {
            if (!current_user_can('manage_options')) {
                wp_send_json_error('Yetkisiz erişim');
            }
            
            // GÜVENLİK: Admin işlemleri için nonce kontrolü
            if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'ativ_admin_nonce')) {
                wp_send_json_error('Güvenlik doğrulaması başarısız');
            }
            
            $id = intval($_POST['id'] ?? 0);
            $status = sanitize_text_field($_POST['status'] ?? '');
            $rejection_reason = isset($_POST['rejection_reason']) ? wp_kses_post($_POST['rejection_reason']) : '';
            
            if (!in_array($status, ['approved', 'rejected', 'pending'])) {
                wp_send_json_error('Geçersiz durum');
            }
            
            global $wpdb;
            $table_name = $wpdb->prefix . 'amator_ilanlar';
            
            // İlan bilgilerini al
            $listing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
            if (!$listing) {
                wp_send_json_error('İlan bulunamadı');
            }
    
        
        $update_data = array('status' => $status);
        if ($status === 'rejected') {
            $update_data['rejection_reason'] = $rejection_reason;
        } else {
            $update_data['rejection_reason'] = null;
        }
        
        $result = $wpdb->update($table_name, $update_data, array('id' => $id));
        
        if ($result !== false) {
            // Duruma göre mail gönder
            if ($status === 'approved') {
                $this->send_notification('listing_approved', array(
                    'title' => stripslashes(htmlspecialchars_decode($listing['title'], ENT_QUOTES)),
                    'seller_name' => $listing['seller_name'],
                    'category' => $this->get_category_name($listing['category']),
                    'listing_id' => $id
                ), $listing['seller_email']);
            } elseif ($status === 'rejected') {
                $this->send_notification('listing_rejected', array(
                    'title' => stripslashes(htmlspecialchars_decode($listing['title'], ENT_QUOTES)),
                    'seller_name' => $listing['seller_name'],
                    'category' => $this->get_category_name($listing['category']),
                    'rejection_reason' => $rejection_reason,
                    'admin_email' => get_option('admin_email'),
                    'listing_id' => $id
                ), $listing['seller_email']);
            }
            
            wp_send_json_success('Durum güncellendi');
        } else {
            wp_send_json_error('Durum güncellenirken hata oluştu');
        }
    }
    
    // Admin ilanı silme
    if ($action === 'ativ_delete_listing_admin') {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Yetkisiz erişim');
        }
        
        // GÜVENLİK: Admin işlemleri için nonce kontrolü
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'ativ_admin_nonce')) {
            wp_send_json_error('Güvenlik doğrulaması başarısız');
        }
        
        $id = intval($_POST['id'] ?? 0);
        $deletion_reason = isset($_POST['deletion_reason']) ? wp_kses_post($_POST['deletion_reason']) : '';
        
        if (!$id) {
            wp_send_json_error('Geçersiz ilan ID');
        }
        
        if (empty($deletion_reason)) {
            wp_send_json_error('Silme nedeni gerekli');
        }
        
        $result = $this->delete_listing_admin($id, $deletion_reason);
        
        if ($result) {
            wp_send_json_success('İlan silindi ve kullanıcıya bildirim gönderildi');
        } else {
            wp_send_json_error('İlan silinirken hata oluştu');
        }
    }
    
    // Kritik işlemler için oturum ve nonce kontrolü
    $critical_actions = ['save_listing', 'update_listing', 'delete_listing', 'get_user_listings', 'upload_video', 'upload_video_temp', 'delete_video_temp'];
    $public_actions = ['get_listings', 'get_brands', 'get_locations'];
    $admin_actions = ['test_update_rates', 'test_send_mail'];
    
    if (in_array($action, $critical_actions)) {
        // Kritik işlemler için kullanıcıya özel nonce kontrolü
        if (!wp_verify_nonce($_POST['nonce'], 'ativ_nonce_' . get_current_user_id())) {
            wp_send_json_error('Güvenlik hatası');
        }
        if (!is_user_logged_in()) {
            wp_send_json_error('Bu işlem için giriş yapmalısınız');
        }
    } elseif (in_array($action, $public_actions)) {
        // Herkese açık işlemler için genel nonce kontrolü
        if (!wp_verify_nonce($_POST['nonce'], 'ativ_public_nonce')) {
            wp_send_json_error('Güvenlik hatası');
        }
    } elseif (in_array($action, $admin_actions)) {
        // Admin işlemleri - sadece yetki kontrolü
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Yetkiniz yok');
        }
    } else {
        // Diğer işlemler için varsayılan kontrol
        if (!wp_verify_nonce($_POST['nonce'], 'ativ_nonce')) {
            wp_send_json_error('Güvenlik hatası');
        }
    }
    
    switch($action) {
        case 'get_listings':
            $this->get_listings();
            break;
        case 'get_user_listings':
            $this->get_user_listings();
            break;
        case 'get_brands':
            $this->get_brands();
            break;
        case 'get_locations':
            $this->get_locations();
            break;
        case 'save_listing':
            $this->save_listing();
            break;
        case 'update_listing':
            $this->update_listing();
            break;
        case 'delete_listing':
            $this->delete_listing();
            break;
        case 'upload_video':
            $this->upload_video();
            break;
        case 'upload_video_temp':
            $this->upload_video_temp();
            break;
        case 'delete_video_temp':
            $this->delete_video_temp();
            break;
        case 'test_update_rates':
            $this->test_update_exchange_rates();
            break;
        case 'test_send_mail':
            $this->test_send_mail();
            break;
        default:
            wp_send_json_error('Geçersiz işlem');
    }
}
    
    private function get_listings() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'amator_ilanlar';
    
    // Yalnızca onaylı ilanları göster
    $listings = $wpdb->get_results("SELECT * FROM $table_name WHERE status = 'approved' ORDER BY created_at DESC", ARRAY_A);
    
    if ($wpdb->last_error) {
        wp_send_json_error('Veritabanı hatası: ' . $wpdb->last_error);
    }
    
    // Görselleri URL formatına çevir ve TL fiyatını hesapla
    foreach ($listings as &$listing) {
        $listing['images'] = $this->get_listing_images($listing['id'], $listing['images']);
        
        if (empty($listing['images'])) {
            $listing['emoji'] = '📻';
        }
        
        // Fiyatı TL'ye dönüştür (filtreleme için)
        $listing['price_in_tl'] = $this->convert_to_tl($listing['price'], $listing['currency']);
        
        // old_price varsa TL'ye çevir ve indirim yüzdesini hesapla
        if (!empty($listing['old_price']) && floatval($listing['old_price']) > floatval($listing['price'])) {
            $listing['old_price_in_tl'] = $this->convert_to_tl($listing['old_price'], $listing['currency']);
            $listing['discount_percent'] = round((($listing['old_price'] - $listing['price']) / $listing['old_price']) * 100);
        } else {
            $listing['old_price'] = null;
            $listing['discount_percent'] = 0;
        }
    }
    
    wp_send_json_success($listings);
    }

    private function get_user_listings() {
        if (!is_user_logged_in()) {
            wp_send_json_error('Bu işlem için giriş yapmalısınız');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'amator_ilanlar';
        $user_id = get_current_user_id();

        $listings = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at DESC", $user_id), ARRAY_A);

        if ($wpdb->last_error) {
            wp_send_json_error('Veritabanı hatası: ' . $wpdb->last_error);
        }

        // Görselleri URL formatına çevir ve TL fiyatını hesapla
        foreach ($listings as &$listing) {
            $listing['images'] = $this->get_listing_images($listing['id'], $listing['images']);
            
            if (empty($listing['images'])) {
                $listing['emoji'] = '📻';
            }
            
            // Fiyatı TL'ye dönüştür (filtreleme için)
            $listing['price_in_tl'] = $this->convert_to_tl($listing['price'], $listing['currency']);
            
            // old_price varsa TL'ye çevir ve indirim yüzdesini hesapla
            if (!empty($listing['old_price']) && floatval($listing['old_price']) > floatval($listing['price'])) {
                $listing['old_price_in_tl'] = $this->convert_to_tl($listing['old_price'], $listing['currency']);
                $listing['discount_percent'] = round((($listing['old_price'] - $listing['price']) / $listing['old_price']) * 100);
            } else {
                $listing['old_price'] = null;
                $listing['discount_percent'] = 0;
            }
        }

        wp_send_json_success($listings);
    }
    
    private function get_listing_images($listing_id, $images_json) {
        if (!$images_json) {
            return array();
        }
        
        $image_files = json_decode($images_json, true);
        if (!is_array($image_files)) {
            return array();
        }
        
        $images = array();
        foreach ($image_files as $image_file) {
            $image_path = ATIV_UPLOAD_DIR . $listing_id . '/' . $image_file;
            if (file_exists($image_path)) {
                $images[] = array(
                    'data' => ATIV_UPLOAD_URL . $listing_id . '/' . $image_file,
                    'name' => $image_file
                );
            }
        }
        
        return $images;
    }
    
    private function get_brands() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'amator_ilanlar';
        
        $brands = $wpdb->get_col("SELECT DISTINCT brand FROM $table_name ORDER BY brand");
        
        wp_send_json_success($brands);
    }
    
    private function get_locations() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'amator_ilanlar';
        
        $locations = $wpdb->get_col("SELECT DISTINCT location FROM $table_name ORDER BY location");
        
        wp_send_json_success($locations);
    }
    
    private function save_listing() {
    if (!is_user_logged_in()) {
        wp_send_json_error('İlan eklemek için giriş yapmalısınız');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'amator_ilanlar';
    
    $data = $_POST;
    $user_id = get_current_user_id();
    
    // Gerekli alanları kontrol et
    $required = ['title', 'category', 'brand', 'model', 'condition', 'price', 'description', 'callsign', 'seller_name', 'location', 'seller_email', 'seller_phone'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            wp_send_json_error("Eksik alan: $field");
        }
    }
    
    $emoji = '📻';
    $currency = sanitize_text_field($data['currency'] ?? 'TRY');
    
    // Video URL'sini hazırla (temp'ten taşınacak)
    $video_url = null;
    if (!empty($data['video_temp_path'])) {
        // Video temp'te, henüz taşıma
        $video_url = null; // Şimdilik null, listing_id aldıktan sonra taşıyacağız
    } elseif (!empty($data['video'])) {
        $video_url = esc_url_raw($data['video']);
    }
    
    $insert_data = array(
        'user_id' => $user_id,
        'title' => sanitize_text_field($data['title']),
        'category' => sanitize_text_field($data['category']),
        'brand' => sanitize_text_field($data['brand']),
        'model' => sanitize_text_field($data['model']),
        'condition' => sanitize_text_field($data['condition']),
        'price' => floatval($data['price']),
        'currency' => $currency,
        'description' => sanitize_textarea_field($data['description']),
        'images' => null,
        'featured_image_index' => 0,
        'video' => $video_url,
        'emoji' => $emoji,
        'callsign' => sanitize_text_field($data['callsign']),
        'seller_name' => sanitize_text_field($data['seller_name']),
        'location' => sanitize_text_field($data['location']),
        'seller_email' => sanitize_email($data['seller_email']),
        'seller_phone' => sanitize_text_field($data['seller_phone']),
        'status' => 'pending'
    );
    
    $result = $wpdb->insert($table_name, $insert_data);
    
    if ($result) {
        $listing_id = $wpdb->insert_id;
        
        // Görselleri işle
        $image_files = array();
        if (isset($data['images']) && !empty($data['images'])) {
            $image_files = $this->process_listing_images($listing_id, $data['images']);
        }
        
        // Video'yu temp'ten final klasöre taşı
        $final_video_url = null;
        if (!empty($data['video_temp_path'])) {
            $final_video_url = $this->move_video_from_temp($data['video_temp_path'], $listing_id);
        }
        
        // Görsel dosya isimlerini ve video URL'sini güncelle
        $update_data = array(
            'images' => !empty($image_files) ? json_encode($image_files) : null,
            'featured_image_index' => intval($data['featuredImageIndex'] ?? 0)
        );
        
        // Video başarıyla taşındıysa URL'yi ekle
        if ($final_video_url) {
            $update_data['video'] = $final_video_url;
        }
        
        $wpdb->update($table_name, $update_data, array('id' => $listing_id));
        
        // Kullanıcıya e-posta gönder - İlan gönderildi
        $this->send_notification('listing_submitted', array(
            'title' => stripslashes(htmlspecialchars_decode($insert_data['title'], ENT_QUOTES)),
            'seller_name' => $insert_data['seller_name'],
            'category' => $this->get_category_name($insert_data['category']),
            'listing_id' => $listing_id,
            'status' => 'Onay Bekleniyor'
        ), $insert_data['seller_email']);
        
        // Yöneticiye e-posta gönder - Yeni ilan bildirimi
        $admin_email = get_option('admin_email');
        if (!empty($admin_email)) {
            $this->send_notification('admin_new_listing', array(
                'title' => stripslashes(htmlspecialchars_decode($insert_data['title'], ENT_QUOTES)),
                'category' => $this->get_category_name($insert_data['category']),
                'seller_name' => $insert_data['seller_name'],
                'seller_email' => $insert_data['seller_email'],
                'price' => $insert_data['price'],
                'currency' => $currency,
                'listing_id' => $listing_id
            ), $admin_email);
        }
        
        wp_send_json_success(array('id' => $listing_id, 'message' => 'İlan başarıyla eklendi'));
    } else {
        wp_send_json_error('İlan eklenirken hata oluştu: ' . $wpdb->last_error);
    }
}
    
    private function process_listing_images($listing_id, $images_data, $start_index = 0) {
        $listing_dir = ATIV_UPLOAD_DIR . $listing_id;
        if (!file_exists($listing_dir)) {
            wp_mkdir_p($listing_dir);
        }
        
        $image_files = array();
        
        if (is_string($images_data)) {
            $images_array = json_decode(stripslashes($images_data), true);
        } else {
            $images_array = $images_data;
        }
        
        if (!is_array($images_array)) {
            return $image_files;
        }
        
        foreach ($images_array as $index => $image) {
            if (isset($image['data'])) {
                // Yeni görselleri mevcut sayının devamından numaralandır
                $file_name = $this->save_base64_image($listing_id, $image['data'], $start_index + $index + 1);
                if ($file_name) {
                    $image_files[] = $file_name;
                }
            }
        }
        
        return $image_files;
    }
    
    private function save_base64_image($listing_id, $base64_data, $image_number) {
        // İzin verilen dosya uzantıları - GÜVENLİK: Sadece görsel formatlarına izin ver
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        
        // Base64 formatını kontrol et ve düzenle
        if (preg_match('/^data:image\/(\w+);base64,/', $base64_data, $matches)) {
            $image_type = strtolower($matches[1]);
            $base64_data = substr($base64_data, strpos($base64_data, ',') + 1);
        } else {
            $image_type = 'jpg';
        }
        
        // GÜVENLİK: Dosya uzantısını doğrula - zararlı uzantıları engelle
        if (!in_array($image_type, $allowed_extensions)) {
            error_log('ATIV Security: Geçersiz dosya uzantısı engellendi: ' . $image_type);
            return false;
        }
        
        $base64_data = str_replace(' ', '+', $base64_data);
        $image_data = base64_decode($base64_data);
        
        if ($image_data === false) {
            return false;
        }
        
        // GÜVENLİK: Gerçek MIME tipi kontrolü - dosya içeriğini doğrula
        if (!class_exists('finfo')) {
            error_log('ATIV Security: finfo uzantısı mevcut değil, getimagesizefromstring kullanılıyor');
            // Alternatif yöntem: getimagesizefromstring kullanarak görsel olup olmadığını kontrol et
            $image_info = @getimagesizefromstring($image_data);
            if ($image_info === false) {
                error_log('ATIV Security: Geçersiz görsel verisi engellendi');
                return false;
            }
            $allowed_types = array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF, IMAGETYPE_WEBP);
            if (!in_array($image_info[2], $allowed_types)) {
                error_log('ATIV Security: Geçersiz görsel tipi engellendi: ' . $image_info[2]);
                return false;
            }
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $detected_mime = $finfo->buffer($image_data);
            $allowed_mimes = array(
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
                'image/webp'
            );
            
            if (!in_array($detected_mime, $allowed_mimes)) {
                error_log('ATIV Security: Geçersiz MIME tipi engellendi: ' . $detected_mime);
                return false;
            }
        }
        
        // GÜVENLİK: Dosya boyutu kontrolü (max 5MB)
        $max_size = 5 * 1024 * 1024;
        if (strlen($image_data) > $max_size) {
            error_log('ATIV Security: Dosya boyutu çok büyük: ' . strlen($image_data) . ' bytes');
            return false;
        }
        
        // Dosya adını oluştur: [ilan-id]P[numara].[uzanti]
        $file_name = intval($listing_id) . 'P' . sprintf('%02d', intval($image_number)) . '.' . $image_type;
        $file_path = ATIV_UPLOAD_DIR . intval($listing_id) . '/' . $file_name;
        
        if (file_put_contents($file_path, $image_data)) {
            return $file_name;
        }
        
        return false;
    }
    
    private function extract_image_index($filename) {
        // Dosya formatı: {id}P{numara}.{ext}
        if (preg_match('/P(\d{2})\./', $filename, $m)) {
            return intval($m[1]);
        }
        return 0;
    }
    
    private function get_max_image_index($image_files) {
        $max = 0;
        foreach ($image_files as $name) {
            $idx = $this->extract_image_index($name);
            if ($idx > $max) $max = $idx;
        }
        return $max;
    }
    
    private function is_base64_image_string($data) {
        return is_string($data) && preg_match('/^data:image\/(\w+);base64,/', $data);
    }
    
    private function update_listing() {
    // Ek güvenlik kontrolü
    if (!is_user_logged_in()) {
        wp_send_json_error('İlan düzenlemek için giriş yapmalısınız');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'amator_ilanlar';
    
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        wp_send_json_error('ID parametresi gerekli');
    }
    
    $user_id = get_current_user_id();
    
    // İlanın kullanıcıya ait olup olmadığını kontrol et
    $existing_listing = $wpdb->get_row($wpdb->prepare("SELECT user_id, images, featured_image_index, emoji, status FROM $table_name WHERE id = %d", $id), ARRAY_A);
    
    if (!$existing_listing) {
        wp_send_json_error('İlan bulunamadı');
    }
    
    if ($existing_listing['user_id'] != $user_id) {
        wp_send_json_error('Bu ilanı düzenleme yetkiniz yok');
    }
    
    $data = $_POST;
    
    // Mevcut görselleri al
    $current_images = $existing_listing['images'] ? json_decode($existing_listing['images'], true) : array();

    // Güncellenecek alanları kademeli olarak topla (sadece gönderilen alanlar)
    $update_data = array();
    $field_map_text = [
        'title' => 'title',
        'category' => 'category',
        'brand' => 'brand',
        'model' => 'model',
        'condition' => 'condition',
        'currency' => 'currency',
        'callsign' => 'callsign',
        'seller_name' => 'seller_name',
        'location' => 'location',
        'seller_phone' => 'seller_phone'
    ];
    foreach ($field_map_text as $post_key => $db_key) {
        if (array_key_exists($post_key, $data)) {
            $update_data[$db_key] = sanitize_text_field($data[$post_key]);
        }
    }
    if (array_key_exists('seller_email', $data)) {
        $update_data['seller_email'] = sanitize_email($data['seller_email']);
    }
    if (array_key_exists('price', $data)) {
        $new_price = floatval($data['price']);
        $old_listing_full = $wpdb->get_row($wpdb->prepare("SELECT price, old_price FROM $table_name WHERE id = %d", $id), ARRAY_A);
        
        if ($old_listing_full) {
            $current_price = floatval($old_listing_full['price']);
            $stored_old_price = $old_listing_full['old_price'] ? floatval($old_listing_full['old_price']) : null;
            
            if ($new_price < $current_price) {
                // Fiyat düştü: mevcut fiyatı old_price olarak sakla (eğer zaten old_price yoksa)
                if (!$stored_old_price) {
                    $update_data['old_price'] = $current_price;
                }
                // Eğer zaten old_price varsa ve yeni fiyat old_price'dan küçükse old_price'ı koru
                // Eğer yeni fiyat old_price'a eşitse veya büyükse old_price'ı temizle
                elseif ($new_price >= $stored_old_price) {
                    $update_data['old_price'] = null;
                }
            } elseif ($new_price >= $current_price) {
                // Fiyat arttı veya aynı kaldı: old_price'ı temizle (indirim sona erdi)
                $update_data['old_price'] = null;
            }
        }
        
        $update_data['price'] = $new_price;
    }
    if (array_key_exists('description', $data)) {
        $update_data['description'] = sanitize_textarea_field($data['description']);
    }
    
    // Video güncelleme
    if (array_key_exists('video', $data)) {
        $update_data['video'] = !empty($data['video']) ? esc_url_raw($data['video']) : null;
    }

    // Görseller: istemciden gelen listeyi nihai kaynak kabul et
    if (array_key_exists('images', $data)) {
        $final_images = array();
        $new_images_payload = array();

        // images alanı JSON ise decode et
        $images_input = null;
        if (is_string($data['images'])) {
            $images_input = json_decode(stripslashes($data['images']), true);
        } else {
            $images_input = $data['images'];
        }

        if (is_array($images_input)) {
            // 1) Önce mevcut korunacak (eski) dosya adlarını sırayla ekle
            $kept_existing = array();
            foreach ($images_input as $img) {
                $isBase64 = isset($img['data']) && $this->is_base64_image_string($img['data']);
                if (!$isBase64 && isset($img['name']) && in_array($img['name'], $current_images, true)) {
                    $kept_existing[] = $img['name'];
                } elseif ($isBase64) {
                    $new_images_payload[] = $img; // base64 olanları sonra yazacağız
                }
            }

            // 2) Yeni gelecek dosyalar için başlangıç numarası: mevcut (korunan) içindeki en yüksek numara
            $start_index = $this->get_max_image_index($kept_existing);
            $new_saved = array();
            if (!empty($new_images_payload)) {
                $new_saved = $this->process_listing_images($id, $new_images_payload, $start_index);
            }

            // 3) Son liste: önce korunacaklar (sırası istemciden), ardından yeni kaydedilenler
            $final_images = array_merge($kept_existing, $new_saved);

            // 4) Disk temizliği: artık listede olmayan mevcut dosyaları sil
            $to_delete = array_diff($current_images, $final_images);
            if (!empty($to_delete)) {
                $this->delete_listing_images($id, $to_delete);
            }

            // 5) DB güncellemesi: images alanını nihai liste ile yaz
            $update_data['images'] = !empty($final_images) ? json_encode($final_images) : null;
        } else {
            // images alanı null/boş gönderildiyse tüm görselleri kaldır
            if (!empty($current_images)) {
                $this->delete_listing_images($id, $current_images);
            }
            $update_data['images'] = null;
        }
    }

    // Kapak resmi indexi gönderildiyse güncelle
    if (array_key_exists('featuredImageIndex', $data)) {
        $fIndex = intval($data['featuredImageIndex']);
        // Eğer images da güncellenmişse sınır kontrolü yap
        if (isset($update_data['images'])) {
            $arr = $update_data['images'] ? json_decode($update_data['images'], true) : array();
            if (is_array($arr) && !empty($arr)) {
                if ($fIndex < 0 || $fIndex >= count($arr)) {
                    $fIndex = 0;
                }
            } else {
                $fIndex = 0;
            }
        }
        $update_data['featured_image_index'] = $fIndex;
    }

    // Emoji sadece açıkça gönderildiyse güncellensin; aksi halde dokunma
    if (array_key_exists('emoji', $data)) {
        $update_data['emoji'] = sanitize_text_field($data['emoji']);
    }
    
    // Red edilen ilanı düzenleniyorsa status'u pending'e ayarla ve rejection_reason'u temizle
    $was_rejected = false;
    $was_approved = false;
    if (!empty($existing_listing['status']) && $existing_listing['status'] === 'rejected') {
        $update_data['status'] = 'pending';
        $update_data['rejection_reason'] = null;
        $was_rejected = true;
    } elseif (!empty($existing_listing['status']) && $existing_listing['status'] === 'approved') {
        // Onaylı ilan düzenleniyorsa: sadece fiyat/para birimi değiştiyse otomatik onayla
        // Diğer alanlar değiştiyse pending'e dönüştür
        
        // Mevcut değerleri al
        $old_listing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
        
        // Kritik alanları kontrol et (fiyat ve para birimi HARİÇ)
        $critical_fields = ['title', 'description', 'category', 'brand', 'model', 'condition', 
                           'callsign', 'seller_name', 'location', 'seller_phone', 'seller_email'];
        
        $has_critical_change = false;
        foreach ($critical_fields as $field) {
            if (isset($update_data[$field]) && $update_data[$field] != $old_listing[$field]) {
                $has_critical_change = true;
                break;
            }
        }
        
        // Görseller değiştiyse de kritik sayılır
        if (isset($update_data['images']) && $update_data['images'] != $old_listing['images']) {
            $has_critical_change = true;
        }
        
        // Emoji değiştiyse kritik sayılır
        if (isset($update_data['emoji']) && $update_data['emoji'] != $old_listing['emoji']) {
            $has_critical_change = true;
        }
        
        if ($has_critical_change) {
            // Kritik alan değişmişse yeniden onay gerekli
            $update_data['status'] = 'pending';
            $was_approved = true;
        }
        // Sadece price/currency değiştiyse status değişmeden kalır (approved)
    }

    // Değişecek veri yoksa başarı döndür (no-op)
    if (empty($update_data)) {
        wp_send_json_success(array('message' => 'Değişiklik yok'));
    }

    $result = $wpdb->update($table_name, $update_data, array('id' => $id));
    
    if ($result !== false) {
        // Reddedilmiş ilan güncellenip tekrar gönderildiyse yöneticiye bildirim gönder
        if ($was_rejected || $was_approved) {
            $admin_email = get_option('admin_email');
            if (!empty($admin_email)) {
                // Güncel verileri al
                $updated_listing = $wpdb->get_row($wpdb->prepare("SELECT title, category, seller_name, seller_email, price, currency FROM $table_name WHERE id = %d", $id), ARRAY_A);
                if ($updated_listing) {
                    $this->send_notification('admin_listing_updated', array(
                        'title' => stripslashes(htmlspecialchars_decode($updated_listing['title'], ENT_QUOTES)),
                        'category' => $this->get_category_name($updated_listing['category']),
                        'seller_name' => $updated_listing['seller_name'],
                        'seller_email' => $updated_listing['seller_email'],
                        'price' => $updated_listing['price'],
                        'currency' => $updated_listing['currency'],
                        'listing_id' => $id
                    ), $admin_email);
                }
            }
        }
        wp_send_json_success(array('message' => 'İlan başarıyla güncellendi'));
    } else {
        wp_send_json_error('İlan güncellenirken hata oluştu: ' . $wpdb->last_error);
    }
}
    
    private function delete_listing() {
    // Ek güvenlik kontrolü
    if (!is_user_logged_in()) {
        wp_send_json_error('İlan silmek için giriş yapmalısınız');
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'amator_ilanlar';
    
    $id = intval($_POST['id'] ?? 0);
    if (!$id) {
        wp_send_json_error('ID parametresi gerekli');
    }
    
    $user_id = get_current_user_id();
    
    // İlanın kullanıcıya ait olup olmadığını kontrol et
    $existing_listing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
    
    if (!$existing_listing) {
        wp_send_json_error('İlan bulunamadı');
    }
    
    if ($existing_listing['user_id'] != $user_id) {
        wp_send_json_error('Bu ilanı silme yetkiniz yok');
    }
    
    // İlanın görsellerini sil
    if ($existing_listing['images']) {
        $image_files = json_decode($existing_listing['images'], true);
        if (is_array($image_files)) {
            $this->delete_listing_images($id, $image_files);
        }
    }
    
    // İlanın videosunu sil (ilan klasöründen)
    if (!empty($existing_listing['video'])) {
        $video_filename = basename($existing_listing['video']);
        $video_path = ATIV_UPLOAD_DIR . $id . '/' . $video_filename;
        if (file_exists($video_path)) {
            @unlink($video_path);
        }
    }
    
    // İlan klasörünü tamamen sil (boş olsa bile)
    $listing_dir = ATIV_UPLOAD_DIR . $id;
    if (is_dir($listing_dir)) {
        // Klasörde kalan dosya var mı kontrol et
        $remaining_files = glob($listing_dir . '/*');
        
        // Kalan dosyaları temizle
        if (!empty($remaining_files)) {
            foreach ($remaining_files as $file) {
                if (is_file($file)) {
                    @unlink($file);
                }
            }
        }
        
        // Boş klasörü sil
        @rmdir($listing_dir);
    }
    
    $result = $wpdb->delete($table_name, array('id' => $id));
    
    if ($result) {
        // Kullanıcıya silme bildirimi gönder
        $this->send_notification('listing_deleted', array(
            'title' => stripslashes(htmlspecialchars_decode($existing_listing['title'], ENT_QUOTES)),
            'seller_name' => $existing_listing['seller_name'],
            'category' => $this->get_category_name($existing_listing['category']),
            'admin_email' => get_option('admin_email'),
            'listing_id' => $id
        ), $existing_listing['seller_email']);
        
        wp_send_json_success(array('message' => 'İlan başarıyla silindi'));
    } else {
        wp_send_json_error('İlan silinirken hata oluştu: ' . $wpdb->last_error);
    }
}

    /**
     * Video yükleme ve işleme
     */
    private function upload_video() {
        // Video yükleme için zaman sınırını artır (300 saniye = 5 dakika)
        @set_time_limit(300);
        @ini_set('memory_limit', '256M');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('Video yüklemek için giriş yapmalısınız');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'amator_ilanlar';

        // Video dosyasını kontrol et
        if (empty($_FILES['video'])) {
            wp_send_json_error('Bir video dosyası seçiniz');
        }

        $file = $_FILES['video'];
        $user_id = get_current_user_id();

        // listing_id kontrol et (zorunlu)
        $listing_id = isset($_POST['listing_id']) ? intval($_POST['listing_id']) : 0;
        if ($listing_id <= 0) {
            wp_send_json_error('İlan ID bulunamadı');
        }

        // Upload hatalarını kontrol et
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = array(
                UPLOAD_ERR_INI_SIZE => 'Dosya boyutu sunucu limitini aşıyor',
                UPLOAD_ERR_FORM_SIZE => 'Dosya boyutu form limitini aşıyor',
                UPLOAD_ERR_PARTIAL => 'Dosya kısmen yüklendi',
                UPLOAD_ERR_NO_FILE => 'Dosya yüklenmedi',
                UPLOAD_ERR_NO_TMP_DIR => 'Geçici klasör bulunamadı',
                UPLOAD_ERR_CANT_WRITE => 'Dosya yazılamadı',
                UPLOAD_ERR_EXTENSION => 'Bir PHP uzantısı yüklemeyi durdurdu'
            );
            $error_msg = isset($error_messages[$file['error']]) ? $error_messages[$file['error']] : 'Bilinmeyen yükleme hatası';
            wp_send_json_error($error_msg);
        }

        // Boyut kontrolü (150MB)
        $max_size = 150 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            wp_send_json_error('Video dosyası 150MB\'dan küçük olmalıdır');
        }

        if ($file['size'] == 0) {
            wp_send_json_error('Dosya boş');
        }

        // Dosya uzantısını güvenli şekilde al ve kontrol et
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('mp4', 'webm');
        
        if (!in_array($file_ext, $allowed_extensions)) {
            wp_send_json_error('Sadece MP4 ve WebM uzantıları desteklenir');
        }

        // MIME type kontrolü (client-side, ek güvenlik için)
        $allowed_mimes = array('video/mp4', 'video/webm');
        if (!in_array($file['type'], $allowed_mimes)) {
            wp_send_json_error('Geçersiz dosya tipi');
        }

        // GERÇEKTİR DOSYA İÇERİĞİ KONTROLÜ - PHP fileinfo ile
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_mimes)) {
            wp_send_json_error('Dosya içeriği video değil');
        }

        // İlan klasörünü oluştur (görseller gibi)
        $listing_dir = ATIV_UPLOAD_DIR . $listing_id;
        if (!file_exists($listing_dir)) {
            if (!wp_mkdir_p($listing_dir)) {
                wp_send_json_error('İlan klasörü oluşturulamadı');
            }
        }

        // Dosya adını oluştur: {listing_id}V01.{ext} formatında
        $file_name = intval($listing_id) . 'V01.' . $file_ext;
        $file_path = $listing_dir . '/' . $file_name;

        // Güvenlik: Path traversal koruması
        $real_upload_dir = realpath(ATIV_UPLOAD_DIR);
        $real_file_path = realpath($listing_dir) . '/' . basename($file_name);
        
        if (strpos($real_file_path, $real_upload_dir) !== 0) {
            wp_send_json_error('Güvenlik ihlali tespit edildi');
        }

        // Eski videoyu sil (varsa)
        $old_video = $wpdb->get_var($wpdb->prepare(
            "SELECT video FROM $table_name WHERE id = %d AND user_id = %d",
            $listing_id,
            $user_id
        ));
        
        if ($old_video) {
            // Eski video dosyasının tam yolunu bul
            $old_file_name = basename($old_video);
            $old_file_path = $listing_dir . '/' . $old_file_name;
            if (file_exists($old_file_path)) {
                @unlink($old_file_path);
            }
        }

        // Dosyayı güvenli şekilde taşı
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            wp_send_json_error('Video dosyası yüklenirken hata oluştu');
        }

        // Dosya izinlerini ayarla (güvenlik)
        chmod($file_path, 0644);

        // URL oluştur
        $upload_dir = wp_upload_dir();
        $plugin_url = plugins_url('uploads/', __FILE__);
        $file_url = $plugin_url . $listing_id . '/' . $file_name;
        
        // Yeni videoyu kaydet
        $updated = $wpdb->update(
            $table_name,
            array('video' => $file_url),
            array('id' => $listing_id, 'user_id' => $user_id),
            array('%s'),
            array('%d', '%d')
        );
        
        if ($updated === false) {
            // Database hatası, yüklenen dosyayı sil
            @unlink($file_path);
            wp_send_json_error('Video kaydedilemedi');
        }
        
        wp_send_json_success(array(
            'message' => 'Video başarıyla yüklendi',
            'url' => $file_url
        ));
    }

    /**
     * Video'yu TEMP klasörüne yükle (form doldurulurken)
     */
    private function upload_video_temp() {
        // Video yükleme için zaman sınırını artır
        @set_time_limit(300);
        @ini_set('memory_limit', '256M');
        
        if (!is_user_logged_in()) {
            wp_send_json_error('Video yüklemek için giriş yapmalısınız');
        }

        // Video dosyasını kontrol et
        if (empty($_FILES['video'])) {
            wp_send_json_error('Bir video dosyası seçiniz');
        }

        $file = $_FILES['video'];
        $user_id = get_current_user_id();

        // Upload hatalarını kontrol et
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_messages = array(
                UPLOAD_ERR_INI_SIZE => 'Dosya boyutu sunucu limitini aşıyor',
                UPLOAD_ERR_FORM_SIZE => 'Dosya boyutu form limitini aşıyor',
                UPLOAD_ERR_PARTIAL => 'Dosya kısmen yüklendi',
                UPLOAD_ERR_NO_FILE => 'Dosya yüklenmedi',
                UPLOAD_ERR_NO_TMP_DIR => 'Geçici klasör bulunamadı',
                UPLOAD_ERR_CANT_WRITE => 'Dosya yazılamadı',
                UPLOAD_ERR_EXTENSION => 'Bir PHP uzantısı yüklemeyi durdurdu'
            );
            $error_msg = isset($error_messages[$file['error']]) ? $error_messages[$file['error']] : 'Bilinmeyen yükleme hatası';
            wp_send_json_error($error_msg);
        }

        // Boyut kontrolü (150MB)
        $max_size = 150 * 1024 * 1024;
        if ($file['size'] > $max_size) {
            wp_send_json_error('Video dosyası 150MB\'dan küçük olmalıdır');
        }

        if ($file['size'] == 0) {
            wp_send_json_error('Dosya boş');
        }

        // Dosya uzantısını güvenli şekilde al ve kontrol et
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('mp4', 'webm');
        
        if (!in_array($file_ext, $allowed_extensions)) {
            wp_send_json_error('Sadece MP4 ve WebM uzantıları desteklenir');
        }

        // MIME type kontrolü
        $allowed_mimes = array('video/mp4', 'video/webm');
        if (!in_array($file['type'], $allowed_mimes)) {
            wp_send_json_error('Geçersiz dosya tipi');
        }

        // GERÇEK DOSYA İÇERİĞİ KONTROLÜ - PHP fileinfo ile
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_mimes)) {
            wp_send_json_error('Dosya içeriği video değil');
        }

        // TEMP klasörü oluştur
        $temp_dir = ATIV_UPLOAD_DIR . 'temp/';
        if (!file_exists($temp_dir)) {
            if (!wp_mkdir_p($temp_dir)) {
                wp_send_json_error('Temp klasörü oluşturulamadı');
            }
        }

        // Benzersiz dosya adı oluştur: temp_{user_id}_{timestamp}_{random}.{ext}
        $file_name = 'temp_' . $user_id . '_' . time() . '_' . wp_generate_password(8, false) . '.' . $file_ext;
        $file_path = $temp_dir . $file_name;

        // Güvenlik: Path traversal koruması
        $real_temp_dir = realpath($temp_dir);
        $real_file_path = realpath(dirname($file_path)) . '/' . basename($file_path);
        
        if (strpos($real_file_path, $real_temp_dir) !== 0) {
            wp_send_json_error('Güvenlik ihlali tespit edildi');
        }

        // Dosyayı güvenli şekilde taşı
        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            wp_send_json_error('Video dosyası yüklenirken hata oluştu');
        }

        // Dosya izinlerini ayarla
        chmod($file_path, 0644);

        // URL oluştur
        $plugin_url = plugins_url('uploads/', __FILE__);
        $file_url = $plugin_url . 'temp/' . $file_name;
        
        wp_send_json_success(array(
            'message' => 'Video temp klasöre yüklendi',
            'temp_url' => $file_url,
            'temp_filename' => $file_name
        ));
    }

    /**
     * Temp klasördeki videoyu final klasöre taşı
     */
    private function move_video_from_temp($temp_url, $listing_id) {
        if (empty($temp_url)) {
            return null;
        }

        // Temp filename'i URL'den çıkar
        $temp_filename = basename($temp_url);
        $temp_path = ATIV_UPLOAD_DIR . 'temp/' . $temp_filename;

        // Dosya var mı kontrol et
        if (!file_exists($temp_path)) {
            error_log('ATIV: Temp video bulunamadı: ' . $temp_path);
            return null;
        }

        // İlan klasörünü oluştur
        $listing_dir = ATIV_UPLOAD_DIR . $listing_id;
        if (!file_exists($listing_dir)) {
            wp_mkdir_p($listing_dir);
        }

        // Dosya uzantısını al
        $file_ext = strtolower(pathinfo($temp_filename, PATHINFO_EXTENSION));
        
        // Final dosya adı: {listing_id}V01.{ext}
        $final_filename = intval($listing_id) . 'V01.' . $file_ext;
        $final_path = $listing_dir . '/' . $final_filename;

        // Dosyayı taşı
        if (rename($temp_path, $final_path)) {
            // URL oluştur
            $plugin_url = plugins_url('uploads/', __FILE__);
            $final_url = $plugin_url . $listing_id . '/' . $final_filename;
            
            return $final_url;
        }

        error_log('ATIV: Video taşınamadı: ' . $temp_path . ' -> ' . $final_path);
        return null;
    }

    /**
     * Temp klasördeki videoyu sil
     */
    private function delete_video_temp() {
        if (!is_user_logged_in()) {
            wp_send_json_error('Yetkiniz yok');
        }

        $temp_url = isset($_POST['temp_url']) ? sanitize_text_field($_POST['temp_url']) : '';
        
        if (empty($temp_url)) {
            wp_send_json_error('Temp URL bulunamadı');
        }

        // Temp filename'i URL'den çıkar
        $temp_filename = basename($temp_url);
        $temp_path = ATIV_UPLOAD_DIR . 'temp/' . $temp_filename;

        // Güvenlik: Dosyanın gerçekten temp klasöründe olduğunu kontrol et
        $real_temp_dir = realpath(ATIV_UPLOAD_DIR . 'temp/');
        $real_file_path = realpath($temp_path);

        if ($real_file_path && strpos($real_file_path, $real_temp_dir) === 0) {
            if (file_exists($temp_path)) {
                @unlink($temp_path);
                wp_send_json_success('Temp video silindi');
            } else {
                wp_send_json_success('Dosya zaten mevcut değil');
            }
        } else {
            wp_send_json_error('Güvenlik ihlali');
        }
    }

    /**
     * Eski temp dosyalarını temizle (1 saatten eski olanlar)
     * Cron job ile çalıştırılır
     */
    public function cleanup_old_temp_videos() {
        $temp_dir = ATIV_UPLOAD_DIR . 'temp/';
        
        if (!is_dir($temp_dir)) {
            return;
        }

        $files = glob($temp_dir . 'temp_*');
        $one_hour_ago = time() - 3600; // 1 saat = 3600 saniye
        $deleted_count = 0;

        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < $one_hour_ago) {
                if (@unlink($file)) {
                    $deleted_count++;
                    error_log('ATIV: Eski temp video temizlendi: ' . basename($file));
                }
            }
        }
        
        if ($deleted_count > 0) {
            error_log('ATIV: Toplam ' . $deleted_count . ' eski temp video temizlendi');
        }
    }
    
    private function delete_listing_images($listing_id, $image_files) {
        // GÜVENLİK: listing_id'yi integer olarak doğrula
        $listing_id = intval($listing_id);
        if ($listing_id <= 0) {
            return false;
        }
        
        $listing_dir = ATIV_UPLOAD_DIR . $listing_id;
        
        // GÜVENLİK: Klasörün gerçekten upload dizini içinde olduğunu doğrula
        $real_listing_dir = realpath($listing_dir);
        $real_upload_dir = realpath(ATIV_UPLOAD_DIR);
        
        if ($real_listing_dir === false) {
            // Klasör henüz mevcut olmayabilir - bu normal bir durum
            error_log('ATIV: Listing klasörü bulunamadı (normal): ' . $listing_dir);
            return false;
        }
        
        if ($real_upload_dir === false) {
            error_log('ATIV Error: Upload klasörü bulunamadı: ' . ATIV_UPLOAD_DIR);
            return false;
        }
        
        if (strpos($real_listing_dir, $real_upload_dir) !== 0) {
            error_log('ATIV Security: Path traversal engellendi: ' . $listing_dir);
            return false;
        }
        
        foreach ($image_files as $image_file) {
            // GÜVENLİK: Path traversal engelle - sadece basit dosya adlarına izin ver
            $clean_filename = basename($image_file);
            if ($clean_filename !== $image_file || strpos($image_file, '..') !== false) {
                error_log('ATIV Security: Geçersiz dosya adı engellendi: ' . $image_file);
                continue;
            }
            
            $file_path = $listing_dir . '/' . $clean_filename;
            
            // GÜVENLİK: Dosyanın gerçekten listing klasörü içinde olduğunu doğrula
            $real_file_path = realpath($file_path);
            if ($real_file_path !== false && strpos($real_file_path, $real_listing_dir) === 0) {
                if (file_exists($real_file_path)) {
                    unlink($real_file_path);
                }
            }
        }
        
        // Klasörü de sil (eğer boşsa)
        if (is_dir($listing_dir) && count(scandir($listing_dir)) == 2) {
            rmdir($listing_dir);
        }
    }

    public static function get_category_name($category) {
        $categories = array(
            'transceiver' => '📻 Telsiz',
            'antenna' => '📡 Anten',
            'amplifier' => '⚡ Amplifikatör',
            'accessory' => '🔧 Aksesuar',
            'other' => '❓ Diğer'
        );
        return isset($categories[$category]) ? $categories[$category] : $category;
    }
    
    /**
     * Admin menüsüne eklenti sayfasını ekler
     */
    public function add_admin_menu() {
        add_menu_page(
            'Amatör Bitlik - İlan Yönetimi',           // Sayfa başlığı
            'Amatör Bitlik',                            // Menü başlığı
            'manage_options',                           // Yetki
            'ativ-listings',                            // Menu slug
            array($this, 'admin_listings_page'),       // Callback
            'dashicons-building',                       // Icon
            25                                          // Position
        );
        
        add_submenu_page(
            'ativ-listings',                            // Parent menu slug
            'Tüm İlanlar',                             // Sayfa başlığı
            'Tüm İlanlar',                             // Menü başlığı
            'manage_options',                          // Yetki
            'ativ-listings',                           // Menu slug
            array($this, 'admin_listings_page')        // Callback
        );
        
        add_submenu_page(
            'ativ-listings',                            // Parent menu slug
            'Kullanıcılar',                            // Sayfa başlığı
            'Kullanıcılar',                            // Menü başlığı
            'manage_options',                          // Yetki
            'ativ-users',                              // Menu slug
            array($this, 'admin_users_page')          // Callback
        );
        
        add_submenu_page(
            'ativ-listings',                            // Parent menu slug
            'Ayarlar',                                  // Sayfa başlığı
            'Ayarlar',                                  // Menü başlığı
            'manage_options',                          // Yetki
            'ativ-settings',                            // Menu slug
            array($this, 'admin_settings_page')        // Callback
        );

        // Lokalizasyon alt menüsü kaldırıldı; Ayarlar sekmeleri içinde gösteriliyor
    }
    
    /**
     * Admin İlan Yönetim Sayfası
     */
    public function admin_listings_page() {
        include ATIV_PLUGIN_PATH . 'templates/admin-all-listings.php';
    }
    
    /**
     * Admin tarafından ilanı sil
     */
    private function delete_listing_admin($id, $deletion_reason = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'amator_ilanlar';
        
        // Yetki kontrolü
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        // İlan bilgilerini al
        $listing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
        
        // Görselleri sil
        if ($listing && $listing['images']) {
            $images = json_decode($listing['images'], true);
            if (is_array($images)) {
                foreach ($images as $image_filename) {
                    $file_path = ATIV_UPLOAD_DIR . $id . '/' . $image_filename;
                    if (file_exists($file_path)) {
                        @unlink($file_path);
                    }
                }
            }
        }
        
        // Videoyu sil
        if ($listing && !empty($listing['video'])) {
            $video_filename = basename($listing['video']);
            $video_path = ATIV_UPLOAD_DIR . $id . '/' . $video_filename;
            if (file_exists($video_path)) {
                @unlink($video_path);
            }
        }
        
        // İlan klasörünü tamamen sil
        $listing_dir = ATIV_UPLOAD_DIR . $id;
        if (is_dir($listing_dir)) {
            // Klasörde kalan dosyaları temizle
            $remaining_files = glob($listing_dir . '/*');
            if (!empty($remaining_files)) {
                foreach ($remaining_files as $file) {
                    if (is_file($file)) {
                        @unlink($file);
                    }
                }
            }
            // Boş klasörü sil
            @rmdir($listing_dir);
        }
        
        // İlanı sil
        $wpdb->delete($table_name, array('id' => $id), array('%d'));
        
        // Admin tarafından silinme bildirim e-postası gönder
        if ($listing) {
            $this->send_notification('listing_deleted_by_admin', array(
                'title' => stripslashes(htmlspecialchars_decode($listing['title'], ENT_QUOTES)),
                'seller_name' => $listing['seller_name'],
                'category' => $this->get_category_name($listing['category']),
                'deletion_reason' => !empty($deletion_reason) ? $deletion_reason : 'Neden belirtilmemiş',
                'admin_email' => get_option('admin_email'),
                'listing_id' => $id
            ), $listing['seller_email']);
        }
        
        return true;
    }
    
    /**
     * ID ile ilanı getir
     */
    private function get_listing_by_id($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'amator_ilanlar';
        $listing = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id), ARRAY_A);
        
        if ($listing) {
            $listing['images'] = $this->get_listing_images($listing['id'], $listing['images']);
        }
        
        return $listing;
    }
    
    /**
     * Admin için düzenleme formu oluştur
     */
    private function generate_admin_edit_form($listing) {
        ob_start();
        ?>
        <form id="adminEditForm" onsubmit="submitAdminEditForm(event)">
            <input type="hidden" name="id" value="<?php echo $listing['id']; ?>">
            
            <div>
                <label>📌 Başlık</label>
                <input type="text" name="title" value="<?php echo esc_attr($listing['title']); ?>" placeholder="İlan başlığı..." required>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label>📂 Kategori</label>
                    <select name="category" required>
                        <option value="">Seçiniz...</option>
                        <option value="transceiver" <?php selected($listing['category'], 'transceiver'); ?>>📻 Telsiz</option>
                        <option value="antenna" <?php selected($listing['category'], 'antenna'); ?>>📡 Anten</option>
                        <option value="amplifier" <?php selected($listing['category'], 'amplifier'); ?>>⚡ Amplifikatör</option>
                        <option value="accessory" <?php selected($listing['category'], 'accessory'); ?>>🔧 Aksesuar</option>
                        <option value="other" <?php selected($listing['category'], 'other'); ?>>❓ Diğer</option>
                    </select>
                </div>
                <div>
                    <label>💰 Fiyat</label>
                    <input type="number" name="price" value="<?php echo $listing['price']; ?>" placeholder="0.00" step="0.01" required>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label>🏢 Marka</label>
                    <input type="text" name="brand" value="<?php echo esc_attr($listing['brand']); ?>" placeholder="Ürün markası..." required>
                </div>
                <div>
                    <label>🎯 Model</label>
                    <input type="text" name="model" value="<?php echo esc_attr($listing['model']); ?>" placeholder="Model numarası..." required>
                </div>
            </div>
            
            <div>
                <label>✨ Durum</label>
                <select name="condition" required>
                    <option value="Sıfır" <?php selected($listing['condition'], 'Sıfır'); ?>>🆕 Sıfır - Hiç Kullanılmamış</option>
                    <option value="Kullanılmış" <?php selected($listing['condition'], 'Kullanılmış'); ?>>✓ Kullanılmış - İyi Durumda</option>
                    <option value="Arızalı" <?php selected($listing['condition'], 'Arızalı'); ?>>⚠️ Arızalı - Tamir Gerekli</option>
                    <option value="El Yapımı" <?php selected($listing['condition'], 'El Yapımı'); ?>>🛠️ El Yapımı - Özel Yapım</option>
                </select>
            </div>
            
            <div>
                <label>📝 Açıklama</label>
                <textarea name="description" placeholder="İlan detaylarını yazınız..." rows="6" required><?php echo esc_textarea($listing['description']); ?></textarea>
            </div>
            
            <div>
                <label>🖼️ Yüklü Görseller (<span id="imageCount"><?php echo count($listing['images']); ?></span>/<span id="imageMax">10</span>)</label>
                <div id="adminImageGallery">
                    <?php foreach ($listing['images'] as $index => $image) : ?>
                        <div class="admin-image-item" title="Silmek için tıkla">
                            <img src="<?php echo esc_url($image['data']); ?>" loading="lazy">
                            <button type="button" class="admin-image-delete-btn" onclick="removeImageFromForm(this)" title="Bu görseli sil">×</button>
                            <input type="hidden" class="image-data" value="<?php echo esc_attr($image['data']); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Satıcı Bilgileri - Salt Okunur -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 30px; border-left: 4px solid #667eea;">
                <h3 style="margin-top: 0; color: #333;">👤 Satıcı Bilgileri (Salt Okunur)</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #666;">Satıcı Adı</label>
                        <input type="text" value="<?php echo esc_attr($listing['seller_name']); ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #fff; color: #333;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #666;">Çağrı İşareti</label>
                        <input type="text" value="<?php echo esc_attr($listing['callsign']); ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #fff; color: #333;">
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 15px;">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #666;">E-posta</label>
                        <input type="email" value="<?php echo esc_attr($listing['seller_email']); ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #fff; color: #333;">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #666;">Telefon</label>
                        <input type="text" value="<?php echo esc_attr($listing['seller_phone']); ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #fff; color: #333;">
                    </div>
                </div>
                
                <div style="margin-top: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #666;">Konum</label>
                    <input type="text" value="<?php echo esc_attr($listing['location']); ?>" readonly style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; background: #fff; color: #333;">
                </div>
            </div>
            
            <div class="ativ-form-buttons">
                <button type="submit" title="Değişiklikleri kaydet">✅ Güncelle</button>
                <button type="button" onclick="closeAdminEditModal()" title="Formu kapat">✕ İptal</button>
            </div>
        </form>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Admin tarafından ilanı güncelle
     */
    private function update_listing_admin($id, $data) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'amator_ilanlar';
        
        // Mevcut ilanı al
        $existing_listing = $wpdb->get_row($wpdb->prepare("SELECT images FROM $table_name WHERE id = %d", $id), ARRAY_A);
        $current_images = $existing_listing['images'] ? json_decode($existing_listing['images'], true) : array();
        
        $update_data = array(
            'title' => sanitize_text_field($data['title'] ?? ''),
            'category' => sanitize_text_field($data['category'] ?? ''),
            'brand' => sanitize_text_field($data['brand'] ?? ''),
            'model' => sanitize_text_field($data['model'] ?? ''),
            'condition' => sanitize_text_field($data['condition'] ?? ''),
            'price' => floatval($data['price'] ?? 0),
            'description' => wp_kses_post($data['description'] ?? ''),
        );
        
        // Görselleri işle
        if (isset($data['images'])) {
            $images_input = null;
            if (is_string($data['images'])) {
                $images_input = json_decode(stripslashes($data['images']), true);
            } else {
                $images_input = $data['images'];
            }
            
            if (is_array($images_input)) {
                // Kalan görselleri topla (silinmeyenler)
                $kept_existing = array();
                foreach ($images_input as $img) {
                    if (isset($img['name']) && in_array($img['name'], $current_images, true)) {
                        $kept_existing[] = $img['name'];
                    }
                }
                
                // Silinen görselleri diskte sil
                $to_delete = array_diff($current_images, $kept_existing);
                if (!empty($to_delete)) {
                    $this->delete_listing_images($id, $to_delete);
                }
                
                // Veritabanını güncelle
                $update_data['images'] = !empty($kept_existing) ? json_encode($kept_existing) : null;
            }
        }
        
        $wpdb->update($table_name, $update_data, array('id' => $id));
        
        return true;
    }
    
    /**
     * Admin Kullanıcılar Sayfası
     */
    public function admin_users_page() {
        include ATIV_PLUGIN_PATH . 'templates/admin-users.php';
    }
    
    /**
     * Admin Ayarlar Sayfası
     */
    public function admin_settings_page() {
        include ATIV_PLUGIN_PATH . 'templates/admin-settings.php';
    }


    /**
     * Varsayılan mail şablonlarını döndür
     */
    private function get_default_template($type) {
        $templates = array(
            'admin_new_listing' => <<<'EOT'
Merhaba Yönetici,

Yeni bir ilan gönderilmiştir ve onayınızı beklemektedir.

İlan Bilgileri:
- Başlık: {title}
- Kategori: {category}
- Satıcı Adı: {seller_name}
- Satıcı E-postası: {seller_email}
- Fiyat: {price} {currency}
- Durum: Onay Bekleniyor

İlanı yönetim panelinden inceleyebilir ve onaylayabilir veya reddedebilirsiniz.

Saygılarımızla,
Amatör Bitlik Sistemi
EOT,
            'admin_listing_updated' => <<<'EOT'
Merhaba Yönetici,

Daha önce reddedilmiş olan bir ilan güncellenmiş ve tekrar onayınız için gönderilmiştir.

Güncellenen İlan Bilgileri:
- Başlık: {title}
- Kategori: {category}
- Satıcı Adı: {seller_name}
- Satıcı E-postası: {seller_email}
- Fiyat: {price} {currency}
- Durum: Onay Bekleniyor

İlanı yönetim panelinden inceleyebilir ve onaylayabilir veya reddedebilirsiniz.

Saygılarımızla,
Amatör Bitlik Sistemi
EOT,
            'submitted' => <<<'EOT'
Merhaba {seller_name},

İlan başarıyla gönderilmiştir. Yönetici tarafından incelenmesi bekleniyor.

İlan Bilgileri:
- Başlık: {title}
- Kategori: {category}

Lütfen sabırlı olun. Yönetici incelemesinden sonra size bilgilendirileceksiniz.

Saygılarımızla,
Amatör Bitlik Ekibi
EOT,
            'approved' => <<<'EOT'
Merhaba {seller_name},

Harika haber! İlanınız onaylanmıştır ve platform üzerinde yayında.

İlan Bilgileri:
- Başlık: {title}
- Kategori: {category}

Hesabınız üzerindeki "Benim İlanlarım" sayfasından ilanınızı görebilirsiniz.

Saygılarımızla,
Amatör Bitlik Ekibi
EOT,
            'rejected' => <<<'EOT'
Merhaba {seller_name},

Maalesef, ilanınız reddedilmiştir.

İlan Bilgileri:
- Başlık: {title}
- Kategori: {category}

Red Nedeni:
{rejection_reason}

İlanı düzenleyerek tekrar gönderebilirsiniz. Lütfen belirtilen neden göz önünde bulundurunuz.

Sorularınız için lütfen {admin_email} adresine yazın.

Saygılarımızla,
Amatör Bitlik Ekibi
EOT,
            'deleted' => <<<'EOT'
Merhaba {seller_name},

İlanınız "{title}" başarıyla silinmiştir.

Eğer bu işlemi siz yapmadıysanız lütfen {admin_email} adresine yazın.

Yeni ilanlar eklemek için platformumuzu ziyaret edebilirsiniz.

Saygılarımızla,
Amatör Bitlik Ekibi
EOT,
            'deleted_by_admin' => <<<'EOT'
Merhaba {seller_name},

Maalesef, "{title}" adlı ilanınız yönetici tarafından silinmiştir.

Silme Nedeni:
{deletion_reason}

Sorularınız için lütfen {admin_email} adresine yazın.

Saygılarımızla,
Amatör Bitlik Ekibi
EOT,
            'user_terms' => <<<'EOT'
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
EOT
        );
        
        return $templates[$type] ?? '';
    }
    
    /**
     * Veritabanından şablon body'sini getir
     */
    private function get_template_body($template_key, $fallback_type) {
        global $wpdb;
        $templates_table = $wpdb->prefix . 'amator_telsiz_sablonlar';
        
        // Cache'i atla - WPDB_NO_CACHE kullan
        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT template_body FROM $templates_table WHERE template_key = %s", $template_key),
            OBJECT,
            0
        );
        
        if ($template && !empty($template->template_body)) {
            return $template->template_body;
        }
        
        // Fallback: varsayılan şablonu döndür
        return $this->get_default_template($fallback_type);
    }
    
    /**
     * Varsayılan şablonları veritabanına ekle
     */
    private function insert_default_templates() {
        global $wpdb;
        $templates_table = $wpdb->prefix . 'amator_telsiz_sablonlar';
        
        $default_templates = array(
            array(
                'template_key' => 'admin_new_listing',
                'template_name' => 'Yöneticiye Yeni İlan Bildirimi',
                'template_subject' => '🆕 Yeni İlan: {title} - Onay Bekleniyor',
                'template_body' => $this->get_default_template('admin_new_listing'),
                'template_description' => 'Yeni ilan eklendiğinde yöneticilere gönderilen e-posta'
            ),
            array(
                'template_key' => 'admin_listing_updated',
                'template_name' => 'Yöneticiye İlan Güncelleme Bildirimi',
                'template_subject' => '🔄 İlan Güncellendi: {title} - Tekrar Onay Bekleniyor',
                'template_body' => $this->get_default_template('admin_listing_updated'),
                'template_description' => 'Reddedilmiş ilan güncellendiğinde yöneticilere gönderilen e-posta'
            ),
            array(
                'template_key' => 'listing_submitted',
                'template_name' => 'İlan Gönderimi Bildirimi',
                'template_subject' => 'İlanınız başarıyla gönderilmiştir',
                'template_body' => $this->get_default_template('submitted'),
                'template_description' => 'Kullanıcı yeni ilan gönderdiğinde gönderilen e-posta'
            ),
            array(
                'template_key' => 'listing_approved',
                'template_name' => 'İlan Onayı Bildirimi',
                'template_subject' => 'İlanınız onaylanmıştır',
                'template_body' => $this->get_default_template('approved'),
                'template_description' => 'İlan yönetici tarafından onaylandığında gönderilen e-posta'
            ),
            array(
                'template_key' => 'listing_rejected',
                'template_name' => 'İlan Reddi Bildirimi',
                'template_subject' => 'İlanınız reddedilmiştir',
                'template_body' => $this->get_default_template('rejected'),
                'template_description' => 'İlan yönetici tarafından reddedildiğinde gönderilen e-posta'
            ),
            array(
                'template_key' => 'listing_deleted',
                'template_name' => 'İlan Silinme Bildirimi (Kullanıcı Tarafından)',
                'template_subject' => 'İlanınız silinmiştir',
                'template_body' => $this->get_default_template('deleted'),
                'template_description' => 'Kullanıcı ilan sildiğinde gönderilen e-posta'
            ),
            array(
                'template_key' => 'listing_deleted_by_admin',
                'template_name' => 'İlan Silinme Bildirimi (Yönetici Tarafından)',
                'template_subject' => 'İlanınız yönetici tarafından silinmiştir',
                'template_body' => $this->get_default_template('deleted_by_admin'),
                'template_description' => 'Yönetici ilan sildiğinde gönderilen e-posta'
            )
        );
        
        foreach ($default_templates as $template) {
            $existing = $wpdb->get_row(
                $wpdb->prepare("SELECT id FROM $templates_table WHERE template_key = %s", $template['template_key'])
            );
            
            if (!$existing) {
                $wpdb->insert($templates_table, $template);
            }
        }
    }
    
    /**
     * SMTP yapılandırmasını getir
     */
    public function get_smtp_settings() {
        global $wpdb;
        $settings_table = $wpdb->prefix . 'amator_telsiz_ayarlar';
        
        $settings = $wpdb->get_row("SELECT * FROM $settings_table LIMIT 1");
        
        return $settings ? (array) $settings : array(
            'smtp_host' => 'smtp.gmail.com',
            'smtp_port' => 587,
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_from_name' => 'Amatör Bitlik',
            'smtp_from_email' => get_option('admin_email'),
            'enable_notifications' => 1
        );
    }
    
    /**
     * E-posta şablonunu değişkenleriyle birlikte getir
     */
    public function get_mail_template($template_key) {
        global $wpdb;
        $templates_table = $wpdb->prefix . 'amator_telsiz_sablonlar';
        
        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $templates_table WHERE template_key = %s", $template_key)
        );
        
        // Şablon yoksa varsayılan şablonu kullan
        if (!$template) {
            error_log("ATIV: Veritabanında $template_key şablonu bulunamadı, varsayılan şablon kullanılacak");
            
            // Varsayılan template'leri kullan
            $default_templates_map = array(
                'listing_submitted' => array('key' => 'submitted', 'subject' => 'İlanınız başarıyla gönderilmiştir'),
                'listing_approved' => array('key' => 'approved', 'subject' => 'İlanınız onaylanmıştır'),
                'listing_rejected' => array('key' => 'rejected', 'subject' => 'İlanınız reddedilmiştir'),
                'listing_deleted' => array('key' => 'deleted', 'subject' => 'İlanınız silinmiştir'),
                'listing_deleted_by_admin' => array('key' => 'deleted_by_admin', 'subject' => 'İlanınız yönetici tarafından silinmiştir'),
                'admin_new_listing' => array('key' => 'admin_new_listing', 'subject' => '🆕 Yeni İlan: - Onay Bekleniyor'),
                'admin_listing_updated' => array('key' => 'admin_listing_updated', 'subject' => '🔄 İlan Güncellendi: - Tekrar Onay Bekleniyor'),
            );
            
            if (isset($default_templates_map[$template_key])) {
                $map = $default_templates_map[$template_key];
                $template = (object) array(
                    'template_key' => $template_key,
                    'template_name' => $template_key,
                    'template_subject' => $map['subject'],
                    'template_body' => $this->get_default_template($map['key']),
                    'template_description' => ''
                );
            } else {
                return null;
            }
        }
        
        return $template ? (array) $template : null;
    }
    
    /**
     * Varsayılan döviz kurlarını ekle
     */
    private function insert_default_exchange_rates() {
        global $wpdb;
        $table = $wpdb->prefix . 'amator_telsiz_doviz_kurlari';
        
        // Varsayılan kurlar
        $currencies = array(
            array('currency' => 'TRY', 'rate' => 1.0),
            array('currency' => 'USD', 'rate' => 32.50),  // Yaklaşık
            array('currency' => 'EUR', 'rate' => 35.00)   // Yaklaşık
        );
        
        foreach ($currencies as $currency) {
            $existing = $wpdb->get_row(
                $wpdb->prepare("SELECT id FROM $table WHERE currency = %s", $currency['currency'])
            );
            
            if (!$existing) {
                $wpdb->insert($table, $currency);
            }
        }
    }
    
    /**
     * API'den döviz kurlarını güncelle
     */
    public function update_exchange_rates_from_api() {
        global $wpdb;
        $table = $wpdb->prefix . 'amator_telsiz_doviz_kurlari';
        
        // Merkez Bankası API'sini kullan (Türkiye)
        $url = 'https://www.tcmb.gov.tr/kurlar/today.xml';
        
        $response = wp_remote_get($url, array('timeout' => 10));
        
        if (is_wp_error($response)) {
            error_log('ATIV Döviz Kuru Güncelleme Hatası: ' . $response->get_error_message());
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        
        // XML parse et
        $xml = simplexml_load_string($body);
        
        if (!$xml) {
            error_log('ATIV XML Parse Hatası');
            return false;
        }
        
        $currencies = array('USD', 'EUR');
        $updated_count = 0;
        
        foreach ($xml->Currency as $currency) {
            $currency_code = (string) $currency['Kod'];
            
            if (in_array($currency_code, $currencies)) {
                // Satış kuru (ForexSelling) veya Alış kuru (ForexBuying) kullan
                $rate_str = (string) $currency->ForexSelling;
                
                if (empty($rate_str)) {
                    $rate_str = (string) $currency->ForexBuying;
                }
                
                // Virgülü noktaya çevir
                $rate = (float) str_replace(',', '.', $rate_str);
                
                // Geçerli bir kur kontrolü
                if ($rate > 0) {
                    // Kuru güncelle
                    $wpdb->query($wpdb->prepare(
                        "UPDATE $table SET rate = %f, updated_at = %s WHERE currency = %s",
                        $rate,
                        current_time('mysql'),
                        $currency_code
                    ));
                    $updated_count++;
                    
                    error_log("ATIV: $currency_code kuru güncellendi: $rate TL");
                }
            }
        }
        
        // Güncelleme zamanını kaydet (24 saat)
        if ($updated_count > 0) {
            set_transient('ativ_exchange_rates_updated', true, 86400);
            return true;
        }
        
        return false;
    }
    
    /**
     * Döviz kurunu al
     */
    public function get_exchange_rate($currency) {
        global $wpdb;
        $table = $wpdb->prefix . 'amator_telsiz_doviz_kurlari';
        
        if ($currency === 'TRY') {
            return 1.0;
        }
        
        $rate = $wpdb->get_var($wpdb->prepare(
            "SELECT rate FROM $table WHERE currency = %s",
            $currency
        ));
        
        return $rate ? (float) $rate : 1.0;
    }
    
    /**
     * Fiyatı TL'ye dönüştür
     */
    public function convert_to_tl($price, $currency) {
        if ($currency === 'TRY') {
            return (float) $price;
        }
        
        $rate = $this->get_exchange_rate($currency);
        return (float) $price * $rate;
    }
    
    /**
     * E-posta gönder
     * 
     * @param string $to E-posta adresi
     * @param string $subject Konu
     * @param string $message İçerik
     * @param array $attachments Ekler (opsiyonel)
     * @return bool Başarı durumu
     */
    public function send_mail($to, $subject, $message, $attachments = array()) {
        $smtp_settings = $this->get_smtp_settings();
        
        // SMTP ayarları kontrol et
        if (empty($smtp_settings['smtp_host']) || empty($smtp_settings['smtp_username'])) {
            error_log('ATIV Mail: SMTP ayarları eksik');
            return false;
        }
        
        // PHPMailer kullan
        require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
        require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
        require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
        
        $mail = new \PHPMailer\PHPMailer\PHPMailer();
        
        try {
            // SMTP ayarlarını yapıl
            $mail->isSMTP();
            $mail->Host = $smtp_settings['smtp_host'];
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_settings['smtp_username'];
            $mail->Password = $smtp_settings['smtp_password'];
            $mail->SMTPSecure = $smtp_settings['smtp_port'] == 465 ? 'ssl' : 'tls';
            $mail->Port = intval($smtp_settings['smtp_port']);
            
            // Gönderici ve alıcı
            $mail->setFrom(
                $smtp_settings['smtp_from_email'],
                $smtp_settings['smtp_from_name']
            );
            $mail->addAddress($to);
            
            // İçerik
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = strip_tags($message);
            
            // Ekler
            if (!empty($attachments) && is_array($attachments)) {
                foreach ($attachments as $attachment) {
                    if (file_exists($attachment)) {
                        $mail->addAttachment($attachment);
                    }
                }
            }
            
            // Gönder
            $result = $mail->send();
            
            if ($result) {
                error_log("ATIV Mail: $to'ya başarılı gönderildi - Konu: $subject");
                return true;
            } else {
                error_log("ATIV Mail: Gönderilemedi - $to - Error: " . $mail->ErrorInfo);
                return false;
            }
            
        } catch (Exception $e) {
            error_log('ATIV Mail Exception: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Bildirim e-postasını hazırla ve gönder
     * 
     * @param string $template_key Şablon anahtarı
     * @param array $variables Şablon değişkenleri
     * @param string $recipient_email Alıcı e-postası
     * @return bool
     */
    public function send_notification($template_key, $variables = array(), $recipient_email = '') {
        if (empty($recipient_email)) {
            error_log('ATIV Notification: Alıcı e-postası boş - template_key: ' . $template_key);
            return false;
        }
        
        error_log('ATIV Notification: ' . $template_key . ' şablonuyla ' . $recipient_email . ' adresine bildirim gönderiliyor...');
        
        // Şablonu al
        $template = $this->get_mail_template($template_key);
        
        if (!$template) {
            error_log("ATIV Notification: $template_key şablonu bulunamadı");
            return false;
        }
        
        error_log('ATIV Notification: Şablon bulundu - Konu: ' . ($template['template_subject'] ?? 'YOK'));
        $subject = $template['template_subject'] ?? '';
        $body = $template['template_body'] ?? '';
        
        // Değişkenleri değiştir - hem {key} hem de [KEY] formatlarını destekle
        if (!empty($variables)) {
            foreach ($variables as $key => $value) {
                // {key} formatı
                $body = str_replace('{' . $key . '}', $value, $body);
                $subject = str_replace('{' . $key . '}', $value, $subject);
                // [KEY] formatı (geriye dönük uyumluluk)
                $body = str_replace('[' . strtoupper($key) . ']', $value, $body);
                $subject = str_replace('[' . strtoupper($key) . ']', $value, $subject);
            }
        }
        
        // Escape karakterlerini temizle
        $body = stripslashes($body);
        $subject = stripslashes($subject);
        
        // HTML wrapper ekle
        $html_body = '
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .email-container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #667eea; color: white; padding: 20px; border-radius: 4px 4px 0 0; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; border: 1px solid #ddd; border-radius: 0 0 4px 4px; }
                .footer { text-align: center; padding: 10px; font-size: 12px; color: #999; margin-top: 20px; }
                .button { display: inline-block; background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin: 10px 0; }
            </style>
        </head>
        <body>
            <div class="email-container">
                <div class="header">
                    <h2>📻 Amatör Bitlik</h2>
                </div>
                <div class="content">
                    ' . nl2br($body) . '
                </div>
                <div class="footer">
                    <p>Bu e-posta otomatik olarak gönderilmiştir. Lütfen yanıtlamayınız.</p>
                    <p>&copy; 2025 Amatör Bitlik - Tüm hakları saklıdır.</p>
                </div>
            </div>
        </body>
        </html>';
        
        // Mail gönder
        $result = $this->send_mail($recipient_email, $subject, $html_body);
        
        if ($result) {
            error_log('ATIV Notification: ' . $template_key . ' şablonuyla ' . $recipient_email . ' adresine bildirim başarıyla gönderildi!');
        } else {
            error_log('ATIV Notification: ' . $template_key . ' şablonuyla ' . $recipient_email . ' adresine bildirim gönderilemedi!');
        }
        
        return $result;
    }
    
    /**
     * Döviz kurlarını manuel olarak güncelle (test için)
     */
    public function test_update_exchange_rates() {
        $result = $this->update_exchange_rates_from_api();
        
        if ($result) {
            // Güncel kurları al
            global $wpdb;
            $rates_table = $wpdb->prefix . 'amator_telsiz_doviz_kurlari';
            $rates = $wpdb->get_results("SELECT currency, rate FROM $rates_table");
            
            $rate_text = '';
            foreach ($rates as $rate) {
                $rate_text .= $rate->currency . ': ' . number_format($rate->rate, 4) . ' TRY, ';
            }
            
            wp_send_json_success(array(
                'message' => '✅ Döviz kurları başarıyla güncellendi!<br><br>Güncel Kurlar: ' . rtrim($rate_text, ', ')
            ));
        } else {
            wp_send_json_error(array(
                'message' => '❌ Döviz kurları güncellenemedi. API isteğinde hata oluştu. Lütfen error log\'unu kontrol edin.'
            ));
        }
    }
    
    /**
     * Test mail gönder
     */
    public function test_send_mail() {
        // Admin check
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array(
                'message' => '❌ Yetkisiz erişim!'
            ));
            return;
        }
        
        global $wpdb;
        
        // SMTP ayarlarını al
        $settings_table = $wpdb->prefix . 'amator_telsiz_ayarlar';
        $settings = $wpdb->get_row("SELECT * FROM $settings_table LIMIT 1");
        
        if (!$settings) {
            wp_send_json_error(array(
                'message' => '❌ SMTP ayarları bulunamadı!'
            ));
            return;
        }
        
        $smtp_host = $settings->smtp_host;
        $smtp_port = $settings->smtp_port;
        $smtp_username = $settings->smtp_username;
        $smtp_password = $settings->smtp_password;
        $smtp_from_name = $settings->smtp_from_name;
        $smtp_from_email = $settings->smtp_from_email;
        
        // Şu anki yöneticinin e-postasını al
        $current_user = wp_get_current_user();
        $to_email = $current_user->user_email;
        
        // SMTP ayarları boş mu kontrol et
        if (empty($smtp_host) || empty($smtp_port) || empty($smtp_username) || empty($smtp_password)) {
            wp_send_json_error(array(
                'message' => '❌ SMTP ayarları eksik! Lütfen tüm SMTP ayarlarını doldurunuz.'
            ));
            return;
        }
        
        // Basit test e-postası gönder
        $subject = '🧪 Amatör Bitlik - Test E-postası';
        $message = '<!DOCTYPE html><html><head><meta charset="UTF-8"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head><body>';
        $message .= '<h2>Test E-postası</h2>';
        $message .= '<p>Bu, SMTP konfigürasyonunuzun düzgün çalışıp çalışmadığını kontrol etmek için gönderilen bir test e-postasıdır.</p>';
        $message .= '<p><strong>Gönderim Saati:</strong> ' . current_time('mysql') . '</p>';
        $message .= '<p>SMTP ayarlarınız doğru bir şekilde yapılandırılmış görünüyor!</p>';
        $message .= '</body></html>';
        
        $result = $this->send_mail($to_email, $subject, $message);
        
        if ($result) {
            wp_send_json_success(array(
                'message' => '✅ Test e-postası başarıyla gönderildi! Lütfen e-posta kutunuzu kontrol edin (spam klasörünü de kontrol etmeyi unutmayın).'
            ));
        } else {
            wp_send_json_error(array(
                'message' => '❌ E-posta gönderilemedi! SMTP ayarlarını kontrol edin ve tekrar deneyin.'
            ));
        }
    }
}

if (!function_exists('getCategoryName')) {
    function getCategoryName($category) {
        return AmateurTelsizIlanVitrini::get_category_name($category);
    }
}

new AmateurTelsizIlanVitrini();
?>