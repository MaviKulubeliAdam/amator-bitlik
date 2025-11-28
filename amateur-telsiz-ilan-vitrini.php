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
    }
    
    public function init() {
        $this->create_tables();
        $this->create_upload_dir();
        add_action('wp_ajax_ativ_ajax', array($this, 'handle_ajax'));
        add_action('wp_ajax_nopriv_ativ_ajax', array($this, 'handle_ajax'));
    }
    
    public function activate() {
        $this->create_tables();
        $this->create_upload_dir();
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
    
    $table_name = $wpdb->prefix . 'amator_ilanlar';
    
    $charset_collate = $wpdb->get_charset_collate();
    
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
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        INDEX user_id (user_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    if (!empty($wpdb->last_error)) {
        error_log('ATIV Tablo oluşturma hatası: ' . $wpdb->last_error);
    }
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
    $action = $_POST['action_type'] ?? '';
    
    // Kritik işlemler için oturum ve nonce kontrolü
    $critical_actions = ['save_listing', 'update_listing', 'delete_listing'];
    $public_actions = ['get_listings', 'get_brands', 'get_locations'];
    
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
        default:
            wp_send_json_error('Geçersiz işlem');
    }
}
    
    private function get_listings() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'amator_ilanlar';
    
    // Varsayılan sıralama: yeniden eskiye
    $listings = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC", ARRAY_A);
    
    if ($wpdb->last_error) {
        wp_send_json_error('Veritabanı hatası: ' . $wpdb->last_error);
    }
    
    // Görselleri URL formatına çevir
    foreach ($listings as &$listing) {
        $listing['images'] = $this->get_listing_images($listing['id'], $listing['images']);
        
        if (empty($listing['images'])) {
            $listing['emoji'] = '📻';
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
        'seller_phone' => sanitize_text_field($data['seller_phone'])
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
    $existing_listing = $wpdb->get_row($wpdb->prepare("SELECT user_id, images, featured_image_index, emoji FROM $table_name WHERE id = %d", $id), ARRAY_A);
    
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
}

// Global helper fonksiyonu
if (!function_exists('getCategoryName')) {
    function getCategoryName($category) {
        return AmateurTelsizIlanVitrini::get_category_name($category);
    }
}

new AmateurTelsizIlanVitrini();
?>