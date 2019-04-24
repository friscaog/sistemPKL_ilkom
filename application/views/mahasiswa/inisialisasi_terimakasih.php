<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><body class="mahasiswa-inisialisasi-terimakasih">

<?php $this->load->view('templates/menu'); ?>

	<!-- Main content -->
	<div class="ui basic top main vertical segment">
		<div class="ui text middle aligned container grid">
			<div class="center aligned ui basic segment column">
				<h2 class="ui icon header">
					<i class="heart icon"></i>
					<div class="content">
						Terima kasih, selamat menggunakan layanan CS PKL! 
						<div class="sub header">Jangan ragu untuk menghubungi administrator jika Anda mengalami kesulitan atau terjadi kesalahan saat menggunakan sistem ini</div>
					</div>
				</h2>
				<a href="<?php echo site_url('mahasiswa');?>" class="ui big orange button">Lanjutkan</a>
			</div>
		</div>
	</div>
