// terms.js - Kullanım Sözleşmesi ve KVKK onayı için floating buton davranışı
(function(){
  document.addEventListener('DOMContentLoaded', function(){
    if (document.body.classList.contains('admin-page')) return; // Yönetici sayfalarında gösterme
    var btn = document.getElementById('termsFloatingBtn');
    var modal = document.getElementById('termsModal');
    var acceptBtn = document.getElementById('acceptTermsBtn');
    var closeBtns = [document.getElementById('closeTermsBtn'), document.getElementById('termsModalCloseBtn')];
    var formLink = document.getElementById('openTermsLink'); // Form içindeki sözleşme linki

    if (!modal) return; // Modal yoksa çık

    var accepted = localStorage.getItem('termsAccepted') === 'true';
    var firstShown = localStorage.getItem('termsFirstVisitShown') === 'true';

    // İlk ziyaret otomatik açma (henüz gösterilmemişse)
    if (!firstShown) {
      localStorage.setItem('termsFirstVisitShown','true');
      // İlk otomatik açılış: her zaman bilgi amaçlı -> kabul butonu gizli
      if (acceptBtn) acceptBtn.style.display = 'none';
      modal.style.display = 'flex';
    }

    // Floating buton yalnızca kabul edilmemişse görünür
    if (btn && !accepted) {
      btn.style.display = 'flex';
    }

    function openTerms(infoOnly) {
      modal.style.display = 'flex';
      // infoOnly true ise kabul butonu gizli kalır; değilse (formdan açılış) gösterilir
      if (acceptBtn) {
        if (infoOnly) {
          acceptBtn.style.display = 'none';
        } else {
          // Sadece formdaki linkten açıldığında gerekli
          var isAccepted = localStorage.getItem('termsAccepted') === 'true';
          acceptBtn.style.display = isAccepted ? 'none' : 'inline-block';
        }
      }
    }

    // Floating buton -> her zaman bilgi amaçlı açılım
    if (btn) {
      btn.addEventListener('click', function(e){
        e.preventDefault();
        openTerms(true); // infoOnly
      });
    }

    // Form içindeki sözleşme linki -> kabul butonu gösterilecek
    if (formLink) {
      formLink.addEventListener('click', function(e){
        e.preventDefault();
        openTerms(false); // form context
      });
    }

    if (acceptBtn) {
      acceptBtn.addEventListener('click', function(){
        localStorage.setItem('termsAccepted', 'true');
        // Formdaki checkbox'ı işaretle (varsa)
        var termsCheckbox = document.getElementById('formTermsCheckbox');
        if (termsCheckbox) termsCheckbox.checked = true;
        if (btn) btn.style.display = 'none';
        acceptBtn.style.display = 'none';
      });
    }

    closeBtns.forEach(function(b){
      if (!b) return; b.addEventListener('click', function(){
        var acc = localStorage.getItem('termsAccepted') === 'true';
        if (btn && !acc) btn.style.display = 'flex';
        modal.style.display = 'none';
      });
    });

    modal.addEventListener('click', function(e){
      if (e.target.id === 'termsModal') {
        modal.style.display = 'none';
        var acc = localStorage.getItem('termsAccepted') === 'true';
        if (btn && !acc) btn.style.display = 'flex';
      }
    });
  });
})();
