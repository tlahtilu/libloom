<?php
// HALAMAN PENGATURAN AKUN (klik nama admin di sidebar)
$akun = loggedUser();

if (!$akun) {
    flash('Akun tidak ditemukan. Silakan login kembali.');
    go('?page=login');
}

admin_open('admin');
?>
<h1>Pengaturan Akun</h1>
<?= $fl ?>

<div class='pn'>
  <div class='top'>
    <div>
      <h3><?= h(trim((string)($akun['nama'] ?? '')) ?: $akun['username']) ?></h3>
      <small>@<?= h($akun['username']) ?> · <?= h(ucfirst($akun['role'])) ?> · terdaftar <?= h(substr((string)($akun['created_at'] ?? ''), 0, 10)) ?></small>
    </div>
    <span class='pill'>● Aktif</span>
  </div>
</div>

<form method='post' action='?act=save_admin' autocomplete='off'>

  <div class='pn'>
    <h3>Informasi akun</h3>
    <small>Data ini dipakai untuk identitas dan untuk login ke Libloom.</small>
    <div class='fm' style='margin-top:12px'>
      <div>
        <label for='nama'>Nama lengkap</label>
        <input id='nama' name='nama' required maxlength='100' value='<?= h($akun['nama'] ?? '') ?>' placeholder='Nama lengkap'>
      </div>
      <div>
        <label for='username'>Username (untuk login)</label>
        <input id='username' name='username' required minlength='3' maxlength='30' pattern='[A-Za-z0-9_.\-]+' value='<?= h($akun['username']) ?>'>
      </div>
      <div>
        <label for='email'>Email</label>
        <input id='email' type='email' name='email' maxlength='100' value='<?= h($akun['email'] ?? '') ?>' placeholder='nama@email.com'>
      </div>
      <div>
        <label for='telp'>Nomor telepon</label>
        <input id='telp' name='telp' maxlength='30' value='<?= h($akun['telp'] ?? '') ?>' placeholder='08xxxxxxxxxx'>
      </div>
    </div>
  </div>

  <div class='pn'>
    <h3>Ubah password</h3>
    <small>Kosongkan ketiga kolom ini kalau tidak ingin mengganti password.</small>
    <div class='fm' style='margin-top:12px'>
      <div class='w'>
        <label for='password_lama'>Password lama</label>
        <input id='password_lama' type='password' name='password_lama' autocomplete='current-password' placeholder='Masukkan password lama'>
      </div>
      <div>
        <label for='password_baru'>Password baru</label>
        <input id='password_baru' type='password' name='password_baru' minlength='6' autocomplete='new-password' placeholder='Minimal 6 karakter'>
      </div>
      <div>
        <label for='konfirmasi_password'>Konfirmasi password baru</label>
        <input id='konfirmasi_password' type='password' name='konfirmasi_password' minlength='6' autocomplete='new-password' placeholder='Ulangi password baru'>
      </div>
      <div class='w'>
        <label style='display:flex;gap:8px;align-items:center;font-weight:400;cursor:pointer'>
          <input type='checkbox' id='tampil' style='width:auto' onclick="['password_lama','password_baru','konfirmasi_password'].forEach(function(i){document.getElementById(i).type=this.checked?'text':'password'}.bind(this))">
          Tampilkan password
        </label>
      </div>
    </div>
  </div>

  <div style='display:flex;gap:12px;flex-wrap:wrap'>
    <button class='btn'>💾 Simpan Perubahan</button>
    <a class='btn g' href='?page=dashboard'>← Kembali ke Dashboard</a>
  </div>
</form>
<?php admin_close();