<?php
defined('BASEPATH') OR exit('No direct script access allowed');

setlocale (LC_TIME, 'id_ID.UTF-8', 'Indonesian_indonesia.1252');

?><body class="mahasiswa-pasca-ujian">

<?php $this->load->view('templates/menu'); ?>

<?php $this->load->view('mahasiswa/menu_steps'); ?>

  <!-- Main content -->
  <div class="ui basic main vertical segment">
    <div class="ui text container">

      <?php $this->load->view('mahasiswa/message_batas_waktu'); ?>

      <?php $this->load->view('mahasiswa/message_status'); ?>
      
      <h1 class="ui dividing header">Pasca Ujian</h1>
      <?php echo form_open_multipart('mahasiswa/pasca_ujian', array('class' => 'ui form '.$form_state)); ?>
        <div class="ui success message">
          <i class="close icon"></i>
          <div class="header">Berhasil</div>
          <p>Data Anda telah tersimpan.</p>
        </div>
        <div class="ui error message">
          <i class="close icon"></i>
          <div class="header">
            Terjadi Kesalahan
          </div>
          <ul class="list">
            <?php if (isset($submit_errors)): foreach ($submit_errors as $key => $value): ?>
              <li><?php echo $value; ?></li>
            <?php endforeach; endif; ?>
          </ul>
        </div>
        <div class="required field <?php echo (isset($submit_errors['laporan_revisi']))?'error':''; ?>">
          <label>Revisi Laporan</label>
          <!-- List file peserta-->
          <div class="ui tiny list ">
          <?php foreach($peserta_data_laporan_revisi as $index => $laporan_revisi): ?>
            <div class="item">
              <a class="header" href="<?php echo $laporan_revisi['file_url']; ?>" target="_blank">Versi <?php echo $index+1;?></a>
              <?php echo strftime("%d %B %Y %H:%M", strtotime($laporan_revisi['plr_date']));?>
            </div>
          <?php endforeach; ?>
          </div>
          <input type="file" name="laporan_revisi"/>
          <div class="ui list hint">
            <div class="item">Revisi laporan harus telah disetujui oleh dosen pembimbing</div>
            <div class="item">Tipe file pdf maks 5MB</div>
          </div>
        </div>  
        <div class="required field <?php echo (isset($submit_errors['laporan_lembar_pengesahan']))?'error':''; ?>">
          <label>Lembar Pengesahan</label>
          <!-- List file peserta-->
          <div class="ui tiny list ">
          <?php foreach($peserta_data_laporan_lembar_pengesahan as $index => $laporan_lembar_pengesahan): ?>
            <div class="item">
              <a class="header" href="<?php echo $laporan_lembar_pengesahan['file_url']; ?>" target="_blank">Versi <?php echo $index+1;?></a>
              <?php echo strftime("%d %B %Y %H:%M", strtotime($laporan_lembar_pengesahan['pllp_date']));?>
            </div>
          <?php endforeach; ?>
          </div>
          <input type="file" name="laporan_lembar_pengesahan"/>
          <div class="ui list hint">
            <div class="item">Tipe file pdf maks 1MB</div>
          </div>
        </div>  
        <div class="required field <?php echo (isset($submit_errors['bukti_pengumpulan_laporan']))?'error':''; ?>">
          <label>Bukti Pengumpulan Laporan</label>
          <!-- List file peserta-->
          <div class="ui tiny list ">
          <?php foreach($peserta_data_bukti_pengumpulan_laporan as $index => $bukti_pengumpulan_laporan): ?>
            <div class="item">
              <a class="header" href="<?php echo $bukti_pengumpulan_laporan['file_url']; ?>" target="_blank">Versi <?php echo $index+1;?></a>
              <?php echo strftime("%d %B %Y %H:%M", strtotime($bukti_pengumpulan_laporan['pbpl_date']));?>
            </div>
          <?php endforeach; ?>
          </div>
          <input type="file" name="bukti_pengumpulan_laporan"/>
          <div class="ui list hint">
            <div class="item">Tipe file pdf maks 1MB</div>
          </div>
        </div>        
        <button class="ui primary button" type="submit" name="submit" value="submit">Submit</button>
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
