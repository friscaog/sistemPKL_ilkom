<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><body class="dosen-profil">

<?php $this->load->view('templates/menu'); ?>

	<!-- Main content -->
	<div class="ui basic top main vertical segment">
		<div class="ui text container">
			<h1 class="ui dividing header">Profil</h1>
			<table class="ui very basic table">
				<tbody>
					<tr>
						<th>Email</th>
						<td><?php echo $profil['dos_email_cs'];?></td>
					</tr>
					<tr>
						<th>NIP</th>
						<td><?php echo $profil['dos_nip'];?></td>
					</tr>
					<tr>
						<th>Nama</th>
						<td><?php echo $profil['dos_nama'];?></td>
					</tr>
					<tr>
						<th>Nomor Telepon</th>
						<td><?php echo $profil['dos_telepon'];?></td>
					</tr>
					<tr>
						<th>Alamat</th>
						<td><?php echo $profil['dos_alamat'];?></td>
					</tr>
					<tr>
						<th>Kapasitas Peserta per Periode</th>
						<td><?php echo $profil['dos_kapasitas_mhs'];?></td>
					</tr>
				</tbody>
			</table>
			<div class="ui warning message">
				<div class="header">
					Pastikan Data Anda Benar
				</div>
				<p>Jika terdapat kesalahan pada data profil Anda, dimohonkan untuk segera menghubungi administrator.</p>
				<p>Terima kasih</p>
			</div>
		</div>
	</div>
