<?php
defined('BASEPATH') OR exit('No direct script access allowed');

setlocale (LC_TIME, 'id_ID.UTF-8', 'Indonesian_indonesia.1252');

if (
  $peserta_data_laporan_draft &&
  $peserta_data_laporan_draft[count($peserta_data_laporan_draft)-1]['pld_konfirmasi']
  )
{
  // Draft laporan sudah dikonfirmasi pembimbing.
  $html_konfirmasi_laporan_draft = '<i class="huge green checkmark positive icon popup-trigger" data-html="Draft laporan <strong>telah disetujui</strong> dosen pembimbing"></i>';
}
else
{
  // Draft laporan belum dikonfirmasi.
  $html_konfirmasi_laporan_draft = '<i class="huge info negative icon popup-trigger" data-html="Draft laporan <strong>belum disetujui</strong> dosen pembimbing"></i>';
}

?><body class="mahasiswa-pasca-pkl">

<?php $this->load->view('templates/menu'); ?>

<?php $this->load->view('mahasiswa/menu_steps'); ?>

  <!-- Main content -->
  <div class="ui basic main vertical segment">
    <div class="ui text container">

      <?php $this->load->view('mahasiswa/message_batas_waktu'); ?>

      <?php $this->load->view('mahasiswa/message_status'); ?>
      
      <h1 class="ui dividing header">Pasca PKL</h1>
      <?php echo form_open_multipart('mahasiswa/pasca_pkl', array('class' => 'ui form '.$form_state)); ?>
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
        <div class="two fields">
          <div class="required field <?php echo (isset($submit_errors['laporan_draft']))?'error':''; ?>">
            <label>Draft Laporan</label>
            <!-- List file peserta-->
            <div class="ui tiny list ">
            <?php foreach($peserta_data_laporan_draft as $index => $laporan_draft): ?>
              <div class="item">
                <a class="header" href="<?php echo $laporan_draft['file_url']; ?>" target="_blank">Versi <?php echo $index+1;?></a>
                <?php echo strftime("%d %B %Y %H:%M", strtotime($laporan_draft['pld_date']));?>
              </div>
            <?php endforeach; ?>
            </div>
            <input type="file" name="laporan_draft" />
            <div class="ui list hint">
              <div class="item">Draft laporan harus telah disetujui oleh dosen pembimbing</div>
              <div class="item">Tipe file pdf maks 5MB</div>
            </div>
          </div>  
          <div class="field">
            <?php echo $html_konfirmasi_laporan_draft;?>
          </div>
        </div> 
        <div class="required field <?php echo (isset($submit_errors['surat_selesai']))?'error':''; ?>">
          <label>Surat Keterangan Telah Selesai PKL</label>
          <!-- List file peserta-->
          <div class="ui tiny list ">
          <?php foreach($peserta_data_surat_selesai_pkl as $index => $surat_selesai_pkl): ?>
            <div class="item">
              <a class="header" href="<?php echo $surat_selesai_pkl['file_url']; ?>" target="_blank">Versi <?php echo $index+1;?></a>
              <?php echo strftime("%d %B %Y %H:%M", strtotime($surat_selesai_pkl['pssp_date']));?>
            </div>
          <?php endforeach; ?>
          </div>
          <input type="file" name="surat_selesai" />
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
    // Popup message.
    $('.popup-trigger').popup();

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