# Amatör Bitlik - Çeviri Dosyaları

Bu dizin WordPress eklentisi için çeviri dosyalarını içerir.

## Dosyalar

- `amator-bitlik.pot` - Çeviri şablon dosyası (POT - Portable Object Template)
- `amator-bitlik-tr_TR.po` - Türkçe çeviri dosyası (PO - Portable Object)
- `amator-bitlik-tr_TR.mo` - Derlenmiş Türkçe çeviri dosyası (MO - Machine Object) - Otomatik oluşturulacak

## Weblate Entegrasyonu

Bu proje Weblate çeviri yönetim sistemini kullanmaya hazır hale getirilmiştir.

### Yapılandırma

1. **Text Domain**: `amator-bitlik`
2. **Domain Path**: `/languages`
3. **Ana Dil**: Türkçe (tr_TR)
4. **Dosya Formatı**: Gettext PO

### Yeni Dil Ekleme

Yeni bir dil eklemek için:

1. `amator-bitlik.pot` dosyasını temel alın
2. Yeni bir `.po` dosyası oluşturun: `amator-bitlik-{locale}.po`
   - Örnek: `amator-bitlik-en_US.po` (İngilizce)
   - Örnek: `amator-bitlik-de_DE.po` (Almanca)
3. `.po` dosyasını çevirin
4. Poedit veya msgfmt ile `.mo` dosyasını derleyin:
   ```bash
   msgfmt amator-bitlik-{locale}.po -o amator-bitlik-{locale}.mo
   ```

### .mo Dosyası Oluşturma

WordPress'in çevirileri kullanabilmesi için `.po` dosyalarından `.mo` dosyaları oluşturulmalıdır:

#### Poedit ile:
1. Poedit'te `.po` dosyasını açın
2. Dosya > Kaydet (otomatik olarak .mo dosyası oluşturur)

#### Komut satırı ile:
```bash
# Tek dosya için
msgfmt amator-bitlik-tr_TR.po -o amator-bitlik-tr_TR.mo

# Tüm .po dosyaları için
for file in *.po; do msgfmt "$file" -o "${file%.po}.mo"; done
```

### Weblate Kurulumu

1. Weblate projesinde yeni bir component oluşturun
2. Şu ayarları kullanın:
   - **Dosya Maskesi**: `languages/amator-bitlik-*.po`
   - **Şablon Dosyası**: `languages/amator-bitlik.pot`
   - **Yeni Çeviri Tabanı**: `languages/amator-bitlik.pot`
   - **Dosya Formatı**: Gettext PO (po)

### Çeviri Güncellemeleri

Kod değişikliklerinden sonra `.pot` dosyasını güncellemek için:

```bash
# WP-CLI kullanarak
wp i18n make-pot . languages/amator-bitlik.pot

# Veya xgettext kullanarak
find . -name "*.php" -not -path "./vendor/*" | xargs xgettext --from-code=UTF-8 -o languages/amator-bitlik.pot
```

## Desteklenen Diller

- 🇹🇷 Türkçe (tr_TR) - %100 Tamamlandı
- Diğer diller Weblate üzerinden eklenebilir

## Katkıda Bulunma

Çeviri katkılarınız için:
1. Weblate üzerinden çeviri yapabilirsiniz (önerilir)
2. Veya `.po` dosyasını düzenleyip pull request gönderebilirsiniz

## Notlar

- `.pot` dosyası güncel tutulmalıdır
- `.mo` dosyaları repository'ye commit edilmemelidir (build sırasında oluşturulmalı)
- Her çeviri güncellemesinden sonra `.mo` dosyalarını yeniden derleyin
- Weblate otomatik olarak `.mo` dosyalarını oluşturabilir

## Lisans

Bu çeviri dosyaları eklenti ile aynı lisansa sahiptir.
