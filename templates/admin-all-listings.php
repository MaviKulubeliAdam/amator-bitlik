<?php
        // Silme işlemi - AJAX ile yapılacak, bu kod kaldırıldı
        
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
        // NOT: İleride performans için price_in_tl kolonu eklenebilir ve trigger ile güncellenebilir
        // Böylece SQL SUM() kullanılarak direkt hesaplanabilir
        
        // Cache kontrolü - 5 dakika cache
        $cache_key = 'ativ_admin_stats_' . md5($table_name);
        $cached_data = get_transient($cache_key);
        
        if (false !== $cached_data && isset($cached_data['total_amount'])) {
            $total_amount = $cached_data['total_amount'];
        } else {
            // esc_sql kullanarak extra güvenlik (wpdb->prefix zaten güvenli olsa da)
            $safe_table = esc_sql($table_name);
            $all_listings = $wpdb->get_results("SELECT price, currency FROM `{$safe_table}` WHERE status != 'rejected'", ARRAY_A);
            $total_amount = 0;
            foreach ($all_listings as $listing) {
                $total_amount += $this->convert_to_tl($listing['price'], $listing['currency']);
            }
            // Cache kaydet (5 dakika)
            set_transient($cache_key, array('total_amount' => $total_amount), 5 * MINUTE_IN_SECONDS);
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
                                            <button class="ativ-btn ativ-btn-delete" style="font-size: 11px; padding: 4px 8px;" onclick="openDeleteModal(<?php echo $listing['id']; ?>, '<?php echo esc_js($listing['title']); ?>')">🗑️ Sil</button>
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
        
        <!-- Silme Modal -->
        <div id="deleteModal" class="admin-edit-modal">
            <div class="admin-edit-modal-content" style="max-width: 500px;">
                <div class="admin-edit-modal-header">
                    <h2>🗑️ İlan Sil</h2>
                    <button class="admin-edit-modal-close" onclick="closeDeleteModal()">×</button>
                </div>
                <form id="deleteForm" onsubmit="submitDeleteForm(event)">
                    <input type="hidden" id="deleteListingId" name="id">
                    
                    <div style="margin-bottom: 20px; padding: 15px; background: #fff3cd; border-left: 3px solid #ffc107; border-radius: 4px;">
                        <p style="margin: 0; color: #856404; font-size: 14px;">
                            <strong id="deleteListingTitle"></strong> başlıklı ilanı silmek üzeresiniz. Bu işlem geri alınamaz.
                        </p>
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; color: #333;">Silme Nedeni (Kullanıcıya gönderilecek)</label>
                        <textarea id="deletionReason" name="deletion_reason" placeholder="Lütfen bu ilanı neden sildiğinizi açıklayın..." rows="5" required style="width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; font-family: inherit; transition: all 0.2s ease;" onblur="this.style.borderColor='#ddd'" onfocus="this.style.borderColor='#dc3545'; this.style.boxShadow='0 0 0 4px rgba(220, 53, 69, 0.1)'"></textarea>
                    </div>
                    
                    <div class="ativ-form-buttons" style="display: flex; gap: 10px; margin-top: 20px; padding-top: 20px; border-top: 2px solid #f0f0f0;">
                        <button type="submit" style="flex: 1; padding: 12px 20px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;" title="İlanı sil ve kullanıcıya bildir">🗑️ Sil</button>
                        <button type="button" onclick="closeDeleteModal()" style="flex: 1; padding: 12px 20px; background: #f0f0f0; color: #333; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s ease;">İptal</button>
                    </div>
                </form>
            </div>
        </div>
        
        <script>
        // GÜVENLİK: Admin nonce'u (esc_js ile XSS koruması)
        var ativAdminNonce = '<?php echo esc_js(wp_create_nonce('ativ_admin_nonce')); ?>';
        
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
                body: 'action=ativ_ajax&action_type=ativ_get_listing_for_admin&id=' + id + '&_wpnonce=' + ativAdminNonce
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
                    _wpnonce: ativAdminNonce,
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
                    _wpnonce: ativAdminNonce,
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
        
        function openDeleteModal(id, title) {
            const modal = document.getElementById('deleteModal');
            document.getElementById('deleteListingId').value = id;
            document.getElementById('deleteListingTitle').textContent = title;
            document.getElementById('deletionReason').value = '';
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
        
        function submitDeleteForm(e) {
            e.preventDefault();
            
            const id = document.getElementById('deleteListingId').value;
            const reason = document.getElementById('deletionReason').value;
            
            if (!reason.trim()) {
                alert('❌ Lütfen silme nedenini yazınız');
                return;
            }
            
            if (!confirm('Bu ilanı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz.')) {
                return;
            }
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: new URLSearchParams({
                    action: 'ativ_ajax',
                    action_type: 'ativ_delete_listing_admin',
                    _wpnonce: ativAdminNonce,
                    id: id,
                    deletion_reason: reason
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    closeDeleteModal();
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
                    _wpnonce: ativAdminNonce,
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
