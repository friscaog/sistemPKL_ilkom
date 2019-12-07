<?php
defined('BASEPATH') OR exit('No direct script access allowed');

setlocale (LC_TIME, 'id_ID.UTF-8', 'Indonesian_indonesia.1252');

// Dropdown options Periode PKL.
$periode_options = '';
foreach ($periode_list as $item) {
  // Form dalam posisi error.
  if ($form_state === 'error')
  {
    $selected = set_select('periode', $item['per_id']);
  }
  // Form dalam posisi submit sukses atau form berjenis edit.
  else if ( ! empty($peserta_data))
  {
    $selected = ($item['per_id']===$peserta_data['per_id']) ? 'selected="selected"' : '';
  }
  // Form berjenis add dan tidak dalam posisi submit (Belum ada option yang terpilih).
  else 
  {
    $selected = '';
  }

  $periode_options .= "<option value=\"{$item['per_id']}\" {$selected}>{$item['per_nama']} (TA {$item['per_tahun']} semester {$item['per_semester']})</options>\n";
}

// Dropdown options Tempat PKL.
$tempat_options = '';
$tempat_value = '';
foreach ($tempat_list as $item) {  
  // Form dalam posisi error.
  if ($form_state === 'error' && set_select('tempat', $item['tem_id']))
  {
    $tempat_value = $item['tem_id'];
  }  
  // Form dalam posisi submit sukses atau form berjenis edit.
  else if ( ! empty($peserta_data) && ($item['tem_id']===$peserta_data['tem_id']))
  {
    $tempat_value = $item['tem_id'];
  }
  
  $tempat_options .= "<div class=\"item\" data-value=\"{$item['tem_id']}\">{$item['tem_nama']}</div>\n";
}

// Dropdown options Pembimbing.
$pembimbing_options = '';
$pembimbing_value = '';
foreach ($dosen_list as $item) {
  // Form dalam posisi error.
  if ($form_state === 'error' && set_select('pembimbing', $item['dos_id']))
  {
    $pembimbing_value = $item['dos_id'];
  }    
  // Form dalam posisi submit sukses atau form berjenis edit.
  else if ( ! empty($peserta_data) && ($item['dos_id']===$peserta_data['pes_pembimbing']))
  {
    $pembimbing_value = $item['dos_id'];
  }
  
  $status_kapasitas = ($item['bimbingan'] >= $item['dos_kapasitas_mhs']) ? '(kapasitas penuh)' : '';
  $pembimbing_options .= "<div class=\"item\" data-value=\"{$item['dos_id']}\">{$status_kapasitas} {$item['dos_nama']}</div>\n";
}

?>
<body class="mahasiswa-pendaftaran">

<?php $this->load->view('templates/menu'); ?>

<?php $this->load->view('mahasiswa/menu_steps'); ?>


  <!-- Main content -->
  <div class="ui basic main vertical segment">
    <div class="ui text container">

      <?php $this->load->view('mahasiswa/message_batas_waktu'); ?>

      <?php $this->load->view('mahasiswa/message_status'); ?>
      
      <h1 class="ui dividing header">Pendaftaran</h1>
      <?php echo form_open_multipart('mahasiswa/pendaftaran', array('class' => 'ui form '.$form_state)); ?>
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
            <?php echo validation_errors('<li>', '</li>'); ?>
            <?php if (isset($submit_errors)): foreach ($submit_errors as $key => $value): ?>
              <li><?php echo $value; ?></li>
            <?php endforeach; endif; ?>
          </ul>
        </div>
