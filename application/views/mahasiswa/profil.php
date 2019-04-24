<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><body class="mahasiswa-profil">

<?php $this->load->view('templates/menu'); ?>

	<!-- Main content -->
	<div class="ui basic top main vertical segment">
		<div class="ui text container">
			<h1 class="ui dividing header">Profil</h1>
			<div class="ui warning message">
				<div class="header">
					Pastikan Data Anda Benar
				</div>
				<p>Pastikan data Anda benar demi kelancaran kegiatan PKL Anda nantinya.</p>
			</div>
			<?php echo form_open('mahasiswa/profil', array('class' => 'ui form '.$form_state)); ?>
				<!-- Form error message -->
				<div class="ui error message">
					<i class="close icon"></i>
					<div class="header">
						Terjadi Kesalahan
					</div>
					<ul class="list">
						<?php echo validation_errors('<li>', '</li>');?>
						<?php echo (isset($submit_errors)) ? $submit_errors : '';?>
					</ul>
				</div>
				<div class="ui success message">
		          <i class="close icon"></i>
		          <div class="header">Berhasil</div>
		          <p>Data Anda berhasil diubah.</p>
		        </div>
				<!-- Form fields -->
				<div class="required field <?php echo form_error('email')?'error':'';?>">
					<label>Email</label>
					<input type="text" name="email" placeholder="Email" value="<?php echo $profil['mhs_email_cs'];?>" disabled>
				</div>
				<div class="required field <?php echo form_error('nim')?'error':'';?>">
					<label>NIM</label>
					<input type="text" name="nim" placeholder="NIM" value="<?php echo ($form_state==='error')?set_value('nim'):$profil['mhs_nim'];?>">
				</div>
				<div class="required field <?php echo form_error('nama')?'error':'';?>">
					<label>Nama</label>
					<input type="text" name="nama" placeholder="Nama" value="<?php echo ($form_state==='error')?set_value('nama'):$profil['mhs_nama'];?>">
				</div>
				<div class="required field <?php echo form_error('telepon')?'error':'';?>">
					<label>Nomor Telepon</label>
					<input type="text" name="telepon" placeholder="Nomor Telepon" value="<?php echo ($form_state==='error')?set_value('telepon'):$profil['mhs_telepon'];?>">
				</div>
				<div class="required field <?php echo form_error('alamat')?'error':'';?>">
					<label>Alamat</label>
					<textarea name="alamat" placeholder="Alamat"><?php echo ($form_state==='error')?set_value('alamat'):$profil['mhs_alamat'];?></textarea>
				</div>
				<button class="ui button" type="submit">Ubah</button>
			</form>
		</div>
	</div>
	<script type="text/javascript">
	  $(document).ready(function(){
	    // Dismissable message.
	    $('.message .close')
	      .on('click', function() {
	        $(this)
	          .closest('.message')
	          .transition('fade')
	        ;
	      })
	    ;

	  });
	 </script>
