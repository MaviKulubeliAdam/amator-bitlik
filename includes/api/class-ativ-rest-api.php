<?php
/**
 * Amatör Bitlik - REST API Yönetimi
 * 
 * Bu sınıf WordPress REST API'si aracılığıyla mobil ve dış uygulamalar için
 * tüm endpoint'leri yönetir.
 * 
 * @package AmatorBitlik
 * @subpackage API
 * @since 1.2.0
 */

if (!defined('ABSPATH')) {
    exit;
}

class ATIV_REST_API {
    
    /**
     * API namespace ve versiyonu
     */
    private $namespace = 'amator-bitlik/v1';
    
    /**
     * Sınıf başlatıldığında çalıştırılan fonksiyon
     */
    public function __construct() {
        add_action('rest_api_init', array($this, 'register_routes'));
        add_action('rest_api_init', array($this, 'register_auth_routes'));
        // Tüm API yanıtlarına CORS başlıklarını ekle
        add_filter('rest_pre_serve_request', array($this, 'add_cors_headers'), 0, 4);
        // Çerezlerin cross-site gönderimi için SameSite=None; Secure zorla
        add_filter('wp_cookie_samesite', array($this, 'force_cookie_samesite_none'), 10, 2);
        add_filter('secure_auth_cookie', '__return_true');
        add_filter('secure_logged_in_cookie', '__return_true');
    }
    
