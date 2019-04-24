<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><body class="mahasiswa-pelaksanaan">

<?php $this->load->view('templates/menu'); ?>

<?php $this->load->view('mahasiswa/menu_steps'); ?>

  <!-- Main content -->
  <div class="ui basic main vertical segment">
	<div class="ui container">

		<?php $this->load->view('mahasiswa/message_batas_waktu'); ?>

    <?php $this->load->view('mahasiswa/message_status'); ?>

	  <!-- Container untuk react component -->
	  <div class="main-content"></div>

	</div>
  </div>
