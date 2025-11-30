<?php
/**
 * Plugin Name: Amatör Bitlik
 * Description: Amatör telsiz ekipmanları için ilan panosu yönetim sistemi
 * Version: 4.8
 * Author: TA4AQG - Erkin Mercan
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
        add_action('init', array($this, 'init'));
        // CSS ve JS'i sadece shortcode kullanıldığında yükle
        add_shortcode('amator_telsiz_ilan', array($this, 'display_listings'));
        // Kullanıcının kendi ilanlarını gösteren shortcode
        add_shortcode('amator_my_listings', array($this, 'display_my_listings'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Admin menüsü
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Döviz kuru güncelleme hook'u
        add_action('ativ_update_exchange_rates', array($this, 'update_exchange_rates_from_api'));
    }
    
    public function init() {
        $this->create_tables();
        $this->insert_default_exchange_rates();
        $this->create_upload_dir();
        add_action('wp_ajax_ativ_ajax', array($this, 'handle_ajax'));
        add_action('wp_ajax_nopriv_ativ_ajax', array($this, 'handle_ajax'));
        
        // Custom cron interval'ı tanımla (6 saat)
        add_filter('cron_schedules', array($this, 'add_custom_cron_schedules'));
        
        // Her 6 saatte bir döviz kurlarını güncelle (cron job)
        if (!wp_next_scheduled('ativ_update_exchange_rates')) {
            wp_schedule_event(time(), 'sixhours', 'ativ_update_exchange_rates');
        }
        
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
        // Cron job'u temizle
        $timestamp = wp_next_scheduled('ativ_update_exchange_rates');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'ativ_update_exchange_rates');
        }
        flush_rewrite_rules();
    }
    
    private function create_upload_dir() {
        if (!file_exists(ATIV_UPLOAD_DIR)) {
            wp_mkdir_p(ATIV_UPLOAD_DIR);
        }
        
         // Güvenlik için .htaccess dosyası oluştur - Sadece görsellere izin ver
    $htaccess_file = ATIV_UPLOAD_DIR . '.htaccess';
    if (!file_exists($htaccess_file)) {
        $htaccess_content = 'Options -Indexes' . PHP_EOL .
                            'RewriteEngine On' . PHP_EOL .
                            PHP_EOL .
                            '# Sadece görsel dosyalara erişime izin ver' . PHP_EOL .
                            '<FilesMatch "\.(jpg|jpeg|png|gif|webp|JPG|JPEG|PNG|GIF|WEBP)$">' . PHP_EOL .
                            '    Allow from all' . PHP_EOL .
                            '    Satisfy Any' . PHP_EOL .
                            '</FilesMatch>' . PHP_EOL .
                            PHP_EOL .
                            '# Diğer tüm dosya türlerini engelle' . PHP_EOL .
                            '<FilesMatch "\.(php|html|htm|txt|log|sql|json|xml|htaccess)$">' . PHP_EOL .
                            '    Deny from all' . PHP_EOL .
                            '</FilesMatch>' . PHP_EOL .
                            PHP_EOL .
                            '# Varsayılan olarak tüm dosyaları engelle' . PHP_EOL .
                            'Deny from all';
        file_put_contents($htaccess_file, $htaccess_content);
    }
    
    // Güvenlik için index.html dosyası oluştur
    $index_file = ATIV_UPLOAD_DIR . 'index.html';
    if (!file_exists($index_file)) {
        file_put_contents($index_file, '<!-- Silence is golden -->');
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
        `condition` enum('Sıfır', 'Kullanılmış', 'Arızalı') NOT NULL,
        price decimal(10,2) NOT NULL,
        currency enum('TRY', 'USD', 'EUR') DEFAULT 'TRY',
        description longtext NOT NULL,
        images longtext,
        featured_image_index int(11) DEFAULT 0,
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
    // Modüler CSS dosyalarını kaydet (base, components, forms)
    wp_register_style('ativ-base', ATIV_PLUGIN_URL . 'css/base.css', array(), '1.1');
    wp_register_style('ativ-components', ATIV_PLUGIN_URL . 'css/components.css', array('ativ-base'), '1.1');
    wp_register_style('ativ-forms', ATIV_PLUGIN_URL . 'css/forms.css', array('ativ-components'), '1.1');
    
    // Modüler JS dosyalarını kaydet (core, modal, ui - sıralama önemli)
    wp_register_script('ativ-core', ATIV_PLUGIN_URL . 'js/core.js', array('jquery'), '1.1', true);
    wp_register_script('ativ-ui', ATIV_PLUGIN_URL . 'js/ui.js', array('ativ-core'), '1.1', true);
    wp_register_script('ativ-modal', ATIV_PLUGIN_URL . 'js/modal.js', array('ativ-ui'), '1.1', true);
    
    $current_user_id = get_current_user_id();
    
    wp_localize_script('ativ-core', 'ativ_ajax', array(
        'url' => admin_url('admin-ajax.php'),
        'nonce' => $current_user_id ? wp_create_nonce('ativ_nonce_' . $current_user_id) : wp_create_nonce('ativ_public_nonce'),
        'public_nonce' => wp_create_nonce('ativ_public_nonce'),
        'upload_url' => ATIV_UPLOAD_URL,
        'is_user_logged_in' => is_user_logged_in(),
        'user_id' => $current_user_id // Kullanıcı ID'sini front-end'e ilet
    ));
}
    
    public function display_listings() {
        // Script ve style'ları yükle
        $this->enqueue_scripts();
        wp_enqueue_style('ativ-base');
        wp_enqueue_style('ativ-components');
        wp_enqueue_style('ativ-forms');
        wp_enqueue_script('ativ-core');
        wp_enqueue_script('ativ-ui');
        wp_enqueue_script('ativ-modal');
        
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
        wp_enqueue_style('ativ-base');
        wp_enqueue_style('ativ-components');
        wp_enqueue_style('ativ-forms');
        wp_enqueue_script('ativ-core');
        wp_enqueue_script('ativ-ui');
        wp_enqueue_script('ativ-modal');

        ob_start();
        include ATIV_PLUGIN_PATH . 'templates/my-listings.php';
        return ob_get_clean();
    }
    
   public function handle_ajax() {
    $action = $_POST['action_type'] ?? $_REQUEST['action'] ?? '';
    
    // Admin edit modal için AJAX
    if ($action === 'ativ_get_listing_for_admin') {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Yetkisiz erişim');
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
        
        $id = intval($_POST['id'] ?? 0);
        $this->update_listing_admin($id, $_POST);
        wp_send_json_success('İlan güncellendi');
    }
    
    // İlan durumunu değiştir (onay/reddet)
    if ($action === 'ativ_change_listing_status') {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Yetkisiz erişim');
        }
        
        $id = intval($_POST['id'] ?? 0);
        $status = sanitize_text_field($_POST['status'] ?? '');
        $rejection_reason = isset($_POST['rejection_reason']) ? wp_kses_post($_POST['rejection_reason']) : '';
        
        if (!in_array($status, ['approved', 'rejected', 'pending'])) {
            wp_send_json_error('Geçersiz durum');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'amator_ilanlar';
        
        $update_data = array('status' => $status);
        if ($status === 'rejected') {
            $update_data['rejection_reason'] = $rejection_reason;
        } else {
            $update_data['rejection_reason'] = null;
        }
        
        $result = $wpdb->update($table_name, $update_data, array('id' => $id));
        
        if ($result !== false) {
            wp_send_json_success('Durum güncellendi');
        } else {
            wp_send_json_error('Durum güncellenirken hata oluştu');
        }
    }
    
    // Kritik işlemler için oturum ve nonce kontrolü
    $critical_actions = ['save_listing', 'update_listing', 'delete_listing', 'get_user_listings'];
    $public_actions = ['get_listings', 'get_brands', 'get_locations'];
    $admin_actions = ['test_update_rates'];
    
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
        case 'upload_image':
            $this->upload_image();
            break;
        case 'test_update_rates':
            $this->test_update_exchange_rates();
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
    
    $insert_data = array(
        'user_id' => $user_id, // Kullanıcı ID'sini ekle
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
        
        // Görsel dosya isimlerini güncelle
        $update_data = array(
            'images' => !empty($image_files) ? json_encode($image_files) : null,
            'featured_image_index' => intval($data['featuredImageIndex'] ?? 0)
        );
        
        $wpdb->update($table_name, $update_data, array('id' => $listing_id));
        
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
        // Base64 formatını kontrol et ve düzenle
        if (preg_match('/^data:image\/(\w+);base64,/', $base64_data, $matches)) {
            $image_type = $matches[1];
            $base64_data = substr($base64_data, strpos($base64_data, ',') + 1);
        } else {
            $image_type = 'jpg';
        }
        
        $base64_data = str_replace(' ', '+', $base64_data);
        $image_data = base64_decode($base64_data);
        
        if ($image_data === false) {
            return false;
        }
        
        // Dosya adını oluştur: [ilan-id]P[numara].[uzanti]
        $file_name = $listing_id . 'P' . sprintf('%02d', $image_number) . '.' . $image_type;
        $file_path = ATIV_UPLOAD_DIR . $listing_id . '/' . $file_name;
        
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
            if ($post_key === 'currency') {
                $update_data[$db_key] = sanitize_text_field($data[$post_key]);
            } else {
                $update_data[$db_key] = sanitize_text_field($data[$post_key]);
            }
        }
    }
    if (array_key_exists('seller_email', $data)) {
        $update_data['seller_email'] = sanitize_email($data['seller_email']);
    }
    if (array_key_exists('price', $data)) {
        $update_data['price'] = floatval($data['price']);
    }
    if (array_key_exists('description', $data)) {
        $update_data['description'] = sanitize_textarea_field($data['description']);
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
    if (!empty($existing_listing['status']) && $existing_listing['status'] === 'rejected') {
        $update_data['status'] = 'pending';
        $update_data['rejection_reason'] = null;
    }

    // Değişecek veri yoksa başarı döndür (no-op)
    if (empty($update_data)) {
        wp_send_json_success(array('message' => 'Değişiklik yok'));
    }

    $result = $wpdb->update($table_name, $update_data, array('id' => $id));
    
    if ($result !== false) {
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
    $existing_listing = $wpdb->get_row($wpdb->prepare("SELECT user_id, images FROM $table_name WHERE id = %d", $id), ARRAY_A);
    
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
    
    $result = $wpdb->delete($table_name, array('id' => $id));
    
    if ($result) {
        wp_send_json_success(array('message' => 'İlan başarıyla silindi'));
    } else {
        wp_send_json_error('İlan silinirken hata oluştu: ' . $wpdb->last_error);
    }
}
    
    private function delete_listing_images($listing_id, $image_files) {
        $listing_dir = ATIV_UPLOAD_DIR . $listing_id;
        
        foreach ($image_files as $image_file) {
            $file_path = $listing_dir . '/' . $image_file;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        // Klasörü de sil (eğer boşsa)
        if (is_dir($listing_dir) && count(scandir($listing_dir)) == 2) {
            rmdir($listing_dir);
        }
    }

    public static function get_category_name($category) {
        $categories = array(
            'transceiver' => 'Telsiz',
            'antenna' => 'Anten',
            'amplifier' => 'Amplifikatör',
            'accessory' => 'Aksesuar',
            'other' => 'Diğer'
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
            'Ayarlar',                                  // Sayfa başlığı
            'Ayarlar',                                  // Menü başlığı
            'manage_options',                          // Yetki
            'ativ-settings',                            // Menu slug
            array($this, 'admin_settings_page')        // Callback
        );
    }
    
    /**
     * Admin İlan Yönetim Sayfası
     */
    public function admin_listings_page() {
        // Silme işlemi
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
            $id = intval($_GET['id']);
            if (wp_verify_nonce($_GET['_wpnonce'] ?? '', 'ativ_delete_' . $id)) {
                $this->delete_listing_admin($id);
                echo '<div class="notice notice-success"><p>İlan başarıyla silindi.</p></div>';
            }
        }
        
        // Tüm ilanları getir
        global $wpdb;
        $table_name = $wpdb->prefix . 'amator_ilanlar';
        
        // Sayfalama
        $per_page = 15;
        $current_page = isset($_GET['paged']) ? intval($_GET['paged']) : 1;
        $offset = ($current_page - 1) * $per_page;
        
        // Arama ve filtreler
        $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $category_filter = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
        $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
        
        // WHERE şartlarını oluştur
        $where_clauses = array();
        $where_params = array();
        
        if ($search) {
            $where_clauses[] = "(title LIKE %s OR description LIKE %s OR seller_name LIKE %s)";
            $where_params[] = '%' . $search . '%';
            $where_params[] = '%' . $search . '%';
            $where_params[] = '%' . $search . '%';
        }
        
        if ($category_filter) {
            $where_clauses[] = "category = %s";
            $where_params[] = $category_filter;
        }
        
        if ($status_filter) {
            $where_clauses[] = "status = %s";
            $where_params[] = $status_filter;
        }
        
        // WHERE cümlesini ve parametreleri hazırla
        $where_sql = '';
        if (!empty($where_clauses)) {
            $where_sql = ' WHERE ' . implode(' AND ', $where_clauses);
        }
        
        // Parametreleri ekle (LIMIT ve OFFSET için)
        $where_params[] = $per_page;
        $where_params[] = $offset;
        
        // İstatistikler
        if (!empty($where_clauses)) {
            $total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name" . $where_sql, array_slice($where_params, 0, count($where_params) - 2)));
        } else {
            $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        }
        
        $this_month = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)");
        $total_users = $wpdb->get_var("SELECT COUNT(DISTINCT user_id) FROM $table_name");
        
        // Toplam değer hesaplaması - tüm fiyatları TL'ye dönüştür
        $all_listings = $wpdb->get_results("SELECT price, currency FROM $table_name", ARRAY_A);
        $total_amount = 0;
        foreach ($all_listings as $listing) {
            $total_amount += $this->convert_to_tl($listing['price'], $listing['currency']);
        }
        
        // Kategorileri al
        $categories = array(
            'transceiver' => 'Telsiz',
            'antenna' => 'Anten',
            'amplifier' => 'Amplifikatör',
            'accessory' => 'Aksesuar',
            'other' => 'Diğer'
        );
        
        $category_counts = $wpdb->get_results("SELECT category, COUNT(*) as count FROM $table_name GROUP BY category", ARRAY_A);
        $category_map = array();
        foreach ($category_counts as $cc) {
            $category_map[$cc['category']] = $cc['count'];
        }
        
        // Status istatistikleri
        $status_counts = $wpdb->get_results("SELECT status, COUNT(*) as count FROM $table_name GROUP BY status", ARRAY_A);
        $status_map = array('pending' => 0, 'approved' => 0, 'rejected' => 0);
        foreach ($status_counts as $sc) {
            $status_map[$sc['status']] = $sc['count'];
        }
        
        // İlanları getir
        $query = "SELECT * FROM $table_name" . $where_sql . " ORDER BY created_at DESC LIMIT %d OFFSET %d";
        $listings = $wpdb->get_results($wpdb->prepare($query, $where_params), ARRAY_A);
        
        $total_pages = ceil($total / $per_page);
        
        ?>
        <div class="wrap ativ-admin-wrap">
            <style>
            .ativ-admin-wrap {
                background: #f8f9fa;
                padding: 20px 0 !important;
            }
            
            .ativ-admin-header {
                background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
                color: white;
                padding: 30px;
                border-radius: 8px;
                margin: 0 20px 30px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            
            .ativ-admin-header h1 {
                color: white;
                margin: 0 0 10px 0;
                font-size: 28px;
            }
            
            .ativ-admin-header p {
                margin: 0;
                opacity: 0.9;
                font-size: 14px;
            }
            
            .ativ-stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin: 0 20px 30px;
            }
            
            .ativ-stat-card {
                background: white;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                border-left: 4px solid #0073aa;
                transition: all 0.3s ease;
            }
            
            .ativ-stat-card:hover {
                box-shadow: 0 4px 16px rgba(0,0,0,0.12);
                transform: translateY(-2px);
            }
            
            .ativ-stat-card.stat-total {
                border-left-color: #0073aa;
            }
            
            .ativ-stat-card.stat-month {
                border-left-color: #17a2b8;
            }
            
            .ativ-stat-card.stat-users {
                border-left-color: #28a745;
            }
            
            .ativ-stat-card.stat-revenue {
                border-left-color: #ffc107;
            }
            
            .ativ-stat-label {
                font-size: 12px;
                color: #666;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 8px;
            }
            
            .ativ-stat-value {
                font-size: 32px;
                font-weight: bold;
                color: #333;
            }
            
            .ativ-stat-icon {
                font-size: 24px;
                margin-right: 10px;
                opacity: 0.6;
            }
            
            .ativ-filters {
                background: white;
                padding: 20px;
                border-radius: 8px;
                margin: 0 20px 20px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                display: flex;
                gap: 15px;
                flex-wrap: wrap;
                align-items: flex-end;
            }
            
            .ativ-filter-group {
                flex: 1;
                min-width: 200px;
            }
            
            .ativ-filter-group label {
                display: block;
                font-weight: 600;
                margin-bottom: 5px;
                font-size: 13px;
                color: #333;
            }
            
            .ativ-filter-group input,
            .ativ-filter-group select {
                width: 100%;
                padding: 8px 12px;
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 13px;
            }
            
            .ativ-filter-group input:focus,
            .ativ-filter-group select:focus {
                outline: none;
                border-color: #0073aa;
                box-shadow: 0 0 0 3px rgba(0,115,170,0.1);
            }
            
            .ativ-filter-buttons {
                display: flex;
                gap: 10px;
            }
            
            .ativ-table-container {
                background: white;
                border-radius: 8px;
                margin: 0 20px 30px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
                overflow: hidden;
            }
            
            .ativ-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 13px;
            }
            
            .ativ-table thead {
                background: #f5f5f5;
                border-bottom: 2px solid #e0e0e0;
            }
            
            .ativ-table th {
                padding: 15px;
                text-align: left;
                font-weight: 600;
                color: #333;
                text-transform: uppercase;
                font-size: 12px;
                letter-spacing: 0.5px;
            }
            
            .ativ-table td {
                padding: 15px;
                border-bottom: 1px solid #e8e8e8;
            }
            
            .ativ-table tbody tr {
                transition: background-color 0.2s ease;
            }
            
            .ativ-table tbody tr:hover {
                background-color: #f9f9f9;
            }
            
            .ativ-listing-title {
                font-weight: 600;
                color: #0073aa;
                margin-bottom: 5px;
            }
            
            .ativ-listing-desc {
                font-size: 12px;
                color: #999;
                margin-top: 5px;
            }
            
            .ativ-category-badge {
                display: inline-block;
                padding: 4px 10px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
            }
            
            .ativ-category-transceiver { background: #e3f2fd; color: #1976d2; }
            .ativ-category-antenna { background: #f3e5f5; color: #7b1fa2; }
            .ativ-category-amplifier { background: #e8f5e9; color: #388e3c; }
            .ativ-category-accessory { background: #fff3e0; color: #e65100; }
            .ativ-category-other { background: #f5f5f5; color: #666; }
            
            .ativ-status-badge {
                display: inline-block;
                padding: 4px 10px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
            }
            
            .ativ-status-pending { background: #fff3cd; color: #856404; }
            .ativ-status-approved { background: #d4edda; color: #155724; }
            .ativ-status-rejected { background: #f8d7da; color: #721c24; }
            
            .ativ-status-new { background: #d4edda; color: #155724; padding: 4px 8px; border-radius: 4px; font-size: 11px; }
            .ativ-status-old { background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 4px; font-size: 11px; }
            
            .ativ-actions {
                display: flex;
                gap: 8px;
            }
            
            .ativ-btn {
                padding: 6px 12px;
                border: none;
                border-radius: 4px;
                font-size: 12px;
                cursor: pointer;
                transition: all 0.2s ease;
                font-weight: 600;
            }
            
            .ativ-btn-edit {
                background: #0073aa;
                color: white;
            }
            
            .ativ-btn-edit:hover {
                background: #005a87;
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(0,115,170,0.3);
            }
            
            .ativ-btn-delete {
                background: #dc3545;
                color: white;
            }
            
            .ativ-btn-delete:hover {
                background: #c82333;
                transform: translateY(-1px);
                box-shadow: 0 2px 8px rgba(220,53,69,0.3);
            }
            
            .ativ-pagination {
                display: flex;
                gap: 5px;
                justify-content: center;
                margin: 30px 20px;
                flex-wrap: wrap;
            }
            
            .ativ-pagination a,
            .ativ-pagination span {
                padding: 8px 12px;
                border-radius: 4px;
                border: 1px solid #ddd;
                text-decoration: none;
                font-size: 13px;
                font-weight: 600;
                transition: all 0.2s ease;
            }
            
            .ativ-pagination a:hover {
                background: #0073aa;
                color: white;
                border-color: #0073aa;
            }
            
            .ativ-pagination .current {
                background: #0073aa;
                color: white;
                border-color: #0073aa;
            }
            
            .ativ-no-results {
                text-align: center;
                padding: 40px 20px;
                color: #999;
            }
            
            .ativ-no-results p {
                font-size: 16px;
                margin: 0;
            }
            </style>
            
            <div class="ativ-admin-header">
                <h1>📻 Amatör Bitlik - İlan Yönetimi</h1>
                <p>Platform üzerinde yayınlanan tüm ilanları yönet ve kontrol et</p>
            </div>
            
            <!-- İstatistikler -->
            <div class="ativ-stats-grid">
                <div class="ativ-stat-card stat-total">
                    <div class="ativ-stat-label"><span class="ativ-stat-icon">📋</span> Toplam İlan</div>
                    <div class="ativ-stat-value"><?php echo $total; ?></div>
                </div>
                <div class="ativ-stat-card stat-month">
                    <div class="ativ-stat-label"><span class="ativ-stat-icon">📅</span> Bu Ayda Eklenen</div>
                    <div class="ativ-stat-value"><?php echo $this_month; ?></div>
                </div>
                <div class="ativ-stat-card stat-users">
                    <div class="ativ-stat-label"><span class="ativ-stat-icon">👥</span> Aktif Kullanıcı</div>
                    <div class="ativ-stat-value"><?php echo $total_users; ?></div>
                </div>
                <div class="ativ-stat-card stat-revenue">
                    <div class="ativ-stat-label"><span class="ativ-stat-icon">💰</span> Toplam Değer</div>
                    <div class="ativ-stat-value"><?php echo number_format($total_amount, 0); ?> TRY</div>
                </div>
            </div>
            
            <!-- Kategoriler Özeti -->
            <div class="ativ-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); margin-bottom: 30px;">
                <?php foreach ($categories as $cat_key => $cat_name) : ?>
                    <div class="ativ-stat-card" style="border-left-color: #ddd; cursor: pointer;" onclick="document.querySelector('select[name=category]').value='<?php echo $cat_key; ?>'; document.querySelector('form').submit();">
                        <div class="ativ-stat-label"><?php echo $cat_name; ?></div>
                        <div class="ativ-stat-value"><?php echo $category_map[$cat_key] ?? 0; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- İlan Durumu Özeti -->
            <div class="ativ-stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); margin-bottom: 30px;">
                <div class="ativ-stat-card" style="border-left-color: #ffc107; cursor: pointer;" onclick="document.querySelector('select[name=status]').value='pending'; document.querySelector('form').submit();">
                    <div class="ativ-stat-label">⏳ Onay Bekleyen</div>
                    <div class="ativ-stat-value"><?php echo $status_map['pending']; ?></div>
                </div>
                <div class="ativ-stat-card" style="border-left-color: #28a745; cursor: pointer;" onclick="document.querySelector('select[name=status]').value='approved'; document.querySelector('form').submit();">
                    <div class="ativ-stat-label">✅ Onaylanmış</div>
                    <div class="ativ-stat-value"><?php echo $status_map['approved']; ?></div>
                </div>
                <div class="ativ-stat-card" style="border-left-color: #dc3545; cursor: pointer;" onclick="document.querySelector('select[name=status]').value='rejected'; document.querySelector('form').submit();">
                    <div class="ativ-stat-label">❌ Reddedilmiş</div>
                    <div class="ativ-stat-value"><?php echo $status_map['rejected']; ?></div>
                </div>
            </div>
            
            <!-- Arama ve Filtreler -->
            <div class="ativ-filters">
                <form method="get" action="" style="display: flex; gap: 15px; width: 100%; flex-wrap: wrap; align-items: flex-end;">
                    <input type="hidden" name="page" value="ativ-listings">
                    
                    <div class="ativ-filter-group" style="min-width: 250px;">
                        <label>🔍 İlan Ara</label>
                        <input type="search" name="s" value="<?php echo esc_attr($search); ?>" placeholder="Başlık, açıklama, satıcı adı...">
                    </div>
                    
                    <div class="ativ-filter-group" style="min-width: 200px;">
                        <label>📂 Kategori</label>
                        <select name="category">
                            <option value="">Tümü</option>
                            <?php foreach ($categories as $cat_key => $cat_name) : ?>
                                <option value="<?php echo $cat_key; ?>" <?php selected($category_filter, $cat_key); ?>>
                                    <?php echo $cat_name; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="ativ-filter-group" style="min-width: 200px;">
                        <label>📊 Durum</label>
                        <select name="status">
                            <option value="">Tümü</option>
                            <option value="pending" <?php selected($status_filter, 'pending'); ?>>⏳ Onay Bekleyen</option>
                            <option value="approved" <?php selected($status_filter, 'approved'); ?>>✅ Onaylanmış</option>
                            <option value="rejected" <?php selected($status_filter, 'rejected'); ?>>❌ Reddedilmiş</option>
                        </select>
                    </div>
                    
                    <div class="ativ-filter-buttons">
                        <input type="submit" class="ativ-btn ativ-btn-edit" value="🔎 Filtrele">
                        <a href="?page=ativ-listings" class="ativ-btn ativ-btn-edit" style="text-decoration: none; text-align: center;">↺ Temizle</a>
                    </div>
                </form>
            </div>
            
            <!-- İlan Tablosu -->
            <div class="ativ-table-container">
                <?php if ($listings) : ?>
                    <table class="ativ-table">
                        <thead>
                            <tr>
                                <th style="width: 5%;">ID</th>
                                <th style="width: 25%;">İlan Bilgisi</th>
                                <th style="width: 10%;">Kategori</th>
                                <th style="width: 10%;">Durum</th>
                                <th style="width: 10%;">Satıcı</th>
                                <th style="width: 10%;">Fiyat</th>
                                <th style="width: 12%;">Tarih</th>
                                <th style="width: 18%;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($listings as $listing) : 
                                $user_info = get_userdata($listing['user_id']);
                                $user_name = $user_info ? $user_info->display_name : 'Bilinmiyor';
                                
                                // Görseli al
                                $images = $this->get_listing_images($listing['id'], $listing['images']);
                                $image_url = !empty($images) ? $images[0]['data'] : '';
                                
                                // Yeniliği kontrol et
                                $created = strtotime($listing['created_at']);
                                $days_ago = floor((time() - $created) / 86400);
                                $is_new = $days_ago < 7;
                                
                                // Renk
                                $category_class = 'ativ-category-' . $listing['category'];
                            ?>
                                <tr>
                                    <td><strong>#<?php echo $listing['id']; ?></strong></td>
                                    <td>
                                        <div style="display: flex; gap: 10px; align-items: center;">
                                            <?php if ($image_url) : ?>
                                                <img src="<?php echo esc_url($image_url); ?>" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; flex-shrink: 0;">
                                            <?php else : ?>
                                                <div style="width: 50px; height: 50px; background: #f0f0f0; border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">📸</div>
                                            <?php endif; ?>
                                            <div style="flex: 1; min-width: 0;">
                                                <div class="ativ-listing-title"><?php echo esc_html($listing['title']); ?></div>
                                                <div class="ativ-listing-desc"><?php echo esc_html(substr($listing['description'], 0, 60)); ?>...</div>
                                                <?php if ($is_new) : ?>
                                                    <span class="ativ-status-new">🆕 Yeni</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="ativ-category-badge <?php echo $category_class; ?>"><?php echo esc_html($this->get_category_name($listing['category'])); ?></span></td>
                                    <td>
                                        <?php
                                            $status_label = '';
                                            $status_class = '';
                                            if ($listing['status'] === 'pending') {
                                                $status_label = '⏳ Onay Bekliyor';
                                                $status_class = 'ativ-status-pending';
                                            } elseif ($listing['status'] === 'approved') {
                                                $status_label = '✅ Onaylandı';
                                                $status_class = 'ativ-status-approved';
                                            } elseif ($listing['status'] === 'rejected') {
                                                $status_label = '❌ Reddedildi';
                                                $status_class = 'ativ-status-rejected';
                                            }
                                        ?>
                                        <span class="ativ-status-badge <?php echo $status_class; ?>"><?php echo $status_label; ?></span>
                                    </td>
                                    <td>
                                        <strong><?php echo esc_html($user_name); ?></strong>
                                        <div style="font-size: 11px; color: #999; margin-top: 3px;"><?php echo esc_html($listing['callsign']); ?></div>
                                    </td>
                                    <td>
                                        <strong><?php echo number_format($listing['price'], 0); ?></strong>
                                        <div style="font-size: 11px; color: #999;"><?php echo esc_html($listing['currency']); ?></div>
                                    </td>
                                    <td>
                                        <div><?php echo esc_html(date('d.m.Y', strtotime($listing['created_at']))); ?></div>
                                        <div style="font-size: 11px; color: #999;"><?php echo esc_html(date('H:i', strtotime($listing['created_at']))); ?></div>
                                    </td>
                                    <td>
                                        <div class="ativ-actions" style="flex-direction: column; gap: 5px;">
                                            <?php if ($listing['status'] === 'pending') : ?>
                                                <button class="ativ-btn" style="background: #28a745; color: white; font-size: 11px; padding: 4px 8px;" onclick="changeListingStatus(<?php echo $listing['id']; ?>, 'approved')">✅ Onayla</button>
                                                <button class="ativ-btn" style="background: #dc3545; color: white; font-size: 11px; padding: 4px 8px;" onclick="openRejectModal(<?php echo $listing['id']; ?>)">❌ Reddet</button>
                                            <?php endif; ?>
                                            <button class="ativ-btn ativ-btn-edit" style="font-size: 11px; padding: 4px 8px;" onclick="openAdminEditModal(<?php echo $listing['id']; ?>)">✏️ Düzenle</button>
                                            <a class="ativ-btn ativ-btn-delete" style="font-size: 11px; padding: 4px 8px; text-align: center; text-decoration: none;" href="<?php echo wp_nonce_url(admin_url('admin.php?page=ativ-listings&action=delete&id=' . $listing['id']), 'ativ_delete_' . $listing['id']); ?>" onclick="return confirm('Bu ilanı silmek istediğinizden emin misiniz?')">🗑️ Sil</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else : ?>
                    <div class="ativ-no-results">
                        <p style="font-size: 48px; margin-bottom: 10px;">🔍</p>
                        <p><strong>İlan bulunamadı</strong></p>
                        <p style="font-size: 14px; margin-top: 10px; color: #ccc;">Arama kriterlerine uygun ilan yok</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if ($total_pages > 1) : ?>
                <div class="ativ-pagination">
                    <?php 
                    $search_param = $search ? '&s=' . urlencode($search) : '';
                    $category_param = $category_filter ? '&category=' . urlencode($category_filter) : '';
                    $status_param = $status_filter ? '&status=' . urlencode($status_filter) : '';
                    
                    // Önceki
                    if ($current_page > 1) {
                        echo '<a href="' . esc_url(admin_url('admin.php?page=ativ-listings&paged=' . ($current_page - 1) . $search_param . $category_param . $status_param)) . '">← Önceki</a>';
                    }
                    
                    // Sayfalar
                    for ($i = 1; $i <= $total_pages; $i++) {
                        if ($i === $current_page) {
                            echo '<span class="current">' . $i . '</span>';
                        } else {
                            echo '<a href="' . esc_url(admin_url('admin.php?page=ativ-listings&paged=' . $i . $search_param . $category_param . $status_param)) . '">' . $i . '</a>';
                        }
                    }
                    
                    // Sonraki
                    if ($current_page < $total_pages) {
                        echo '<a href="' . esc_url(admin_url('admin.php?page=ativ-listings&paged=' . ($current_page + 1) . $search_param . $category_param . $status_param)) . '">Sonraki →</a>';
                    }
                    ?>
                </div>
            <?php endif; ?>
        </div>
        
        <style>
            .admin-edit-modal {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 10000;
                align-items: center;
                justify-content: center;
                animation: fadeIn 0.3s ease;
            }
            
            @keyframes fadeIn {
                from { background: rgba(0, 0, 0, 0); }
                to { background: rgba(0, 0, 0, 0.5); }
            }
            
            @keyframes slideUp {
                from { 
                    opacity: 0;
                    transform: translateY(30px);
                }
                to { 
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .admin-edit-modal.active {
                display: flex;
            }
            
            .admin-edit-modal-content {
                background: white;
                border-radius: 12px;
                width: 90%;
                max-width: 850px;
                max-height: 90vh;
                overflow-y: auto;
                padding: 40px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: slideUp 0.3s ease;
            }
            
            .admin-edit-modal-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 2px solid #f0f0f0;
            }
            
            .admin-edit-modal-header h2 {
                margin: 0;
                color: #333;
                font-size: 24px;
            }
            
            .admin-edit-modal-close {
                background: none;
                border: none;
                font-size: 32px;
                cursor: pointer;
                color: #ccc;
                width: 40px;
                height: 40px;
                display: flex;
                align-items: center;
                justify-content: center;
                border-radius: 50%;
                transition: all 0.2s ease;
            }
            
            .admin-edit-modal-close:hover {
                background: #f0f0f0;
                color: #333;
            }
            
            #adminEditForm {
                font-size: 14px;
            }
            
            #adminEditForm label {
                display: block;
                font-weight: 600;
                margin-bottom: 8px;
                color: #333;
            }
            
            #adminEditForm input,
            #adminEditForm select,
            #adminEditForm textarea {
                width: 100%;
                padding: 10px 12px;
                border: 1px solid #ddd;
                border-radius: 6px;
                font-size: 14px;
                font-family: inherit;
                transition: all 0.2s ease;
            }
            
            #adminEditForm input:focus,
            #adminEditForm select:focus,
            #adminEditForm textarea:focus {
                outline: none;
                border-color: #0073aa;
                box-shadow: 0 0 0 4px rgba(0, 115, 170, 0.1);
            }
            
            #adminEditForm > div {
                margin-bottom: 20px;
            }
            
            #adminImageGallery {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
                gap: 12px;
                padding: 20px;
                background: #f9f9f9;
                border-radius: 8px;
                border: 2px dashed #ddd;
            }
            
            .admin-image-item {
                position: relative;
                aspect-ratio: 1;
                overflow: hidden;
                border-radius: 8px;
                background: #f0f0f0;
                transition: all 0.2s ease;
            }
            
            .admin-image-item img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                display: block;
            }
            
            .admin-image-delete-btn {
                position: absolute;
                top: 4px;
                right: 4px;
                background: #dc3545 !important;
                color: white;
                border: none;
                border-radius: 50%;
                width: 28px;
                height: 28px;
                padding: 0;
                cursor: pointer;
                font-size: 16px;
                display: none;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
                box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
            }
            
            .admin-image-item:hover .admin-image-delete-btn {
                display: flex !important;
            }
            
            .admin-image-delete-btn:hover {
                background: #c82333 !important;
                transform: scale(1.1);
                box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
            }
            
            #adminEditForm .ativ-form-buttons {
                display: flex;
                gap: 10px;
                margin-top: 30px;
                padding-top: 20px;
                border-top: 2px solid #f0f0f0;
            }
            
            #adminEditForm .ativ-form-buttons button {
                flex: 1;
                padding: 12px 20px;
                border: none;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s ease;
            }
            
            #adminEditForm .ativ-form-buttons button[type="submit"] {
                background: linear-gradient(135deg, #0073aa 0%, #005a87 100%);
                color: white;
            }
            
            #adminEditForm .ativ-form-buttons button[type="submit"]:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(0, 115, 170, 0.3);
            }
            
            #adminEditForm .ativ-form-buttons button[type="button"] {
                background: #f0f0f0;
                color: #333;
            }
            
            #adminEditForm .ativ-form-buttons button[type="button"]:hover {
                background: #e0e0e0;
            }
        </style>
        
        <div id="adminEditModal" class="admin-edit-modal">
            <div class="admin-edit-modal-content">
                <div class="admin-edit-modal-header">
                    <h2>İlan Düzenle</h2>
                    <button class="admin-edit-modal-close" onclick="closeAdminEditModal()">×</button>
                </div>
                <div id="adminEditContent"></div>
            </div>
        </div>
        
        <!-- Red Nedeni Modal -->
        <div id="rejectModal" class="admin-edit-modal">
            <div class="admin-edit-modal-content" style="max-width: 500px;">
                <div class="admin-edit-modal-header">
                    <h2>❌ İlan Reddet</h2>
                    <button class="admin-edit-modal-close" onclick="closeRejectModal()">×</button>
                </div>
                <form id="rejectForm" onsubmit="submitRejectForm(event)">
                    <input type="hidden" id="rejectListingId" name="id">
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Red Nedeni</label>
                        <textarea id="rejectionReason" name="rejection_reason" placeholder="Lütfen bu ilanı neden reddettiğinizi açıklayın..." rows="6" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; font-family: inherit; transition: all 0.2s ease;" onblur="this.style.borderColor='#ddd'" onfocus="this.style.borderColor='#0073aa'; this.style.boxShadow='0 0 0 4px rgba(0, 115, 170, 0.1)'"></textarea>
                    </div>
                    
                    <div class="ativ-form-buttons" style="display: flex; gap: 10px; margin-top: 20px; padding-top: 20px; border-top: 2px solid #f0f0f0;">
                        <button type="submit" style="flex: 1; padding: 12px 20px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;" title="İlanı reddet ve nedeni kaydet">❌ Reddet</button>
                        <button type="button" onclick="closeRejectModal()" style="flex: 1; padding: 12px 20px; background: #f0f0f0; color: #333; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">İptal</button>
                    </div>
                </form>
            </div>
        </div>
        
        <script>
        function openAdminEditModal(id) {
            const modal = document.getElementById('adminEditModal');
            const content = document.getElementById('adminEditContent');
            
            // Loading göster
            content.innerHTML = '<div style="text-align: center; padding: 40px; color: #999;"><p style="font-size: 48px; margin: 0;">⏳</p><p>Yükleniyor...</p></div>';
            
            // AJAX ile ilanı getir
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=ativ_ajax&action_type=ativ_get_listing_for_admin&id=' + id
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    content.innerHTML = data.data;
                    modal.classList.add('active');
                    // Body scroll'u deaktif et
                    document.body.style.overflow = 'hidden';
                } else {
                    content.innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><p>❌ İlan yüklenemedi</p><p>' + (data.data || 'Bilinmeyen hata') + '</p></div>';
                }
            })
            .catch(error => {
                content.innerHTML = '<div style="text-align: center; padding: 40px; color: #dc3545;"><p>❌ Hata</p><p>' + error + '</p></div>';
            });
        }
        
        function submitAdminEditForm(e) {
            e.preventDefault();
            
            const form = document.getElementById('adminEditForm');
            const formData = new FormData(form);
            
            // Kalan görselleri topla
            const remainingImages = [];
            document.querySelectorAll('#adminImageGallery .admin-image-item').forEach(item => {
                const imageData = item.querySelector('.image-data').value;
                remainingImages.push({
                    data: imageData,
                    name: imageData.split('/').pop() // Dosya adı
                });
            });
            
            // Submit butonunu deaktif et
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = '⏳ Kaydediliyor...';
            
            // AJAX ile güncelle
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'ativ_ajax',
                    action_type: 'ativ_update_listing_admin',
                    id: formData.get('id'),
                    title: formData.get('title'),
                    category: formData.get('category'),
                    brand: formData.get('brand'),
                    model: formData.get('model'),
                    condition: formData.get('condition'),
                    price: formData.get('price'),
                    description: formData.get('description'),
                    images: JSON.stringify(remainingImages)
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Başarı animasyonu
                    submitBtn.textContent = '✅ Kaydedildi!';
                    submitBtn.style.background = '#28a745';
                    setTimeout(() => {
                        closeAdminEditModal();
                        location.reload();
                    }, 1500);
                } else {
                    alert('❌ Hata: ' + (data.data || 'Bilinmeyen hata'));
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalText;
                }
            })
            .catch(error => {
                alert('❌ Hata: ' + error);
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        }
        
        function closeAdminEditModal() {
            const modal = document.getElementById('adminEditModal');
            modal.classList.remove('active');
            // Body scroll'u etkinleştir
            document.body.style.overflow = 'auto';
        }
        
        function openRejectModal(id) {
            const modal = document.getElementById('rejectModal');
            document.getElementById('rejectListingId').value = id;
            document.getElementById('rejectionReason').value = '';
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeRejectModal() {
            const modal = document.getElementById('rejectModal');
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        function submitRejectForm(e) {
            e.preventDefault();
            
            const id = document.getElementById('rejectListingId').value;
            const reason = document.getElementById('rejectionReason').value;
            
            if (!reason.trim()) {
                alert('❌ Lütfen red nedenini yazınız');
                return;
            }
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'ativ_ajax',
                    action_type: 'ativ_change_listing_status',
                    id: id,
                    status: 'rejected',
                    rejection_reason: reason
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeRejectModal();
                    location.reload();
                } else {
                    alert('❌ Hata: ' + (data.data || 'Bilinmeyen hata'));
                }
            })
            .catch(error => {
                alert('❌ Hata: ' + error);
            });
        }
        
        function removeImageFromForm(btn) {
            btn.closest('.admin-image-item').style.animation = 'fadeOut 0.3s ease forwards';
            setTimeout(() => {
                btn.closest('.admin-image-item').remove();
                updateImageCount();
            }, 300);
        }
        
        function updateImageCount() {
            const count = document.querySelectorAll('#adminImageGallery .admin-image-item').length;
            document.getElementById('imageCount').textContent = count;
        }
        
        function changeListingStatus(id, status) {
            let statusLabel = status === 'approved' ? 'onaylamak' : 'reddetmek';
            if (!confirm('Bu ilanı ' + statusLabel + ' istediğinizden emin misiniz?')) {
                return;
            }
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'ativ_ajax',
                    action_type: 'ativ_change_listing_status',
                    id: id,
                    status: status
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('❌ Hata: ' + (data.data || 'Bilinmeyen hata'));
                }
            })
            .catch(error => {
                alert('❌ Hata: ' + error);
            });
        }
        
        // Modal dışına tıklandığında kapat
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('adminEditModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        closeAdminEditModal();
                    }
                });
            }
        });
        
        // ESC tuşu ile kapat
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('adminEditModal');
                if (modal && modal.classList.contains('active')) {
                    closeAdminEditModal();
                }
            }
        });
        
        // Fade out animasyonu ekle
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                from {
                    opacity: 1;
                    transform: scale(1);
                }
                to {
                    opacity: 0;
                    transform: scale(0.8);
                }
            }
        `;
        document.head.appendChild(style);
        </script>
        <?php
    }
    
    /**
     * Admin tarafından ilanı sil
     */
    private function delete_listing_admin($id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'amator_ilanlar';
        
        // Yetki kontrolü
        if (!current_user_can('manage_options')) {
            return false;
        }
        
        // Görselleri sil
        $listing = $wpdb->get_row($wpdb->prepare("SELECT images FROM $table_name WHERE id = %d", $id), ARRAY_A);
        if ($listing && $listing['images']) {
            $images = json_decode($listing['images'], true);
            if (is_array($images)) {
                foreach ($images as $image) {
                    if (isset($image['filename'])) {
                        $file_path = ATIV_UPLOAD_DIR . $image['filename'];
                        if (file_exists($file_path)) {
                            unlink($file_path);
                        }
                    }
                }
            }
        }
        
        // İlanı sil
        $wpdb->delete($table_name, array('id' => $id), array('%d'));
        
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
     * Admin Ayarlar Sayfası
     */
    public function admin_settings_page() {
        global $wpdb;
        
        // Ayarları kaydet
        if (isset($_POST['action']) && $_POST['action'] === 'ativ_save_settings') {
            if (!wp_verify_nonce($_POST['ativ_settings_nonce'], 'ativ_settings_nonce')) {
                wp_die('Güvenlik hatası');
            }
            
            $settings_table = $wpdb->prefix . 'amator_telsiz_ayarlar';
            
            // SMTP ayarlarını kaydet
            $smtp_data = array(
                'smtp_host' => sanitize_text_field($_POST['smtp_host'] ?? 'smtp.gmail.com'),
                'smtp_port' => intval($_POST['smtp_port'] ?? 587),
                'smtp_username' => sanitize_text_field($_POST['smtp_username'] ?? ''),
                'smtp_password' => sanitize_text_field($_POST['smtp_password'] ?? ''),
                'smtp_from_name' => sanitize_text_field($_POST['smtp_from_name'] ?? 'Amatör Bitlik'),
                'smtp_from_email' => sanitize_email($_POST['smtp_from_email'] ?? get_option('admin_email')),
                'enable_notifications' => 1,
                'updated_at' => current_time('mysql')
            );
            
            // Kayıt varsa güncelle, yoksa ekle
            $existing = $wpdb->get_row("SELECT id FROM $settings_table LIMIT 1");
            if ($existing) {
                $wpdb->update($settings_table, $smtp_data, array('id' => $existing->id));
            } else {
                $wpdb->insert($settings_table, $smtp_data);
            }
            
            // Mail şablonlarını kaydet
            $templates_table = $wpdb->prefix . 'amator_telsiz_sablonlar';
            
            $templates = array(
                'listing_submitted' => array(
                    'name' => 'İlan Gönderimi Bildirimi',
                    'body' => sanitize_textarea_field($_POST['mail_template_listing_submitted'] ?? '')
                ),
                'listing_approved' => array(
                    'name' => 'İlan Onayı Bildirimi',
                    'body' => sanitize_textarea_field($_POST['mail_template_listing_approved'] ?? '')
                ),
                'listing_rejected' => array(
                    'name' => 'İlan Reddi Bildirimi',
                    'body' => sanitize_textarea_field($_POST['mail_template_listing_rejected'] ?? '')
                ),
                'listing_deleted' => array(
                    'name' => 'İlan Silinme Bildirimi (Kullanıcı Tarafından)',
                    'body' => sanitize_textarea_field($_POST['mail_template_listing_deleted'] ?? '')
                ),
                'listing_deleted_by_admin' => array(
                    'name' => 'İlan Silinme Bildirimi (Yönetici Tarafından)',
                    'body' => sanitize_textarea_field($_POST['mail_template_listing_deleted_by_admin'] ?? '')
                )
            );
            
            foreach ($templates as $template_key => $template_data) {
                $existing_template = $wpdb->get_row(
                    $wpdb->prepare("SELECT id FROM $templates_table WHERE template_key = %s", $template_key)
                );
                
                $template_update_data = array(
                    'template_name' => $template_data['name'],
                    'template_body' => $template_data['body'],
                    'updated_at' => current_time('mysql')
                );
                
                if ($existing_template) {
                    $wpdb->update($templates_table, $template_update_data, array('id' => $existing_template->id));
                } else {
                    $wpdb->insert($templates_table, array_merge(
                        array('template_key' => $template_key),
                        $template_update_data
                    ));
                }
            }
            
            echo '<div class="notice notice-success"><p>⚙️ Ayarlar başarıyla kaydedildi!</p></div>';
        }
        
        // Veritabanından ayarları getir
        $settings_table = $wpdb->prefix . 'amator_telsiz_ayarlar';
        $templates_table = $wpdb->prefix . 'amator_telsiz_sablonlar';
        
        $settings = $wpdb->get_row("SELECT * FROM $settings_table LIMIT 1");
        
        $smtp_host = $settings ? $settings->smtp_host : 'smtp.gmail.com';
        $smtp_port = $settings ? $settings->smtp_port : 587;
        $smtp_username = $settings ? $settings->smtp_username : '';
        $smtp_password = $settings ? $settings->smtp_password : '';
        $smtp_from_name = $settings ? $settings->smtp_from_name : 'Amatör Bitlik';
        $smtp_from_email = $settings ? $settings->smtp_from_email : get_option('admin_email');
        
        // Şablonları getir
        $mail_template_listing_submitted = $this->get_template_body('listing_submitted', 'submitted');
        $mail_template_listing_approved = $this->get_template_body('listing_approved', 'approved');
        $mail_template_listing_rejected = $this->get_template_body('listing_rejected', 'rejected');
        $mail_template_listing_deleted = $this->get_template_body('listing_deleted', 'deleted');
        $mail_template_listing_deleted_by_admin = $this->get_template_body('listing_deleted_by_admin', 'deleted_by_admin');
        
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
            </style>
            
            <div class="ativ-settings-header">
                <h1>⚙️ Amatör Bitlik - Ayarlar</h1>
                <p>E-posta bildirimleri ve SMTP ayarlarını düzenleyin</p>
            </div>
            
            <form method="POST" action="">
                <?php wp_nonce_field('ativ_settings_nonce', 'ativ_settings_nonce'); ?>
                <input type="hidden" name="action" value="ativ_save_settings">
                
                <!-- Sekmeler -->
                <div class="ativ-settings-tabs">
                    <button type="button" class="ativ-settings-tab active" onclick="switchTab(event, 'smtp')">📧 SMTP Ayarları</button>
                    <button type="button" class="ativ-settings-tab" onclick="switchTab(event, 'templates')">📝 Mail Şablonları</button>
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
        }
        </script>
        <?php
    }
    
    /**
     * Varsayılan mail şablonlarını döndür
     */
    private function get_default_template($type) {
        $templates = array(
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

İlana buradan erişebilirsiniz: {listing_url}

Saygılarımızla,
Amatör Bitlik Ekibi
EOT,
            'rejected' => <<<'EOT'
Merhaba {seller_name},

Maalesef, "{title}" adlı ilanınız reddedilmiştir.

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
        
        $template = $wpdb->get_row(
            $wpdb->prepare("SELECT template_body FROM $templates_table WHERE template_key = %s", $template_key)
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
}

if (!function_exists('getCategoryName')) {
    function getCategoryName($category) {
        return AmateurTelsizIlanVitrini::get_category_name($category);
    }
}

new AmateurTelsizIlanVitrini();
?>