    /**
     * REST API route'larını kaydet
     */
    public function register_routes() {
        // GET: Tüm ilanları listele (filtreleme seçenekleriyle)
        register_rest_route($this->namespace, '/listings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_listings'),
            'permission_callback' => '__return_true',
            'args' => array(
                'page' => array(
                    'description' => 'Sayfa numarası',
                    'type' => 'integer',
                    'default' => 1,
                    'sanitize_callback' => 'absint'
                ),
                'per_page' => array(
                    'description' => 'Sayfa başına ilan sayısı',
                    'type' => 'integer',
                    'default' => 20,
                    'sanitize_callback' => 'absint'
                ),
                'status' => array(
                    'description' => 'İlan durumu (pending, approved, rejected, suspended)',
                    'type' => 'string',
                    'default' => 'approved',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'category' => array(
                    'description' => 'Kategori (transceiver, antenna, amplifier, accessory, other)',
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'search' => array(
                    'description' => 'Başlık ve marka içinde arama',
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'location' => array(
                    'description' => 'Konum filtresi',
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'min_price' => array(
                    'description' => 'Minimum fiyat',
                    'type' => 'number',
                    'sanitize_callback' => array($this, 'sanitize_float')
                ),
                'max_price' => array(
                    'description' => 'Maksimum fiyat',
                    'type' => 'number',
                    'sanitize_callback' => array($this, 'sanitize_float')
                ),
                'orderby' => array(
                    'description' => 'Sıralama kriteri (created_at, price, title)',
                    'type' => 'string',
                    'default' => 'created_at',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'order' => array(
                    'description' => 'Sıralama yönü (ASC, DESC)',
                    'type' => 'string',
                    'default' => 'DESC',
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
        
        // GET: Tek bir ilanı getir
        register_rest_route($this->namespace, '/listings/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_listing'),
            'permission_callback' => '__return_true',
            'args' => array(
                'id' => array(
                    'description' => 'İlan ID\'si',
                    'type' => 'integer',
                    'required' => true,
                    'sanitize_callback' => 'absint'
                )
            )
        ));
        
        // POST: Yeni ilan oluştur (giriş gerekli)
        register_rest_route($this->namespace, '/listings', array(
            'methods' => 'POST',
            'callback' => array($this, 'create_listing'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'title' => array(
                    'description' => 'İlan başlığı',
                    'type' => 'string',
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'category' => array(
                    'description' => 'Kategori',
                    'type' => 'string',
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'brand' => array(
                    'description' => 'Marka',
                    'type' => 'string',
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'model' => array(
                    'description' => 'Model',
                    'type' => 'string',
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'condition' => array(
                    'description' => 'Durumu',
                    'type' => 'string',
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'price' => array(
                    'description' => 'Fiyat',
                    'type' => 'number',
                    'required' => true,
                    'sanitize_callback' => array($this, 'sanitize_float')
                ),
                'currency' => array(
                    'description' => 'Para birimi (TRY, USD, EUR)',
                    'type' => 'string',
                    'default' => 'TRY',
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'description' => array(
                    'description' => 'İlan açıklaması',
                    'type' => 'string',
                    'required' => true,
                    'sanitize_callback' => 'sanitize_textarea_field'
                ),
                'location' => array(
                    'description' => 'Konum',
                    'type' => 'string',
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'seller_name' => array(
                    'description' => 'Satıcı adı',
                    'type' => 'string',
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'seller_email' => array(
                    'description' => 'Satıcı e-postası',
                    'type' => 'string',
                    'required' => false,
                    'sanitize_callback' => 'sanitize_email'
                ),
                'seller_phone' => array(
                    'description' => 'Satıcı telefonu',
                    'type' => 'string',
                    'required' => false,
                    'sanitize_callback' => 'sanitize_text_field'
                )
            )
        ));
        
        // PUT/PATCH: İlan güncelle (kendi ilanları yalnızca sahibi)
        register_rest_route($this->namespace, '/listings/(?P<id>\d+)', array(
            'methods' => array('PUT', 'PATCH'),
            'callback' => array($this, 'update_listing'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'id' => array(
                    'description' => 'İlan ID\'si',
                    'type' => 'integer',
                    'required' => true,
                    'sanitize_callback' => 'absint'
                )
            )
        ));
        
        // DELETE: İlan sil (kendi ilanları yalnızca sahibi)
        register_rest_route($this->namespace, '/listings/(?P<id>\d+)', array(
            'methods' => 'DELETE',
            'callback' => array($this, 'delete_listing'),
            'permission_callback' => array($this, 'check_user_permission'),
            'args' => array(
                'id' => array(
                    'description' => 'İlan ID\'si',
                    'type' => 'integer',
                    'required' => true,
                    'sanitize_callback' => 'absint'
                )
            )
        ));
        
        // GET: Kategorileri listele
        register_rest_route($this->namespace, '/categories', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_categories'),
            'permission_callback' => '__return_true'
        ));
        
        // GET: Şehirleri listele
        register_rest_route($this->namespace, '/cities', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_cities'),
            'permission_callback' => '__return_true'
        ));
        
        // GET: Kullanıcı profil bilgisi (giriş gerekli)
        register_rest_route($this->namespace, '/user/profile', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_user_profile'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // POST: Kullanıcı profil güncelle
        register_rest_route($this->namespace, '/user/profile', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_user_profile'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
        
        // GET: Kullanıcının ilanlarını listele
        register_rest_route($this->namespace, '/user/listings', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_user_listings'),
            'permission_callback' => array($this, 'check_user_permission')
        ));
    }
    
    /**
     * Kullanıcı izin kontrolü
     */
    public function check_user_permission($request = null) {
        // Önce standart kontrol
        if (is_user_logged_in()) {
            return true;
        }

        // X-WP-Nonce varsa doğrula
        if ($request && method_exists($request, 'get_header')) {
            $nonce = $request->get_header('X-WP-Nonce');
            if ($nonce && wp_verify_nonce($nonce, 'wp_rest')) {
                // Cookie'den kullanıcıyı doğrula ve ayarla
                $cookie_val = null;
                if (defined('LOGGED_IN_COOKIE') && isset($_COOKIE[LOGGED_IN_COOKIE])) {
                    $cookie_val = $_COOKIE[LOGGED_IN_COOKIE];
                } else {
                    foreach ($_COOKIE as $name => $val) {
                        if (strpos($name, 'wordpress_logged_in_') === 0) {
                            $cookie_val = $val;
                            break;
                        }
                    }
                }
                if ($cookie_val) {
                    $user_id = wp_validate_auth_cookie($cookie_val, 'logged_in');
                    if ($user_id) {
                        wp_set_current_user($user_id);
                        return true;
                    }
                }
            }
        }

        return false;
    }
    
    /**
     * Tüm ilanları getir (filtreleme seçenekleriyle)
     */
    public function get_listings($request) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'amator_ilanlar';
        
        // Parametreleri al
        $page = $request->get_param('page') ?? 1;
        $per_page = $request->get_param('per_page') ?? 20;
        $status = $request->get_param('status') ?? 'approved';
        $category = $request->get_param('category');
        $search = $request->get_param('search');
        $location = $request->get_param('location');
        $min_price = $request->get_param('min_price');
        $max_price = $request->get_param('max_price');
        $orderby = $request->get_param('orderby') ?? 'created_at';
        $order = strtoupper($request->get_param('order') ?? 'DESC');
        
        // Temel SQL sorgusu
        $query = "SELECT * FROM $table_name WHERE 1=1";
        $params = array();
        
        // Durum filtresi
        if (!empty($status)) {
            $query .= " AND status = %s";
            $params[] = $status;
        }
        
        // Kategori filtresi
        if (!empty($category)) {
            $query .= " AND category = %s";
            $params[] = $category;
        }
        
        // Arama filtresi
        if (!empty($search)) {
            $query .= " AND (title LIKE %s OR brand LIKE %s OR model LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($search) . '%';
            $params[] = $search_term;
            $params[] = $search_term;
            $params[] = $search_term;
        }
        
        // Konum filtresi
        if (!empty($location)) {
            $query .= " AND location LIKE %s";
            $params[] = '%' . $wpdb->esc_like($location) . '%';
        }
        
        // Fiyat filtresi (sadece TRY'de)
        if ($min_price !== null) {
            $query .= " AND price >= %f AND currency = 'TRY'";
            $params[] = floatval($min_price);
        }
        if ($max_price !== null) {
            $query .= " AND price <= %f AND currency = 'TRY'";
            $params[] = floatval($max_price);
        }
        
        // Toplam sayıyı al
        $total_query = "SELECT COUNT(*) FROM $table_name WHERE 1=1";
        if (!empty($status)) {
            $total_query .= " AND status = %s";
        }
        if (!empty($category)) {
            $total_query .= " AND category = %s";
        }
        if (!empty($search)) {
            $total_query .= " AND (title LIKE %s OR brand LIKE %s OR model LIKE %s)";
        }
        if (!empty($location)) {
            $total_query .= " AND location LIKE %s";
        }
        if ($min_price !== null) {
            $total_query .= " AND price >= %f AND currency = 'TRY'";
        }
        if ($max_price !== null) {
            $total_query .= " AND price <= %f AND currency = 'TRY'";
        }
        
        $total = $wpdb->get_var($wpdb->prepare($total_query, $params));
        
        // Sıralama
        $allowed_orderby = array('created_at', 'price', 'title', 'updated_at');
        if (!in_array($orderby, $allowed_orderby)) {
            $orderby = 'created_at';
        }
        $allowed_order = array('ASC', 'DESC');
        if (!in_array($order, $allowed_order)) {
            $order = 'DESC';
        }
        
        $query .= " ORDER BY $orderby $order";
        
        // Sayfalama
        $offset = ($page - 1) * $per_page;
        $query .= " LIMIT %d OFFSET %d";
        $params[] = $per_page;
        $params[] = $offset;
        
        // Sorguyu çalıştır
        $listings = $wpdb->get_results($wpdb->prepare($query, $params), ARRAY_A);
        
        // İlanları formatlı sonuç olarak döndür
        $formatted_listings = array_map(array($this, 'format_listing'), $listings);
        
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $formatted_listings,
            'pagination' => array(
                'total' => intval($total),
                'pages' => ceil($total / $per_page),
                'current_page' => intval($page),
                'per_page' => intval($per_page)
            )
        ), 200);
    }
    
    /**
     * Tek bir ilanı getir
     */
    public function get_listing($request) {
        global $wpdb;
        
        $id = intval($request['id']);
        $table_name = $wpdb->prefix . 'amator_ilanlar';
        
        $listing = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id),
            ARRAY_A
        );
        
        if (!$listing) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'İlan bulunamadı'
            ), 404);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $this->format_listing($listing)
        ), 200);
    }
    
    /**
     * Yeni ilan oluştur
     */
    public function create_listing($request) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        $params = $request->get_json_params();
        
        // Gerekli alanları kontrol et (satıcı bilgileri otomatik doldurulacak)
        $required_fields = array('title', 'category', 'brand', 'model', 'condition', 'price', 'description');
        foreach ($required_fields as $field) {
            if (empty($params[$field])) {
                return new WP_REST_Response(array(
                    'success' => false,
                    'message' => "'{$field}' alanı gereklidir"
                ), 400);
            }
        }
        
        // Kategori değeri kontrol et
        $valid_categories = array('transceiver', 'antenna', 'amplifier', 'accessory', 'other');
        if (!in_array($params['category'], $valid_categories)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Geçersiz kategori'
            ), 400);
        }
        
        // Durumu kontrol et
        $valid_conditions = array('Sıfır', 'Kullanılmış', 'Arızalı', 'El Yapımı');
        if (!in_array($params['condition'], $valid_conditions)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Geçersiz durum'
            ), 400);
        }
        
        $table_name = $wpdb->prefix . 'amator_ilanlar';

        // Kullanıcı profilinden eksik satıcı alanlarını tamamla
        $profile_name = null; $profile_phone = null; $profile_location = null; $profile_email = null;
        $users_table = $wpdb->prefix . 'amator_bitlik_kullanıcılar';
        $db_user = $wpdb->get_row($wpdb->prepare("SELECT * FROM $users_table WHERE user_id = %d", $user_id), ARRAY_A);
        $profile_callsign = null;
        if ($db_user) {
            $profile_name = $db_user['name'] ?? null;
            $profile_phone = $db_user['phone'] ?? null;
            $profile_location = $db_user['location'] ?? null;
            $profile_callsign = $db_user['callsign'] ?? null;
        }
        $current_user = get_user_by('id', $user_id);
        if ($current_user) {
            $profile_email = $current_user->user_email;
        }

        // Satıcı bilgilerini daima arka plandan doldur (istemci gönderse bile override)
        $insert_data = array(
            'user_id' => $user_id,
            'title' => sanitize_text_field($params['title']),
            'category' => sanitize_text_field($params['category']),
            'brand' => sanitize_text_field($params['brand']),
            'model' => sanitize_text_field($params['model']),
            'condition' => sanitize_text_field($params['condition']),
            'price' => floatval($params['price']),
            'currency' => sanitize_text_field($params['currency'] ?? 'TRY'),
            'description' => sanitize_textarea_field($params['description']),
            'location' => sanitize_text_field($profile_location ?? ''),
            'seller_name' => sanitize_text_field($profile_name ?? ''),
            'seller_email' => sanitize_email($profile_email ?? ''),
            'seller_phone' => sanitize_text_field($profile_phone ?? ''),
            'status' => 'pending',
            'created_at' => current_time('mysql')
        );

        // Çağrı işaretini (callsign) de profilden al ve ekle (tablo sütunu mevcutsa)
        if ($profile_callsign !== null) {
            $insert_data['callsign'] = strtoupper(trim($profile_callsign));
        }
        
        $result = $wpdb->insert($table_name, $insert_data);
        
        if (!$result) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'İlan oluşturulurken hata oluştu'
            ), 500);
        }
        
        $listing_id = $wpdb->insert_id;
        
        // Oluşturulan ilanı döndür
        $new_listing = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $listing_id),
            ARRAY_A
        );
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'İlan başarıyla oluşturuldu',
            'data' => $this->format_listing($new_listing)
        ), 201);
    }
    
    /**
     * İlanı güncelle
     */
    public function update_listing($request) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        $id = intval($request['id']);
        $table_name = $wpdb->prefix . 'amator_ilanlar';
        
        // İlanın bulunup bulunmadığını ve kullanıcıya ait olup olmadığını kontrol et
        $listing = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id),
            ARRAY_A
        );
        
        if (!$listing) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'İlan bulunamadı'
            ), 404);
        }
        
        if ($listing['user_id'] != $user_id) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Bu ilanı düzenleme yetkiniz yok'
            ), 403);
        }
        
        $params = $request->get_json_params();
        $update_data = array();
        
        // Güncellenebilir alanlar
        $updatable_fields = array('title', 'category', 'brand', 'model', 'condition', 'price', 'currency', 'description', 'location', 'seller_name', 'seller_email', 'seller_phone');
        
        foreach ($updatable_fields as $field) {
            if (isset($params[$field])) {
                if ($field === 'price') {
                    $update_data[$field] = floatval($params[$field]);
                } elseif ($field === 'description') {
                    $update_data[$field] = sanitize_textarea_field($params[$field]);
                } elseif ($field === 'seller_email') {
                    $update_data[$field] = sanitize_email($params[$field]);
                } else {
                    $update_data[$field] = sanitize_text_field($params[$field]);
                }
            }
        }
        
        if (empty($update_data)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Güncellenecek alan bulunamadı'
            ), 400);
        }
        
        $update_data['updated_at'] = current_time('mysql');
        
        $result = $wpdb->update($table_name, $update_data, array('id' => $id));
        
        if ($result === false) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'İlan güncellenirken hata oluştu'
            ), 500);
        }
        
        // Güncellenmiş ilanı döndür
        $updated_listing = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id),
            ARRAY_A
        );
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'İlan başarıyla güncellendi',
            'data' => $this->format_listing($updated_listing)
        ), 200);
    }
    
    /**
     * İlanı sil
     */
    public function delete_listing($request) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        $id = intval($request['id']);
        $table_name = $wpdb->prefix . 'amator_ilanlar';
        
        // İlanın bulunup bulunmadığını ve kullanıcıya ait olup olmadığını kontrol et
        $listing = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $id),
            ARRAY_A
        );
        
        if (!$listing) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'İlan bulunamadı'
            ), 404);
        }
        
        if ($listing['user_id'] != $user_id) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Bu ilanı silme yetkiniz yok'
            ), 403);
        }
        
        $result = $wpdb->delete($table_name, array('id' => $id));
        
        if (!$result) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'İlan silinirken hata oluştu'
            ), 500);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'İlan başarıyla silindi'
        ), 200);
    }
    
    /**
     * Kategorileri listele
     */
    public function get_categories() {
        $categories = array(
            array('id' => 'transceiver', 'name' => 'Transceiver', 'name_tr' => 'Verici-Alıcı'),
            array('id' => 'antenna', 'name' => 'Antenna', 'name_tr' => 'Anten'),
            array('id' => 'amplifier', 'name' => 'Amplifier', 'name_tr' => 'Yükselteç'),
            array('id' => 'accessory', 'name' => 'Accessory', 'name_tr' => 'Aksesuar'),
            array('id' => 'other', 'name' => 'Other', 'name_tr' => 'Diğer')
        );
        
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $categories
        ), 200);
    }
    
    /**
     * Şehirleri listele
     */
    public function get_cities() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'amator_bitlik_sehirler';
        
        // Tablo varsa şehirleri döndür
        $cities = $wpdb->get_results("SELECT il_adi as name FROM $table_name ORDER BY il_adi ASC", ARRAY_A);
        
        if (!$cities) {
            $cities = array();
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $cities
        ), 200);
    }
    
    /**
     * Kullanıcı profil bilgisini getir
     */
    public function get_user_profile() {
        $user_id = get_current_user_id();
        $current_user = get_user_by('id', $user_id);
        
        global $wpdb;
        $users_table = $wpdb->prefix . 'amator_bitlik_kullanıcılar';
        
        $db_user = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $users_table WHERE user_id = %d", $user_id),
            ARRAY_A
        );
        
        $profile = array(
            'user_id' => $user_id,
            'username' => $current_user->user_login,
            'email' => $current_user->user_email,
            'name' => $db_user ? $db_user['name'] : trim($current_user->first_name . ' ' . $current_user->last_name),
            'callsign' => $db_user ? $db_user['callsign'] : '',
            'phone' => $db_user ? $db_user['phone'] : '',
            'location' => $db_user ? $db_user['location'] : ''
        );
        
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $profile
        ), 200);
    }
    
    /**
     * Kullanıcı profilini güncelle
     */
    public function update_user_profile($request) {
        $user_id = get_current_user_id();
        $params = $request->get_json_params();
        
        global $wpdb;
        $users_table = $wpdb->prefix . 'amator_bitlik_kullanıcılar';
        
        $update_data = array();
        
        if (isset($params['name'])) {
            $update_data['name'] = sanitize_text_field($params['name']);
        }
        if (isset($params['callsign'])) {
            $update_data['callsign'] = strtoupper(str_replace(' ', '', sanitize_text_field($params['callsign'])));
        }
        if (isset($params['phone'])) {
            $update_data['phone'] = sanitize_text_field($params['phone']);
        }
        if (isset($params['location'])) {
            $update_data['location'] = sanitize_text_field($params['location']);
        }
        
        if (empty($update_data)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Güncellenecek alan bulunamadı'
            ), 400);
        }
        
        // Kullanıcı kaydı var mı
        $existing = $wpdb->get_var(
            $wpdb->prepare("SELECT id FROM $users_table WHERE user_id = %d", $user_id)
        );
        
        if ($existing) {
            $wpdb->update($users_table, $update_data, array('user_id' => $user_id));
        } else {
            $update_data['user_id'] = $user_id;
            $update_data['email'] = get_user_by('id', $user_id)->user_email;
            $wpdb->insert($users_table, $update_data);
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Profil başarıyla güncellendi'
        ), 200);
    }
    
    /**
     * Kullanıcının ilanlarını listele
     */
    public function get_user_listings($request) {
        global $wpdb;
        
        $user_id = get_current_user_id();
        $table_name = $wpdb->prefix . 'amator_ilanlar';
        
        $page = $request->get_param('page') ?? 1;
        $per_page = $request->get_param('per_page') ?? 20;
        
        $query = "SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at DESC";
        
        // Toplam sayı
        $total = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE user_id = %d", $user_id)
        );
        
        // Sayfalama
        $offset = ($page - 1) * $per_page;
        $query .= " LIMIT %d OFFSET %d";
        
        $listings = $wpdb->get_results(
            $wpdb->prepare($query, $user_id, $per_page, $offset),
            ARRAY_A
        );
        
        $formatted_listings = array_map(array($this, 'format_listing'), $listings);
        
        return new WP_REST_Response(array(
            'success' => true,
            'data' => $formatted_listings,
            'pagination' => array(
                'total' => intval($total),
                'pages' => ceil($total / $per_page),
                'current_page' => intval($page),
                'per_page' => intval($per_page)
            )
        ), 200);
    }
    
    /**
     * Authentication routes'larını kaydet
     */
    public function register_auth_routes() {
        // POST: Giriş yap (username + password)
        register_rest_route($this->namespace, '/auth/login', array(
            'methods' => 'POST',
            'callback' => array($this, 'login_user'),
            'permission_callback' => '__return_true',
            'args' => array(
                'username' => array(
                    'description' => 'Kullanıcı adı',
                    'type' => 'string',
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field'
                ),
                'password' => array(
                    'description' => 'Şifre',
                    'type' => 'string',
                    'required' => true
                )
            )
        ));
        
        // POST: Çıkış yap
        register_rest_route($this->namespace, '/auth/logout', array(
            'methods' => 'POST',
            'callback' => array($this, 'logout_user'),
            'permission_callback' => array($this, 'check_user_permission')
        ));

        // GET: Yeni REST nonce üret (login'den SONRA çağrılmalı)
        register_rest_route($this->namespace, '/auth/nonce', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_rest_nonce'),
            // Not: Cookie doğrulamasını callback içinde kendimiz yapacağız
            'permission_callback' => '__return_true'
        ));
    }
    
    /**
     * Kullanıcı giriş yap
     */
    public function login_user($request) {
        $username = sanitize_text_field($request->get_param('username'));
        $password = $request->get_param('password');
        
        if (empty($username) || empty($password)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Kullanıcı adı ve şifre gerekli'
            ), 400);
        }
        
        $user = wp_authenticate($username, $password);
        
        if (is_wp_error($user)) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Kullanıcı adı veya şifre hatalı'
            ), 401);
        }
        
        // Giriş yap
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, true);
        // REST istekleri için nonce üret
        $nonce = wp_create_nonce('wp_rest');
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Başarıyla giriş yapıldı',
            'user' => array(
                'id' => $user->ID,
                'username' => $user->user_login,
                'email' => $user->user_email,
                'name' => $user->display_name
            ),
            'nonce' => $nonce
        ), 200);
    }
    
    /**
     * Kullanıcı çıkış yap
     */
    public function logout_user($request) {
        wp_logout();
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Başarıyla çıkış yapıldı'
        ), 200);
    }

    /**
     * Geçerli oturum için REST nonce döndür
     */
    public function get_rest_nonce($request) {
        // WP REST, cookie auth için X-WP-Nonce beklediğinden
        // burada çerezi manuel doğrulayıp kullanıcıyı ayarlıyoruz.
        if (defined('LOGGED_IN_COOKIE') && isset($_COOKIE[LOGGED_IN_COOKIE])) {
            $cookie_val = $_COOKIE[LOGGED_IN_COOKIE];
        } else {
            // Bazı kurulumlarda cookie adı dinamik hash içerir; hepsini dolaş
            $cookie_val = null;
            foreach ($_COOKIE as $name => $val) {
                if (strpos($name, 'wordpress_logged_in_') === 0) {
                    $cookie_val = $val;
                    break;
                }
            }
        }

        if (!$cookie_val) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Oturum çerezi bulunamadı'
            ), 401);
        }

        $user_id = wp_validate_auth_cookie($cookie_val, 'logged_in');
        if (!$user_id) {
            return new WP_REST_Response(array(
                'success' => false,
                'message' => 'Geçersiz oturum'
            ), 401);
        }

        wp_set_current_user($user_id);
        $nonce = wp_create_nonce('wp_rest');
        return new WP_REST_Response(array(
            'success' => true,
            'nonce' => $nonce
        ), 200);
    }

    /**
     * CORS başlıklarını ekle ve preflight (OPTIONS) isteklerini yönet
     */
    public function add_cors_headers($served, $result, $request, $server) {
        // Sadece bizim namespace için uygula
        $route = is_object($request) ? $request->get_route() : '';
        if (strpos($route, '/' . $this->namespace . '/') !== 0 && $route !== '/' . $this->namespace) {
            return $served;
        }

        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';

        // Site kökenini hesapla
        $home_url = get_home_url();
        $home_scheme = parse_url($home_url, PHP_URL_SCHEME);
        $home_host = parse_url($home_url, PHP_URL_HOST);
        $site_origin = $home_scheme . '://' . $home_host;

        // İzin verilen origin listesi (site origin + tanımlı whitelist)
        $allowed_origins = array($site_origin);
        if (defined('ATIV_ALLOWED_CORS_ORIGINS') && is_array(ATIV_ALLOWED_CORS_ORIGINS)) {
            $allowed_origins = array_merge($allowed_origins, ATIV_ALLOWED_CORS_ORIGINS);
        }

        // Origin varsa ve whitelist'te ise onu yansıt, yoksa site origin'i yansıt
        if ($origin && in_array($origin, $allowed_origins, true)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        } else {
            header('Access-Control-Allow-Origin: ' . $site_origin);
        }

        header('Vary: Origin');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Authorization, X-WP-Nonce, Content-Type');
        header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
        header('Access-Control-Expose-Headers: X-WP-Nonce');

        // Preflight isteği ise erken dön
        if ('OPTIONS' === $_SERVER['REQUEST_METHOD']) {
            status_header(200);
            echo '';
            return true;
        }

        return $served;
    }

    /**
     * WordPress çerezleri için SameSite=None zorla (cross-site XHR için gereklidir)
     */
    public function force_cookie_samesite_none($samesite, $cookies) {
        // Güvenli bağlantı şart (tarayıcılar SameSite=None için Secure ister)
        if (!is_ssl()) {
            return $samesite; // http ise dokunma
        }
        return 'None';
    }
    
    /**
     * İlanı formatlı sonuç olarak döndür (görsel URL'leri ekle vb.)
     */
    private function format_listing($listing) {
        // Görselleri işle
        $images = array();
        if (!empty($listing['images'])) {
            $image_files = json_decode($listing['images'], true);
            if (is_array($image_files)) {
                foreach ($image_files as $image_file) {
                    $images[] = ATIV_UPLOAD_URL . $listing['id'] . '/' . $image_file;
                }
            }
        }
        
        // Videoyu işle
        $video = null;
        if (!empty($listing['video'])) {
            $video = $listing['video'];
        }
        
        return array(
            'id' => intval($listing['id']),
            'title' => $listing['title'],
            'category' => $listing['category'],
            'brand' => $listing['brand'],
            'model' => $listing['model'],
            'condition' => $listing['condition'],
            'price' => floatval($listing['price']),
            'currency' => $listing['currency'],
            'old_price' => $listing['old_price'] ? floatval($listing['old_price']) : null,
            'description' => $listing['description'],
            'images' => $images,
            'featured_image_index' => intval($listing['featured_image_index']),
            'video' => $video,
            'callsign' => $listing['callsign'],
            'seller_name' => $listing['seller_name'],
            'location' => $listing['location'],
            'seller_email' => $listing['seller_email'],
            'seller_phone' => $listing['seller_phone'],
            'status' => $listing['status'],
            'created_at' => $listing['created_at'],
            'updated_at' => $listing['updated_at'],
            'user_id' => intval($listing['user_id'])
        );
    }

    /**
     * REST argümanları için güvenli float sanitizer (WP 3 arg ile çağırır)
     */
    public function sanitize_float($value, $request = null, $param = null) {
        return is_numeric($value) ? floatval($value) : 0.0;
    }
}

// REST API'yi başlat
add_action('plugins_loaded', function() {
    new ATIV_REST_API();
});
?>
