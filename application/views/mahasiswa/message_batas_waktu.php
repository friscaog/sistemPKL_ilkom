<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Menampilkan batas waktu submit form.
 * Pesan yang ditampilkan tergantung pada nilai $batas_waktu_submit terhadap tanggal saat ini.
 */

setlocale (LC_TIME, 'id_ID.UTF-8', 'Indonesian_indonesia.1252');
?>

<?php if ($batas_waktu_submit && (time() < strtotime($batas_waktu_submit)+60*60*24)):?>
  
	<div class="ui small orange icon message">
		<i class="wait icon"></i>
		<div class="content">
	  	Silakan lengkapi atau perbarui data untuk tahap ini paling lambat tanggal <?php echo strftime("%d %B %Y", strtotime($batas_waktu_submit));?>.
	  </div>
	</div>

<?php elseif($batas_waktu_submit):?>

	<div class="ui small red icon message">
		<i class="lock icon"></i>
		<div class="content">
	  	Submit data untuk tahap ini sudah tidak dapat dilakukan. Batas akhir adalah tanggal <?php echo strftime("%d %B %Y", strtotime($batas_waktu_submit));?>.
	  </div>
	</div>

<?php endif; ?>
