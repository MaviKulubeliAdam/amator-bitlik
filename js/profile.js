// Bitlik Profilim - AJAX ile profil kaydetme

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profileInfoForm');
    if (!form) return;

    // Alan kodu seçeneğini doldur (modal.js'den countryCodes kullanıyoruz)
    const countryCodeSelect = document.getElementById('profileCountryCode');
    
    // Kısa bir delay ile countryCodes'ın yüklenmesini bekle
    setTimeout(function() {
        if (countryCodeSelect && typeof countryCodes !== 'undefined' && countryCodes.length > 0) {
            countryCodeSelect.innerHTML = '';
            countryCodes.forEach(country => {
                const option = document.createElement('option');
                option.value = country.dialCode;
                option.textContent = `${country.flag} ${country.dialCode}`;
                option.setAttribute('data-name', country.name);
                if (country.dialCode === '+90') {
                    option.selected = true;
                }
                countryCodeSelect.appendChild(option);
            });
        } else if (!countryCodeSelect) {
            console.warn('[WARN] profileCountryCode element bulunamadı');
        } else {
            console.warn('[WARN] countryCodes yüklenemedi');
        }
    }, 100);

    // Telefon numarasını formatla
    const phoneInput = document.getElementById('profilePhone');
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, ''); // Sadece rakamlar
            let formatted = '';
            
            if (value.length >= 1) formatted = value.substring(0, 3);
            if (value.length >= 4) formatted += ' ' + value.substring(3, 6);
            if (value.length >= 7) formatted += ' ' + value.substring(6, 8);
            if (value.length >= 9) formatted += ' ' + value.substring(8, 10);
            
            e.target.value = formatted.trim();
        });
    }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Önce ban durumunu kontrol et
        try {
            const banCheckResponse = await fetch(window.ajaxurl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'check_user_ban',
                    nonce: window.atheneaNonce || ''
                })
            });
            const banStatus = await banCheckResponse.json();
            if (banStatus.data && banStatus.data.is_banned) {
                // Ban modalı göster
                if (window.showBannedUserModal) {
                    window.showBannedUserModal(banStatus.data.ban_reason, banStatus.data.banned_at);
                }
                return;
            }
        } catch (e) {
            console.error('Ban durumu kontrol edilemedi:', e);
        }
        
        const callsign = document.getElementById('profileCallsign').value.trim();
        const name = document.getElementById('profileName').value.trim();
        const email = document.getElementById('profileEmail').value.trim();
        const location = document.getElementById('profileLocation').value.trim();
        
        // Telefon: alan kodu + numarası boşluksuz birleştir
        const countryCode = document.getElementById('profileCountryCode').value;
        const phoneNumber = document.getElementById('profilePhone').value.replace(/\D/g, ''); // Sadece rakamlar
        const phone = countryCode + phoneNumber;
        
        const termsAccepted = document.getElementById('profileTermsCheckbox').checked;
        const user_id = window.bitlikUserId || null;

        // Sözleşme kontrolü
        if (!termsAccepted) {
            showMessage('Kullanıcı sözleşmesini kabul etmelisiniz.', false);
            return;
        }

        // Zorunlu alan kontrolü
        if (!callsign || !name || !email || !location || !phoneNumber) {
            console.log('[PROFILE DEBUG] HATA: Boş zorunlu alan var');
            showMessage('Tüm alanları doldurun.', false);
            return;
        }

        // E-posta formatı kontrolü
        if (!validateEmail(email)) {
            console.log('[PROFILE DEBUG] HATA: E-posta formatı yanlış');
            showMessage('Geçerli bir e-posta girin.', false);
            return;
        }

        // Telefon formatı kontrolü
        // +90 ile başlayıp 10 haneli olmalı (boşluksuz veya boşluklu)
        const phoneWithoutSpaces = phone.replace(/\s/g, '');
        const phoneRegex = /^\+90\d{10}$/;
        console.log('[PROFILE DEBUG] Telefon regex testi:');
        console.log('[PROFILE DEBUG] Regex:', phoneRegex);
        console.log('[PROFILE DEBUG] Test değeri (boşluksuz):', phoneWithoutSpaces);
        console.log('[PROFILE DEBUG] Regex sonucu:', phoneRegex.test(phoneWithoutSpaces));
        
        if (!phoneRegex.test(phoneWithoutSpaces)) {
            console.log('[PROFILE DEBUG] HATA: Telefon formatı yanlış - ' + phone);
            showMessage('Telefon formatı: +90 ile başlamalı ve 10 haneli olmalı (örn: +90 555 123 4567)', false);
            return;
        }

        // AJAX ile kaydet
        const data = new FormData();
        data.append('action', 'amator_bitlik_add_user');
        data.append('user_id', user_id);
        data.append('callsign', callsign);
        data.append('name', name);
        data.append('email', email);
        data.append('location', location);
        data.append('phone', phone);
        data.append('_wpnonce', window.atheneaNonce || '');

        showLoading(true);
        fetch(window.ajaxurl, {
            method: 'POST',
            body: data
        })
        .then(async res => {
            // Önce ham metni al
            const text = await res.text();
            // Deneyerek JSON parse et
            try {
                const json = JSON.parse(text);
                showLoading(false);
                if (json.success) {
                    showMessage(json.data.message, true);
                } else {
                    showMessage(json.data.message || 'Kayıt başarısız.', false);
                }
            } catch (e) {
                // JSON parse hatası -> sunucu HTML veya hata döndürüyor
                showLoading(false);
                console.error('Sunucu beklenmeyen yanıt döndürdü:', text);
                showMessage('Sunucu hatası (beklenmeyen yanıt). Konsolu kontrol edin.', false);
            }
        })
        .catch((err) => {
            showLoading(false);
            console.error('Sunucu hatası:', err);
            showMessage('Sunucu hatası.', false);
        });
    });

    function showMessage(msg, success) {
        const el = document.getElementById('profileInfoMessage');
        el.textContent = msg;
        el.style.color = success ? 'green' : 'red';
    }
    function showLoading(show) {
        document.getElementById('profileLoadingOverlay').style.display = show ? 'flex' : 'none';
    }
    function validateEmail(email) {
        return /^\S+@\S+\.\S+$/.test(email);
    }

    // Sözleşme linkine tıklayınca modal aç
    const termsLink = document.getElementById('profileTermsLink');
    
    if (termsLink) {
        termsLink.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const modal = document.getElementById('termsModal');
            const acceptBtn = document.getElementById('acceptTermsBtn');
            
            if (modal) {
                modal.style.display = 'flex';
                if (acceptBtn) {
                    const isAccepted = localStorage.getItem('termsAccepted') === 'true';
                    acceptBtn.style.display = isAccepted ? 'none' : 'inline-block';
                    
                    // Kabul butonuna tıklayınca checkbox'ı işaretle
                    acceptBtn.onclick = function() {
                        localStorage.setItem('termsAccepted', 'true');
                        const checkbox = document.getElementById('profileTermsCheckbox');
                        if (checkbox) checkbox.checked = true;
                        modal.style.display = 'none';
                    };
                }
            }
        });
    }
});