<!--         <div class="required field <?php echo form_error('periode')?'error':'';?> ">
          <label>Periode</label>
          <select name="periode" class="ui dropdown periode <?php echo ($form_type==='edit')?'disabled':'';?>">
            <option value="">Periode</option>
            <?php echo $periode_options; ?>
          </select>
        </div>
        <div class="required field <?php echo form_error('tempat')?'error':''; ?> ">
          <label>Institusi Tempat PKL</label>
          <div class="ui search selection dropdown tempat <?php echo ($form_type==='edit')?'disabled':'';?>">
            <input type="hidden" name="tempat" value="<?php echo $tempat_value;?>"/>
            <i class="dropdown icon"></i>
            <div class="default text">Institusi tempat PKL</div>
            <div class="menu">
              <?php echo $tempat_options; ?>
            </div>
          </div>
          <?php if($form_type!=='edit'): ?>
          <div class="ui list hint">
            <div class="item">Pilihan berisi nama-nama institusi tempat PKL yang belum memenuhi kuota mahasiswa <span class="periode-hint"></span></div>
          </div>
          <?php endif;?>
        </div>
        <div class="required field <?php echo form_error('pembimbing')?'error':''; ?> ">
          <label>Dosen Pembimbing</label>
          <div class="ui search selection dropdown pembimbing <?php echo ($form_type==='edit')?'disabled':'';?>">
            <input type="hidden" name="pembimbing" value="<?php echo $pembimbing_value;?>">
            <i class="dropdown icon"></i>
            <div class="default text">Dosen pembimbing</div>
            <div class="menu">
              <?php echo $pembimbing_options; ?>
            </div>            
          </div>
          <?php if($form_type!=='edit'): ?>
          <div class="ui list hint">
            <div class="item">Daftar dosen pembimbing disertai status kapasitas mahasiswa bimbingan <span class="periode-hint"></span></div>
            <div class="item">Komisi PKL dapat mengubah pilihan dosen pembimbing dengan pertimbangan tertentu, terutama untuk dosen pembimbing yang kapasitasnya penuh</div>
          </div>
          <?php endif;?>
        </div> -->
        <div class="required field <?php echo (isset($submit_errors['transkrip']) || form_error('transkrip'))?'error':''; ?>">
          <label>Transkrip Nilai</label>
          <!-- List file peserta-->
          <div class="ui tiny list ">
          <?php foreach($peserta_data_transkrip as $index => $transkrip): ?>
            <div class="item">
              <a class="header" href="<?php echo $transkrip['file_url']; ?>" target="_blank">Versi <?php echo $index+1;?></a>
              <?php echo strftime("%d %B %Y %H:%M", strtotime($transkrip['ptn_date']));?>
            </div>
          <?php endforeach; ?>
          </div>
          <input type="file" name="transkrip" />
          <div class="ui list hint">
            <div class="item">Mahasiswa wajib menyelesaikan minimal 110 SKS</div>
            <div class="item">Tipe file pdf maks 1MB</div>
            <div class="item">Peserta dapat melakukan upload ulang file</div>
          </div>
        </div>
        <div class="required field <?php echo (isset($submit_errors['surat_pernyataan_memenuhi_syarat']) || form_error('surat_pernyataan_memenuhi_syarat'))?'error':''; ?>">
          <label>Surat Pernyataan Memenuhi Syarat</label>
          <!-- button untuk geerate pdf -->
          <a class="ui orange button popup-trigger" data-content="Download Surat Pernyataan" href="Pendaftaran/pernyataan" target="_blank"><i class="download icon"></i>Download Surat Pernyataan</a>
          <!-- List file peserta-->
          <div class="ui tiny list ">
          <?php foreach($peserta_data_surat_pernyataan_memenuhi_syarat as $index => $surat_pernyataan_memenuhi_syarat): ?>
            <div class="item">
              <a class="header" href="<?php echo $surat_pernyataan_memenuhi_syarat['file_url']; ?>" target="_blank">Versi <?php echo $index+1;?></a>
              <?php echo strftime("%d %B %Y %H:%M", strtotime($surat_pernyataan_memenuhi_syarat['pspms_date']));?>
            </div>
          <?php endforeach; ?>
          </div>
          <input type="file" name="surat_pernyataan_memenuhi_syarat" />
          <div class="ui list hint">
            <div class="item">Surat pernyataan telah memenuhi syarat mengikuti PKL dari pembimbing akademik</div>
            <div class="item">Tipe file pdf maks 1MB</div>
            <div class="item">Peserta dapat melakukan upload ulang file</div>
          </div>
        </div>
        <div class="required field <?php echo (isset($submit_errors['surat_permohonan']) || form_error('surat_permohonan'))?'error':''; ?>">
          <label>Surat Permohonan PKL</label>
          <!-- button untuk geerate pdf -->
          <a class="ui orange button popup-trigger" data-content="Download Surat Permohonan" href="Pendaftaran/permohonan" target="_blank"><i class="download icon"></i>Download Surat Permohonan</a>
          <!-- List file peserta-->
          <div class="ui tiny list ">
          <?php foreach($peserta_data_surat_permohonan as $index => $surat_permohonan): ?>
            <div class="item">
              <a class="header" href="<?php echo $surat_permohonan['file_url']; ?>" target="_blank">Versi <?php echo $index+1;?></a>
              <?php echo strftime("%d %B %Y %H:%M", strtotime($surat_permohonan['pspp_date']));?>
            </div>
          <?php endforeach; ?>
          </div>
          <input type="file" name="surat_permohonan" />
          <div class="ui list hint">
            <div class="item">Tipe file pdf maks 1MB</div>
            <div class="item">Peserta dapat melakukan upload ulang file</div>
          </div>
        </div>
        <div class="required field <?php echo (isset($submit_errors['surat_penerimaan']) || form_error('surat_penerimaan'))?'error':''; ?>">
          <label>Surat Penerimaan Tempat PKL</label>
          <!-- List file peserta-->
          <div class="ui tiny list ">
          <?php foreach($peserta_data_surat_penerimaan as $index => $surat_penerimaan): ?>
            <div class="item">
              <a class="header" href="<?php echo $surat_penerimaan['file_url']; ?>" target="_blank">Versi <?php echo $index+1;?></a>
              <?php echo strftime("%d %B %Y %H:%M", strtotime($surat_penerimaan['pspt_date']));?>
            </div>
          <?php endforeach; ?>
          </div>
          <input type="file" name="surat_penerimaan" />
          <div class="ui list hint">
            <div class="item">Tipe file pdf maks 1MB</div>
            <div class="item">Peserta dapat melakukan upload ulang file</div>
          </div>
        </div>
        
        <button class="ui primary button" name="submit" value="submit" type="submit">Submit</button>
      </form>
    </div>
  </div>

  <script src="<?php echo base_url(); ?>assets/library/lodash.js"></script>
  <script type="text/javascript">
  $(document).ready(function(){
    // Dropdown.
    $('.ui.dropdown')
      .dropdown({
        fullTextSearch: true
      })
    ;

    // Dismissable message.
    $('.message .close')
      .on('click', function() {
        $(this)
          .closest('.message')
          .transition('fade')
        ;
      })
    ;

    // Ketika nilai dropdown periode berubah, update pilihan dosen pembimbing.
    $('.ui.dropdown.periode')
      .dropdown({
        fullTextSearch: true,
        onChange: function(value, text){
          updateDropdownPembimbing(value, text);
        }
      })
    ;
  });

  function updateDropdownPembimbing(periode_id, periode_nama){
    $.ajax({
      method: 'GET',
      url: '<?php echo site_url('mahasiswa/pendaftaran/get_dropdown');?>/' + periode_id
    })
    .done(function(data, textStatus, jqXHR) {
      if (data.message === 'Ok'){
        try {
          // Update pilihan dropdown pembimbing.
          var initialValue = $('.ui.dropdown.pembimbing').dropdown('get value');
          var pembimbingItems = '';
          var options = _.each(data.result.pembimbing, function(value){
            var statusKapasitas = (value.bimbingan >= value.dos_kapasitas_mhs) ? '(kapasitas penuh)' : '';
            pembimbingItems += '<div class="item" data-value="'+value.dos_id+'">'+statusKapasitas+' '+value.dos_nama+'</div>';
          });
          $('.ui.dropdown.pembimbing .menu').html(pembimbingItems);
          $('.ui.dropdown.pembimbing').dropdown('refresh');
          $('.ui.dropdown.pembimbing').dropdown('clear');
          $('.ui.dropdown.pembimbing').dropdown('set selected', initialValue);

          // Update pilihan dropdown tempat PKL.
          var initialValue = $('.ui.dropdown.tempat').dropdown('get value');
          var tempatItems = '';
          var options = _.each(data.result.tempat, function(value){
            tempatItems += '<div class="item" data-value="'+value.tem_id+'">'+value.tem_nama+'</div>';
          });
          $('.ui.dropdown.tempat .menu').html(tempatItems);
          $('.ui.dropdown.tempat').dropdown('refresh');
          $('.ui.dropdown.tempat').dropdown('clear');
          $('.ui.dropdown.tempat').dropdown('set selected', initialValue);

          // Mengubah nama periode pada hint di dropdown tempat PKL dan dosen pembimbing.
          $('.periode-hint').html('pada periode '+periode_nama);
        } catch(e){
          console.error('Terjadi kegagalan komunikasi ke server');  
        }
      } else {
        console.error('Terjadi kegagalan komunikasi ke server');  
      }     
    })
    .fail(function(jqXHR, textStatus, errorThrown){
      try{
        console.error(jqXHR.responseJSON.message);
      } catch (e){
        console.error('Terjadi kegagalan komunikasi ke server');  
      };
    });
  }
  </script>
