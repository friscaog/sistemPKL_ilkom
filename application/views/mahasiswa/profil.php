<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><body class="mahasiswa-profil">

<?php $this->load->view('templates/menu'); ?>

	<!-- Main content -->
	<div class="ui basic top main vertical segment">
		<div class="ui text container">
			<h1 class="ui dividing header">Pendaftaran</h1>
			<!-- menampilkan warning message untuk mengisi pendaftaran terlebih dahulu -->
			<!-- flashdata adalah session yang disimpan sekali -->
			<?php if($this->session->flashdata('msg')): ?>
			<div class="ui warning message">
				<div class="header">
					Lengkapi Pendaftaran Terlebih Dahulu.
				</div>
				<p><?php echo $this->session->flashdata('msg'); ?></p>
			</div>
			<?php endif; ?>

			<!-- <div class="ui warning message">
				<div class="header">
					Pastikan Data Anda Benar
				</div>
				<p>Pastikan data Anda benar demi kelancaran kegiatan PKL Anda nantinya.</p>
			</div> -->

			<?php echo form_open('mahasiswa/profil/input_profil_pendaftaran', array('class' => 'ui form '.$form_state)); ?>
				<!-- Form fields -->
				<div class="required field ">
					<label>Periode</label>
					<select name="periode_id" class="ui dropdown periode">
						<!-- jika status=1(sudah terdaftar) maka disabled pilihan -->
						<?php if($status > 0): ?> 
							<?php foreach($data as $row){ ?>
								<option selected hidden disabled><?php echo $row->per_nama ?></option>
							<?php } ?>
						<?php else: ?>
	            		<?php foreach($periode as $row){ ?>
					    	<option  value="<?php echo $row->per_id ?>"><?php echo $row->per_nama ?></option>
						<?php } ?>
						<?php endif; ?>
          			</select>
				</div>

				<div class="required field ">
					<label>Institusi Tempat PKL</label>
					<select name="tempat_id" class="ui dropdown periode">
						<!-- jika status=1(sudah terdaftar) maka disabled pilihan -->
						<?php if($status > 0): ?>
						<?php foreach($data as $row){ ?>
							<option selected hidden disabled><?php echo $row->tem_nama ?></option>
						<?php } ?>
						<?php else: ?>
	            		<?php foreach($tempat as $row){ ?>
					    	<option value="<?php echo $row->tem_id ?>"><?php echo $row->tem_nama ?></option>
						<?php } ?>
						<?php endif; ?>
          			</select>
				</div>

				<div class="required field ">
					<label>Dosen Pembimbing</label>
					<select name="dosen_id" class="ui dropdown periode">
						<!-- jika status=1(sudah terdaftar) maka disabled pilihan -->
						<?php if($status > 0): ?>
						<?php foreach($data as $row){ ?>
							<option selected hidden disabled><?php echo $row->dos_nama ?></option>
						<?php } ?>
						<?php else: ?>
	            		<?php foreach($dos_pem as $row){ ?>
					    	<option value="<?php echo $row->dos_id ?>"><?php echo $row->dos_nama ?></option>
						<?php } ?>
						<?php endif; ?>
	          		</select>
				</div>

				<!-- jika status=1(sudah terdaftar) maka disabled button -->
				<?php if($status > 0): ?>
					<button class="ui primary button" name="submit" value="submit" type="submit" disabled>Submit</button>
				<?php else: ?>	
					<button class="ui primary button" name="submit" value="submit" type="submit">Submit</button>
				<?php endif ?>
			</form>
		</div>

		<div class="ui text container">
			<br><h1 class="ui dividing header">Profil</h1>
			
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
				<div class="required field <?php echo form_error('dosen_pa')?'error':'';?>">
					<label>Dosen Pembimbing Akademik</label>
					<input type="text" name="dosen_pa" placeholder="Dosen Pembimbing Akademik" value="<?php echo $profil['dos_nama'];?>" disabled>
				</div>
				<div class="required field <?php echo form_error('nim')?'error':'';?>">
					<label>NIM</label>
					<input type="text" name="nim" placeholder="NIM" value="<?php echo ($form_state==='error')?set_value('nim'):$profil['mhs_nim'];?>">
				</div>

				<!-- masukkan semester disini -->
				<div class="required field <?php echo form_error('semester')?'error':'';?>">
					<label>Semester</label>
					<input type="text" name="semester" placeholder="Semester" value="<?php echo ($form_state==='error')?set_value('semester'):$profil['mhs_smt'];?>">
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
