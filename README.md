# Amatör Bitlik – İlan Vitrini Eklentisi

Amatör telsiz ekipman ilanlarını WordPress üzerinde kolayca yönetmek için geliştirilmiş ilan vitrini eklentisi. Çoklu para birimi, SMTP e-posta bildirimleri, yönetici akışları (onay/red/silme), kullanıcı arayüzü ve şablon yönetimi içerir.

## Özellikler
- Çoklu para birimi desteği ve TL otomatik dönüşüm
- SMTP ile güvenilir e-posta gönderimi (UTF-8 + base64)
- Kullanıcı bildirimleri: ilan gönderildi, onaylandı, reddedildi, silindi
- Yönetici bildirimleri: yeni ilan, reddedilen ilan güncellendi
- Kategori isimleri Türkçe ve emoji’li gösterim
- E-posta şablonları veritabanında tutularak özelleştirilebilir, varsayılanlara geri düşme
- “Benim İlanlarım” sayfasında durum rozetleri, red nedeni ve düzenleme akışı
- Yönetici panelinde ilan düzenleme, reddetme ve silme (silme nedeni modalı)

## Kurulum
1. WordPress kurulumunuzda eklenti klasörünü yükleyin.
2. Eklenti dosyası: `amateur-telsiz-ilan-vitrini.php`.
3. Yönetim panelinden eklentiyi etkinleştirin.
4. İlk etkinleştirmede gerekli tablolar ve varsayılan e-posta şablonları oluşturulur:
	 - `wp_amator_ilanlar`
	 - `wp_amator_telsiz_ayarlar`
	 - `wp_amator_telsiz_sablonlar`
	 - `wp_amator_telsiz_doviz_kurlari`

## Yapılandırma
- Yönetim › Ayarlar › Amatör Bitlik menüsünden SMTP bilgilerini girin:
	- `smtp_host`, `smtp_port`, `smtp_username`, `smtp_password`
	- Gönderen adı ve e-posta (`smtp_from_name`, `smtp_from_email`)
- “Test Mail” düğmesi ile SMTP yapılandırmasını doğrulayın.
- E-posta şablonlarını aynı sayfada düzenleyebilirsiniz. Şablon değişkenleri otomatik yer değiştirilir.

## Kategori İsimleri
Eklenti kategori anahtarlarını aşağıdaki Türkçe ve emoji’li karşılıklarla gösterir:
- `transceiver` → 📻 Telsiz
- `antenna` → 📡 Anten
- `amplifier` → ⚡ Amplifikatör
- `accessory` → 🔧 Aksesuar
- `other` → ❓ Diğer

## E-posta Şablonları ve Değişkenler
Şablon anahtarları ve ilgili değişkenler:
- `listing_submitted`: `{title}`, `{seller_name}`, `{category}`, `{listing_id}`
- `listing_approved`: `{title}`, `{seller_name}`, `{category}`, `{listing_id}`
- `listing_rejected`: `{title}`, `{seller_name}`, `{category}`, `{rejection_reason}`, `{admin_email}`, `{listing_id}`
- `listing_deleted`: `{title}`, `{seller_name}`, `{category}`, `{admin_email}`, `{listing_id}`
- `listing_deleted_by_admin`: `{title}`, `{seller_name}`, `{category}`, `{deletion_reason}`, `{admin_email}`, `{listing_id}`
- `admin_new_listing`: `{title}`, `{category}`, `{seller_name}`, `{seller_email}`, `{price}`, `{currency}`, `{listing_id}`
- `admin_listing_updated`: `{title}`, `{category}`, `{seller_name}`, `{seller_email}`, `{price}`, `{currency}`, `{listing_id}`

Notlar:
- Onay e-postasında ilan URL’si kullanılmaz; kullanıcı “Benim İlanlarım” sayfasından ilanı görür.
- Şablon gövdeleri DB’de özelleştirilebilir; bulunamayan durumda güvenli varsayılana düşer.
- Metinlerde kaçış (\\) sorunlarını önlemek için gönderim sırasında gerekli temizlemeler yapılır.

