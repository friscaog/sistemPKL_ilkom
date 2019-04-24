<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><body class="homepage">

<?php $this->load->view('templates/menu'); ?>

  <div class="ui basic top vertical segment">
    <div class="ui main text container">
      <?php foreach($informasi_data as $value): ?>
        <div class="ui vertical segment article">
          <h1 class="ui header"><?php echo $value['inf_judul'];?></h1>
          <div class="ui horizontal small divided link list meta">
            <span class="item"><i class="wait icon"></i><?php echo $value['inf_tgl_publikasi'];?></span>
          </div>
          <?php echo $value['inf_konten'];?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <?php /* Jika terdapat tombol prev dan next, tombol next dibuat dengan float right.
           Hanya terdapat tombol next, tombol next dibuat dengan align right,  */ ?>
  <div class="ui basic vertical segment">
    <div class="ui text container pagination <?php echo ( ! $prev_url)?'right aligned':''; ?>">
      <?php if ($prev_url): ?>
        <a class="ui animated button" tabindex="0" href="<?php echo $prev_url;?>">
          <div class="visible content">Sebelumnya</div>
          <div class="hidden content">
            <i class="left arrow icon"></i>
          </div>
        </a>
      <?php endif;?>
      <?php if ($next_url): ?>
        <a class="ui <?php echo ($prev_url)?'right floated':''; ?> animated button" tabindex="0" href="<?php echo $next_url;?>">
          <div class="visible content">Selanjutnya</div>
          <div class="hidden content">
            <i class="right arrow icon"></i>
          </div>
        </a>
      <?php endif;?>
    </div>
  </div>
  