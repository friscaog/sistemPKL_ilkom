<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Cek step aktif.
$step0_active = '';
$step1_active = '';
$step2_active = '';
$step3_active = '';
$step4_active = '';
switch ($tahapan) 
{
  case 0:
    $step0_active = 'active';
    break;
  case 1:
    $step1_active = 'active';
    break;
  case 2:
    $step2_active = 'active';
    break;
  case 3:
    $step3_active = 'active';
    break;
  case 4:
    $step4_active = 'active';
    break;
  default:
    $step0_active = 'active';
    break;
}

// Cek step disabled.
$step1_disabled = '';
$step2_disabled = '';
$step3_disabled = '';
$step4_disabled = '';
switch ($maks_tahapan) 
{
  default:
  case 0:
    $step1_disabled = 'disabled';
  case 1:
    $step2_disabled = 'disabled';
  case 2:
    $step3_disabled = 'disabled';
  case 3:
    $step4_disabled = 'disabled';
  case 4:
    break;
}

?>  <!-- Steps -->
  <div class="ui basic top vertical segment">
    <div class="ui container">
      <div class="ui fluid small steps">
        <a class="<?php echo $step0_active;?> step" href="<?php echo site_url('mahasiswa/pendaftaran');?>">
          <i class="file text icon"></i>
          <div class="content">
            <div class="title">Pendaftaran</div>
            <div class="description">Permohonan pendaftaran PKL</div>
          </div>
        </a>
        <a class="<?php echo $step1_active;?> <?php echo $step1_disabled;?> step" href="<?php echo site_url('mahasiswa/pelaksanaan');?>">
          <i class="rocket icon"></i>
          <div class="content">
            <div class="title">Pelaksanaan</div>
            <div class="description">Pelaksanaan kegiatan harian PKL</div>
          </div>
        </a>
        <a class="<?php echo $step2_active;?> <?php echo $step2_disabled;?> step" href="<?php echo site_url('mahasiswa/pasca_pkl');?>">
          <i class="legal icon"></i>
          <div class="content">
            <div class="title">Pasca PKL</div>
            <div class="description">Persiapan ujian PKL</div>
          </div>
        </a>
        <a class="<?php echo $step3_active;?> <?php echo $step3_disabled;?> step" href="<?php echo site_url('mahasiswa/pasca_ujian');?>">
          <i class="book icon"></i>
          <div class="content">
            <div class="title">Pasca Ujian</div>
            <div class="description">Pengumpulan laporan akhir</div>
          </div>
        </a>
        <a class="<?php echo $step4_active;?> <?php echo $step4_disabled;?> step" href="<?php echo site_url('mahasiswa/selesai');?>">
          <i class="star icon"></i>
          <div class="content">
            <div class="title">Selesai</div>
          </div>
        </a>
      </div>
    </div>
  </div>
