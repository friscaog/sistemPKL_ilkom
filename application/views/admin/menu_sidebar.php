<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!-- Vertical Menu -->
  <div class="three wide column">
    <div class="ui vertical fluid menu">          
      <a class="<?php echo ($page_admin === 'manage_admin')?'active':'';?> item" href="<?php echo site_url('admin/manage_admin');?>">
        Manage Admin
      </a>
      <a class="<?php echo ($page_admin === 'manage_tempat_pkl')?'active':'';?> item" href="<?php echo site_url('admin/manage_tempat_pkl');?>">
        Manage Tempat PKL
      </a>
      <a class="<?php echo ($page_admin === 'manage_dosen')?'active':'';?> item" href="<?php echo site_url('admin/manage_dosen');?>">
        Manage Dosen
      </a>
      <a class="<?php echo ($page_admin === 'manage_periode')?'active':'';?> item" href="<?php echo site_url('admin/manage_periode');?>">
        Manage Periode
      </a>
      <a class="<?php echo ($page_admin === 'manage_informasi')?'active':'';?> item" href="<?php echo site_url('admin/manage_informasi');?>">
        Manage Informasi
      </a>
      <a class="<?php echo ($page_admin === 'manage_berkas')?'active':'';?> item" href="<?php echo site_url('admin/manage_berkas');?>">
        Manage Berkas
      </a>
      <a class="<?php echo ($page_admin === 'peserta')?'active':'';?> item" href="<?php echo site_url('admin/peserta');?>">
        Peserta
      </a>
    </div>
  </div>
