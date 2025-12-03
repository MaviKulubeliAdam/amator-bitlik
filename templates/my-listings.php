<div id="ativ-container" class="container my-listings-page">
  <script>pageType = 'my-listings';</script>
  <?php
  // Telefon numarasını formatla (detay gösterimi için: +90 548 222 99 89)
  function format_phone_for_display($phone) {
    if (empty($phone)) return '';
    
    // Parse phone - ülke kodu ve numarayı ayır
    $phone = trim($phone);
    $dialCode = '';
    $number = '';
    
    // + ile başlıyorsa ülke kodunu ayır
    if (preg_match('/^(\+\d+)\s*(.*)$/', $phone, $matches)) {
      $dialCode = $matches[1];
      $number = preg_replace('/\D/', '', $matches[2]); // Sadece rakamlar
    } else {
      // Ülke kodu yoksa tüm rakamları al
      $number = preg_replace('/\D/', '', $phone);
      $dialCode = '+90'; // Varsayılan Türkiye
    }
    
    // Numarayı formatla (5482229989 -> 548 222 99 89)
    $len = strlen($number);
    if ($len <= 3) {
      $formatted = $number;
    } else if ($len <= 6) {
      $formatted = substr($number, 0, 3) . ' ' . substr($number, 3);
    } else if ($len <= 8) {
      $formatted = substr($number, 0, 3) . ' ' . substr($number, 3, 3) . ' ' . substr($number, 6);
    } else {
      $formatted = substr($number, 0, 3) . ' ' . substr($number, 3, 3) . ' ' . substr($number, 6, 2) . ' ' . substr($number, 8);
    }
    
    return $dialCode . ' ' . $formatted;
  }
  ?>
  <header class="header">
    <h1>Benim İlanlarım</h1>
    <p>Kendi yayınladığınız ilanların listesi</p>
  </header>

  <div class="controls">
    <!-- Filtreleri sola al -->
    <div style="display: flex; gap: 12px; flex-wrap: wrap; align-items: center; flex: 1;">
      
      <!-- Fiyata göre sıralama -->
      <div class="dropdown-filter" id="priceSortDropdown">
        <button type="button" class="filter-select dropdown-button" id="priceSortButton" aria-label="Fiyat filtresi">
          <span id="priceSortButtonText">💰 Fiyat</span>
          <span class="dropdown-arrow">▼</span>
        </button>
        <div class="dropdown-menu" id="priceSortMenu">
          <div class="dropdown-options" id="priceSortOptions">
            <div class="dropdown-option selected" data-value="">💰 Fiyat</div>
            <div class="dropdown-option" data-value="price-asc">Fiyat: Düşükten Yükseğe</div>
            <div class="dropdown-option" data-value="price-desc">Fiyat: Yüksekten Düşüğe</div>
          </div>
        </div>
      </div>
      
      <!-- Onay durumuna göre filtreleme -->
      <div class="dropdown-filter" id="statusFilterDropdown">
        <button type="button" class="filter-select dropdown-button" id="statusFilterButton" aria-label="Durum filtresi">
          <span id="statusFilterButtonText">📋 Tüm Durumlar</span>
          <span class="dropdown-arrow">▼</span>
        </button>
        <div class="dropdown-menu" id="statusFilterMenu">
          <div class="dropdown-options" id="statusFilterOptions">
            <div class="dropdown-option selected" data-value="">📋 Tüm Durumlar</div>
            <div class="dropdown-option" data-value="approved">✅ Onaylı</div>
            <div class="dropdown-option" data-value="pending">⏳ Onay Bekleyen</div>
            <div class="dropdown-option" data-value="rejected">❌ Reddedilen</div>
          </div>
        </div>
      </div>
      
      <!-- Tarihe göre sıralama -->
      <div class="dropdown-filter" id="dateSortDropdown">
        <button type="button" class="filter-select dropdown-button" id="dateSortButton" aria-label="Tarih filtresi">
          <span id="dateSortButtonText">📅 Tarih</span>
          <span class="dropdown-arrow">▼</span>
        </button>
        <div class="dropdown-menu" id="dateSortMenu">
          <div class="dropdown-options" id="dateSortOptions">
            <div class="dropdown-option selected" data-value="">📅 Tarih</div>
            <div class="dropdown-option" data-value="newest">Yeniden Eskiye</div>
            <div class="dropdown-option" data-value="oldest">Eskiden Yeniye</div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Yeni İlan butonu sağda -->
    <?php if (is_user_logged_in()): ?>
      <button id="addListingBtn" class="add-listing-btn">+ Yeni İlan</button>
    <?php endif; ?>
  </div>

  <div class="listings-wrapper">
    <div class="listings-container">
      <div id="myListingsGrid" class="listings-list">
        <!-- Debug: Toplam <?php echo count($my_listings); ?> ilan bulundu -->
        <?php if (empty($my_listings)): ?>
          <div class="no-results">Henüz ilanınız yok.</div>
        <?php else: ?>
          <?php foreach ($my_listings as $listing):
            $image_url = '';
            if (!empty($listing['images']) && is_array($listing['images'])) {
              $featured_index = intval($listing['featured_image_index'] ?? 0);
              $featured_img = $listing['images'][$featured_index] ?? $listing['images'][0] ?? null;
              if ($featured_img && !empty($featured_img['data'])) {
                $image_url = $featured_img['data'];
              } elseif ($featured_img && !empty($featured_img['name'])) {
                $image_url = ATIV_UPLOAD_URL . $listing['id'] . '/' . $featured_img['name'];
              }
            }
          ?>
          <div class="listing-row-wrapper" style="display: flex; flex-direction: column;">
            <div class="listing-row" data-listing-id="<?php echo esc_attr($listing['id']); ?>" style="position: relative; border: 2px solid <?php echo ($listing['status'] === 'rejected' ? '#dc3545' : ($listing['status'] === 'pending' ? '#ffc107' : '#28a745')); ?>; border-radius: 4px; display: flex; flex-wrap: wrap; cursor: pointer;" onclick="toggleListingDetails(this.querySelector('.listing-row-title'))">
              <div class="listing-row-image">
                <?php if ($image_url): ?>
                  <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($listing['title']); ?>">
                <?php else: ?>
                  <div class="listing-row-image-fallback"><?php echo esc_html($listing['emoji'] ?? '📻'); ?></div>
                <?php endif; ?>
              </div>
              <div class="listing-row-info" style="flex: 1; min-width: 0; overflow-wrap: break-word; word-wrap: break-word;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 5px;">
                  <h3 class="listing-row-title" style="cursor: pointer; margin: 0;"><?php echo esc_html($listing['title']); ?></h3>
                  <?php if ($listing['status'] === 'rejected'): ?>
                    <span style="background: #dc3545; color: white; font-size: 11px; padding: 4px 8px; border-radius: 12px; white-space: nowrap; font-weight: bold;">❌ Reddedildi</span>
                  <?php elseif ($listing['status'] === 'pending'): ?>
                    <span style="background: #ffc107; color: #333; font-size: 11px; padding: 4px 8px; border-radius: 12px; white-space: nowrap; font-weight: bold;">⏳ Beklemede</span>
                  <?php else: ?>
                    <span style="background: #28a745; color: white; font-size: 11px; padding: 4px 8px; border-radius: 12px; white-space: nowrap; font-weight: bold;">✅ Onaylı</span>
                  <?php endif; ?>
                </div>
                
                <!-- Red nedeni veya pending uyarısı -->
                <?php if ($listing['status'] === 'rejected'): ?>
                <div style="background: #ffebee; border-left: 3px solid #dc3545; padding: 10px; margin-bottom: 8px; border-radius: 2px; word-wrap: break-word; overflow-wrap: break-word;">
                  <div style="color: #721c24; font-size: 12px; word-wrap: break-word; overflow-wrap: break-word; white-space: normal;">
                    <strong>Red Nedeni:</strong> <?php echo esc_html($listing['rejection_reason'] ?? 'Neden belirtilmemiş'); ?>
                  </div>
                  <div style="background: #fff3cd; border-left: 2px solid #ff9800; padding: 8px; border-radius: 2px; margin-top: 8px; word-wrap: break-word; overflow-wrap: break-word;">
                    <div style="color: #856404; font-size: 12px;">
                      💡 <strong>İlanınızı düzenleyip tekrar gönderin.</strong> "Düzenle" butonuna tıklayarak değişiklikler yapabilirsiniz.
                    </div>
                  </div>
                </div>
                <?php elseif ($listing['status'] === 'pending'): ?>
                <div style="background: #fffbf0; border-left: 3px solid #ffc107; padding: 8px 10px; margin-bottom: 8px; border-radius: 2px; word-wrap: break-word; overflow-wrap: break-word;">
                  <div style="color: #856404; font-size: 12px;">
                    ⏳ <strong>Yönetici incelemesinde...</strong> İlanınızı düzenleyebilirsiniz.
                  </div>
                </div>
                <?php endif; ?>
                
                <p class="listing-row-category"><?php echo esc_html(getCategoryName($listing['category'])); ?> • <?php echo esc_html($listing['condition']); ?></p>
                <p class="listing-row-details"><?php echo esc_html($listing['brand']); ?> <?php echo esc_html($listing['model']); ?> • <?php echo esc_html($listing['callsign']); ?></p>
                <p class="listing-row-date">Yayınlanma: <?php echo esc_html(date_i18n('d.m.Y H:i', strtotime($listing['created_at']))); ?></p>
              </div>
              <div class="listing-row-price">
                <div class="price-amount"><?php echo esc_html($listing['price']); ?> <?php echo esc_html($listing['currency'] ?? 'TRY'); ?></div>
              </div>
              <div class="listing-row-actions">
                <?php if ($listing['status'] === 'rejected' || $listing['status'] === 'pending'): ?>
                  <button class="action-btn edit-btn" onclick="event.stopPropagation(); window.editMyListing(<?php echo intval($listing['id']); ?>)" title="Düzenle">✏️ Düzenle</button>
                <?php else: ?>
                  <button class="action-btn edit-btn" onclick="event.stopPropagation(); window.editListing(<?php echo intval($listing['id']); ?>)" title="Düzenle">✏️ Düzenle</button>
                <?php endif; ?>
                <button class="action-btn delete-btn" onclick="event.stopPropagation(); window.confirmDeleteListing(<?php echo intval($listing['id']); ?>)" title="Sil">🗑️ Sil</button>
              </div>
            </div>
            <div class="listing-row-details-expanded">
              <div class="listing-details-content">
                <div class="details-section">
                  <h4>Ürün Açıklaması</h4>
                  <p><?php echo nl2br(esc_html($listing['description'])); ?></p>
                </div>
                <div class="details-grid">
                <div class="detail-item">
                  <span class="detail-label">Kategori:</span>
                  <span class="detail-value"><?php echo esc_html(getCategoryName($listing['category'])); ?></span>
                </div>
                  <div class="detail-item">
                    <span class="detail-label">Durum:</span>
                    <span class="detail-value"><?php echo esc_html($listing['condition']); ?></span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Marka:</span>
                    <span class="detail-value"><?php echo esc_html($listing['brand']); ?></span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Model:</span>
                    <span class="detail-value"><?php echo esc_html($listing['model']); ?></span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Fiyat:</span>
                    <span class="detail-value"><?php echo esc_html($listing['price'] . ' ' . ($listing['currency'] ?? 'TRY')); ?></span>
                  </div>
                  <div class="detail-item">
                    <span class="detail-label">Konum:</span>
                    <span class="detail-value"><?php echo esc_html($listing['location']); ?></span>
                  </div>
                </div>
                <div class="details-section">
                  <h4>Satıcı Bilgileri</h4>
                  <div class="seller-info">
                    <p><strong><?php echo esc_html($listing['seller_name']); ?></strong></p>
                    <p>Çağrı İşareti: <?php echo esc_html($listing['callsign']); ?></p>
                    <p>E-posta: <?php echo esc_html($listing['seller_email']); ?></p>
                    <p>Telefon: <?php echo esc_html(format_phone_for_display($listing['seller_phone'])); ?></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
    <?php include ATIV_PLUGIN_PATH . 'templates/partial-modal.php'; ?>
    <!-- Kullanım Sözleşmesi / KVKK Floating Button -->
    <button id="termsFloatingBtn" class="terms-floating-btn" aria-label="Kullanım Sözleşmesi ve KVKK Aydınlatma Metni">
      📜 Kullanım & KVKK
      <small>Okuyun ve onaylayın</small>
    </button>