## Kullanıcı Arayüzü
- `templates/index.php`: Listeleme ana sayfası (arama/filtre vb.)
- `templates/my-listings.php`: Kullanıcının ilanları, durum rozetleri, red nedeni ve düzenleme/silme butonları.
- Red nedeni, “Red Nedeni: çünkü …” biçiminde tek satır hizalı gösterilir.

## Yönetici Paneli
- Yönetim sayfasında ilan kartlarında aksiyonlar:
	- Onayla/Reddet: Red için modal açılır ve neden zorunludur.
	- Düzenle: İlan bilgilerini görebilir/düzenleyebilirsiniz.
	- Sil: Silme modalı açılır, silme nedeni zorunludur; kullanıcıya e-posta ile bildirilir.

## AJAX İşlemleri
Eklenti tek bir endpoint üzerinden aksiyonları yönetir: `admin-ajax.php` `action=ativ_ajax`

- `action_type=ativ_change_listing_status` (admin)
	- Parametreler: `id`, `status` (`approved|rejected|pending`), `rejection_reason` (opsiyonel ama `rejected` için zorunlu)
	- Etki: DB güncellemesi ve ilgili e-posta bildirimi.

- `action_type=ativ_delete_listing_admin` (admin)
	- Parametreler: `id`, `deletion_reason` (zorunlu)
	- Etki: İlan ve görseller silinir; kullanıcıya “yönetici tarafından silindi” e-postası gönderilir.

## Hızlı Test Adımları
SMTP testi:
```
WordPress › Ayarlar › Amatör Bitlik › Test Mail
```

Bildirim testleri:
- Yeni ilan ekleyin ve onay/red akışını yönetin; ilgili e-postaların geldiğini doğrulayın.
- Admin panelinden bir ilanı Sil butonuyla silin; silme nedeni modalını doldurun; kullanıcı e-postasını doğrulayın.

## Sorun Giderme
- Türkçe karakter bozulması: SMTP ayarlarını kontrol edin; eklenti PHPMailer’de `UTF-8` + `base64` kullanır.
- Şablon değişkeni görünmüyor: Şablon anahtarını doğru seçtiğinizden ve DB’de kayıtlı olduğundan emin olun; eklenti varsayılan şablona geri düşer.
- Kategori İngilizce görünüyor: `get_category_name()` eşlemesi aktif; DB’deki kategori anahtarları doğru mu kontrol edin.

## Dizin Yapısı
```
amateur-telsiz-ilan-vitrini.php
css/
	base.css
	components.css
	forms.css
	style.css
js/
	core.js
	modal.js
	script.js
	ui.js
templates/
	index.php
	my-listings.php
	partial-modal.php
languages/
	amator-bitlik.pot          # Çeviri şablon dosyası
	amator-bitlik-tr_TR.po     # Türkçe çeviri
	README.md                  # Çeviri dokümantasyonu
```

## Çoklu Dil Desteği

Eklenti WordPress çeviri sistemi ile çoklu dil desteği sunmaktadır.

### Ana Dil
- **Türkçe (tr_TR)** - Varsayılan dil

### Çeviri Dosyaları
- Text Domain: `amator-bitlik`
- Domain Path: `/languages`
- Format: Gettext PO/MO

### Weblate Entegrasyonu
Proje Weblate çeviri yönetim sistemi ile entegre edilmiştir. Yeni diller eklemek ve çevirileri yönetmek için:

1. Weblate'te proje oluşturun
2. Dosya maskesi: `languages/amator-bitlik-*.po`
3. Şablon dosyası: `languages/amator-bitlik.pot`

Detaylı bilgi için `languages/README.md` dosyasına bakın.

### MO Dosyası Oluşturma
```bash
# Tek dosya için
msgfmt languages/amator-bitlik-tr_TR.po -o languages/amator-bitlik-tr_TR.mo

# Tüm .po dosyaları için
for file in languages/*.po; do msgfmt "$file" -o "${file%.po}.mo"; done
```

## Lisans
Bu proje özel kullanım içindir. Gerekmedikçe lisans başlıkları eklenmez.

