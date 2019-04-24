<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Tentukan URL profil akun.
$akun_profil_url = NULL;
switch ($this->session->jenis_akun) {
  case 'mahasiswa':
    $akun_profil_url = site_url('mahasiswa/profil');
    break;
  case 'dosen':
    $akun_profil_url = site_url('dosen/profil');
    break;
}

?>  <div class="ui fixed inverted menu">
    <div class="ui container">
      <a href="<?php echo site_url(); ?>" class="header item">
        <!--<i class="bordered inverted blue send icon"></i>-->
        <img class="ui logo image" src="<?php echo base_url();?>assets/images/unud.png" alt="Logo Universitas Udayana"/>
        <strong>CS PKL</strong>
      </a>
      <a href="<?php echo site_url(); ?>" class="<?php echo ($page === 'homepage') ? 'active' : ''; ?> item">Beranda</a>
      <a href="<?php echo site_url('download'); ?>" class="<?php echo ($page === 'download') ? 'active' : ''; ?> item">Download</a>

      <?php if ($this->session->jenis_akun === 'mahasiswa'): ?>
        <!-- Link akun mahasiswa -->
        <a href="<?php echo site_url('mahasiswa'); ?>" class="<?php echo ($page === 'mahasiswa') ? 'active' : ''; ?> blue item"><i class="user icon"></i>Mahasiswa</a>
      <?php endif; ?>

      <?php if ($this->session->jenis_akun === 'dosen'): ?>
        <!-- Link akun dosen -->
        <a href="<?php echo site_url('dosen'); ?>" class="<?php echo ($page === 'dosen') ? 'active' : ''; ?> blue item"><i class="user icon"></i>Dosen</a>
      <?php endif; ?>

      <?php if ($this->session->is_admin):?>
        <!-- Link akun admin -->
        <a href="<?php echo site_url('admin'); ?>" class="<?php echo ($page === 'admin') ? 'active' : ''; ?> orange item"><i class="spy icon"></i>Admin</a>
      <?php endif; ?>

      <?php if ($this->session->email): ?>
        <div class="ui simple dropdown right item">
          Halo, <?php echo ($this->session->nama)?$this->session->nama:$this->session->email; ?> <i class="dropdown icon"></i>
          <div class="menu">
            <?php if ($akun_profil_url): ?>
              <a class="<?php echo ($page === 'profil') ? 'active' : ''; ?> item" href="<?php echo $akun_profil_url;?>">Profil</a>
            <?php endif; ?>
            <a class="item" href="<?php echo site_url('logout');?>">Log-out</a>
          </div>
        </div>
      <?php else: ?>      
        <div class="right item">
          <a class="ui blue inverted button" href="<?php echo site_url('login')?>">Log-in</a>
        </div>
      <?php endif; ?>
      <!-- <div class="ui simple dropdown item">
        Dropdown <i class="dropdown icon"></i>
        <div class="menu">
          <a class="item" href="#">Link Item</a>
          <a class="item" href="#">Link Item</a>
          <div class="divider"></div>
          <div class="header">Header Item</div>
          <div class="item">
            <i class="dropdown icon"></i>
            Sub Menu
            <div class="menu">
              <a class="item" href="#">Link Item</a>
              <a class="item" href="#">Link Item</a>
            </div>
          </div>
          <a class="item" href="#">Link Item</a>
        </div>
      </div> -->
    </div>
  </div>
