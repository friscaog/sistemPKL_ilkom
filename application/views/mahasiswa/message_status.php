<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Menampilkan pesan status mahasiswa.
 * Terdapat empat jenis pesan yang akan muncul tergantung nilai $peserta_status.
 */
if ($peserta_status=='belum_lengkap'):?> 

  <div class="ui icon message">
    <i class="minus icon"></i>
    <div class="content">
      <div class="header">Belum Lengkap</div>
      <p>Silakan lengkapi data-data yang diperlukan untuk dapat melanjutkan ke tahap berikutnya.</p>
    </div>
  </div>

<?php elseif ($peserta_status=='mengunggu_konfirmasi_pembimbing'):?>

  <div class="ui info icon message">
    <i class="blue warning icon"></i>
    <div class="content">
      <div class="header">Menunggu Konfirmasi Pembimbing</div>
      <p>Sedang menunggu persetujuan pembimbing pada data-data tertentu.</p>
    </div>
  </div>

<?php elseif ($peserta_status=='pending'):?>

  <div class="ui success icon message">
    <i class="green checkmark icon"></i>
    <div class="content">
      <div class="header">Menunggu Verifikasi Administrator</div>
      <p>Sedang menunggu verifikasi administrator untuk melanjutkan ke tahap berikutnya.</p>
    </div>
  </div>

<?php elseif ($peserta_status=='approved'):?>

  <div class="ui black icon message">
    <i class="spy icon"></i>
    <div class="content">
      <div class="header">Telah Terverifikasi Administrator</div>
      <p>Tahap ini telah terverifikasi oleh administrator.</p>
    </div>
  </div>

<?php endif;?>
