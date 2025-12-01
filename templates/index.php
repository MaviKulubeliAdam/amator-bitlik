<script>pageType = 'gallery';</script>
<div class="container">
  <header class="header">
    <h1>Amatör Telsiz İlan Vitrini</h1>
    <p>Kaliteli ekipmanları keşfedin</p>
  </header>
  <div class="controls">
    <div class="search-box"><input type="text" id="searchInput" placeholder="İlan ara... (başlık, çağrı işareti, açıklama)">
    </div>
    <div class="filter-group"><label class="filter-label">Kategori</label>
      <div class="dropdown-filter" id="categoryDropdown"><button type="button" class="filter-select dropdown-button" id="categoryButton" aria-label="Kategori filtresi"> <span id="categoryButtonText">Tüm Kategoriler</span> <span class="dropdown-arrow">▼</span> </button>
        <div class="dropdown-menu" id="categoryMenu">
          <div class="dropdown-options" id="categoryOptions">
            <div class="dropdown-option selected" data-value="all">
              Tüm Kategoriler
            </div>
            <div class="dropdown-option" data-value="transceiver">
              Telsiz
            </div>
            <div class="dropdown-option" data-value="antenna">
              Anten
            </div>
            <div class="dropdown-option" data-value="amplifier">
              Amplifikatör
            </div>
            <div class="dropdown-option" data-value="accessory">
              Aksesuar
            </div>
            <div class="dropdown-option" data-value="other">
              Diğer
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="filter-group"><label class="filter-label">Durum</label>
      <div class="dropdown-filter" id="conditionDropdown"><button type="button" class="filter-select dropdown-button" id="conditionButton" aria-label="Durum filtresi"> <span id="conditionButtonText">Tüm Durumlar</span> <span class="dropdown-arrow">▼</span> </button>
        <div class="dropdown-menu" id="conditionMenu">
          <div class="dropdown-options" id="conditionOptions">
            <div class="dropdown-option selected" data-value="all">
              Tüm Durumlar
            </div>
            <div class="dropdown-option" data-value="Sıfır">
              Sıfır
            </div>
            <div class="dropdown-option" data-value="Kullanılmış">
              Kullanılmış
            </div>
            <div class="dropdown-option" data-value="Arızalı">
              Arızalı
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="filter-group"><label class="filter-label">Marka</label>
      <div class="dropdown-filter" id="brandDropdown"><button type="button" class="filter-select dropdown-button" id="brandButton" aria-label="Marka filtresi"> <span id="brandButtonText">Tüm Markalar</span> <span class="dropdown-arrow">▼</span> </button>
        <div class="dropdown-menu" id="brandMenu">
          <div class="dropdown-search"><input type="text" id="brandSearchInput" placeholder="Marka ara...">
          </div>
          <div class="dropdown-options" id="brandOptions">
            <div class="dropdown-option selected" data-value="all">
              Tüm Markalar
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="filter-group"><label class="filter-label">Konum</label>
      <div class="dropdown-filter" id="locationDropdown"><button type="button" class="filter-select dropdown-button" id="locationButton" aria-label="Konum filtresi"> <span id="locationButtonText">Tüm Konumlar</span> <span class="dropdown-arrow">▼</span> </button>
        <div class="dropdown-menu" id="locationMenu">
          <div class="dropdown-search"><input type="text" id="locationSearchInput" placeholder="Konum ara...">
          </div>
          <div class="dropdown-options" id="locationOptions">
            <div class="dropdown-option selected" data-value="all">
              Tüm Konumlar
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="filter-group"><label class="filter-label">Fiyat Aralığı</label>
      <div class="dropdown-filter" id="priceDropdown"><button type="button" class="filter-select dropdown-button" id="priceButton" aria-label="Fiyat aralığı filtresi"> <span id="priceButtonText">Tüm Fiyatlar</span> <span class="dropdown-arrow">▼</span> </button>
        <div class="dropdown-menu" id="priceMenu">
          <div class="dropdown-options" id="priceOptions">
            <div class="dropdown-option selected" data-value="all">
              Tüm Fiyatlar
            </div>
            <div class="dropdown-option" data-value="0-1000">
              0 - 1.000 ₺
            </div>
            <div class="dropdown-option" data-value="1000-5000">
              1.000 - 5.000 ₺
            </div>
            <div class="dropdown-option" data-value="5000-15000">
              5.000 - 15.000 ₺
            </div>
            <div class="dropdown-option" data-value="15000-30000">
              15.000 - 30.000 ₺
            </div>
            <div class="dropdown-option" data-value="30000-999999">
              30.000 ₺ +
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="filter-group"><label class="filter-label">Sıralama</label>
      <div class="dropdown-filter" id="sortDropdown">
        <button type="button" class="filter-select dropdown-button" id="sortButton" aria-label="Sıralama filtresi">
          <span id="sortButtonText">Yeniden Eskiye</span>
          <span class="dropdown-arrow">▼</span>
        </button>
        <div class="dropdown-menu" id="sortMenu">
          <div class="dropdown-options" id="sortOptions">
            <div class="dropdown-option selected" data-value="newest">
              Yeniden Eskiye
            </div>
            <div class="dropdown-option" data-value="oldest">
              Eskiden Yeniye
            </div>
            <div class="dropdown-option" data-value="price_asc">
              Ucuzdan Pahalıya
            </div>
            <div class="dropdown-option" data-value="price_desc">
              Pahalıdan Ucuza
            </div>
            <div class="dropdown-option" data-value="title_asc">
              A'dan Z'ye
            </div>
            <div class="dropdown-option" data-value="title_desc">
              Z'den A'ya
            </div>
          </div>
        </div>
      </div>
    </div>

<?php 
// Sadece oturum açmış kullanıcılar butonu görebilir
if (is_user_logged_in()): 
?>
<button id="addListingBtn" class="add-listing-btn">+ Yeni İlan</button>
<?php endif; ?>

  </div>
  <div class="listings-wrapper">
    <div id="listingsContainer" class="listings-container">
      <div id="listingsGrid" class="listings-grid"></div>
      <div id="paginationContainer" class="pagination-container"></div>
      <div id="noResults" class="no-results" style="display: none;">
        Sonuç bulunamadı. Lütfen farklı bir arama deneyin.
      </div>
    </div>
  </div>
</div>
<?php include ATIV_PLUGIN_PATH . 'templates/partial-modal.php'; ?>
<!-- Kullanım Sözleşmesi / KVKK Floating Button -->
<button id="termsFloatingBtn" class="terms-floating-btn" aria-label="Kullanım Sözleşmesi ve KVKK Aydınlatma Metni">
  📜 Kullanım & KVKK
  <small>Okuyun ve onaylayın</small>
</button>