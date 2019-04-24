<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><body class="mahasiswa-inisialisasi">

<?php $this->load->view('templates/menu'); ?>

	<!-- Main content -->
	<div class="ui basic top main vertical segment">
		<div class="ui text container">
			<div class="center aligned ui basic segment">
				<h2 class="ui icon header">
					<i class="smile icon"></i>
					<div class="content">
						Halo, selamat datang di CS PKL!
						<div class="sub header">Sebelum mulai menggunakan sistem ini, silakan isi data diri Anda terlebih dahulu</div>
					</div>
				</h2>
			</div>
			<div class="hidden ui divider"></div>
			<?php echo form_open('mahasiswa/inisialisasi', array('class' => 'ui form '.$form_state)); ?>
				<!-- Form error message -->
				<div class="ui error message">
					<div class="header">
						Terjadi Kesalahan
					</div>
					<ul class="list">
						<?php echo validation_errors('<li>', '</li>');?>
						<?php echo (isset($submit_errors)) ? $submit_errors : '';?>
					</ul>
				</div>
				<!-- Form fields -->
				<div class="required field <?php echo form_error('nim')?'error':'';?>">
          <label>NIM</label>
         	<input type="text" name="nim" value="<?php echo set_value('nim');?>">
         	<div class="ui list hint">
            <div class="item">Pastikan NIM yang Anda masukkan sudah benar demi kelancaran kegiatan PKL Anda nantinya</div>
          </div>
        </div>
        <div class="required field <?php echo form_error('nama')?'error':'';?>">
          <label>Nama Lengkap</label>
         	<input type="text" name="nama" value="<?php echo set_value('nama');?>">
         	<div class="ui list hint">
            <div class="item">Masukkan nama lengkap Anda sesuai dengan yang tertera pada SIMAK UNUD</div>
          </div>
        </div>
        <div class="required field <?php echo form_error('telepon')?'error':'';?>">
          <label>Nomor Telepon</label>
         	<input type="text" name="telepon" value="<?php echo set_value('telepon');?>">
         	<div class="ui list hint">
            <div class="item">Nomor ini akan digunakan untuk menghubungi Anda terkait dengan kegiatan PKL</div>
          </div>
        </div>
        <div class="required field <?php echo form_error('alamat')?'error':'';?>">
          <label>Alamat Asal</label>
         	<textarea rows="2" name="alamat"><?php echo set_value('alamat');?></textarea>
        </div>
        <button class="ui primary button" type="submit">Submit</button>
			</form>			
		</div>
	</div>
	<script type="text/javascript">
	$(document).ready(function(){
		// Form validation
		$.fn.form.settings.templates.error = function(errors){
			var html = '<div class="header">'+
						'Terjadi Kesalahan'+
					'</div>'+
					'<ul class="list">';
			$.each(errors, function(index, value){
				html += '<li>'+value+'</li>';
			});
			html += '</ul>';
			return html;
		}
		$('.ui.form')
        .form({
          fields: {
            nim: {
              identifier  : 'nim',
              rules: [
                {
                  type   : 'empty',
                  prompt : 'Anda harus memasukkan NIM'
                },
                {
                	type		: 'number',
                	prompt	: 'NIM tidak valid, silakan periksa kembali'
                }
              ]
            },
            nama: {
              identifier  : 'nama',
              rules: [
                {
                  type   : 'empty',
                  prompt : 'Anda harus memasukkan Nama Lengkap'
                },
                {
                	type	: 'regExp[/^[a-zA-Z ]*$/]',
                	prompt: 'Nama Lengkap tidak valid, hanya diijinkan menggunakan karakter alfabet atau spasi'
                }
              ]
            },
            telepon: {
            	identifier: 'telepon',
            	rules: [
            		{
                  type   : 'empty',
                  prompt : 'Anda harus memasukkan Nomor Telepon'
                },
            		{
            			type: 'number',
            			prompt: 'Nomor Telepon tidak valid, hanya diijinkan menggunakan angka'
            		}
            	]
            },
            alamat: {
            	identifier: 'alamat',
            	rules: [
            		{
                  type   : 'empty',
                  prompt : 'Anda harus memasukkan Alamat Asal'
                }
            	]
            }
          }
        })
      ;
	});
	</script>