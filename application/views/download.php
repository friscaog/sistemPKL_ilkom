<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><body class="download">

<?php $this->load->view('templates/menu'); ?>

  <div class="ui basic top vertical segment">
    <div class="ui main text container">
      <?php foreach($berkas_data as $berkas): ?>
        <div class="ui vertical segment article">
          <h1 class="ui header"><?php echo $berkas['ber_nama'];?></h1>
          <div class="ui horizontal small divided link list meta">
            <span class="item"><i class="wait icon"></i><?php echo $berkas['ber_tgl_publikasi'];?></span>
          </div>    
          <div> 
            <a class="ui orange button" href="<?php echo $berkas['files'][count($berkas['files'])-1]['bf_file'];?>">
              <i class="download icon"></i> Download
            </a>
          </div>
        </div>
      <?php endforeach;?>
      
      <!--<div class="ui vertical segment article">
        <h1 class="ui header">Ketentuan PKL Periode VII</h1>
        <div class="ui horizontal small divided link list meta">
          <span class="item"><i class="wait icon"></i>29 Maret 2016 15:13</span>
          <span class="item"><i class="download icon"></i>27 download</span>
          <span class="item"><i class="write icon"></i>1 revisi</span>
        </div>     
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam in suscipit ante, ut cursus diam. Sed et fermentum mi, sed semper tellus. Etiam convallis pharetra libero, varius vestibulum magna tempor vel. Aenean convallis velit gravida quam lobortis, id finibus sapien tempor.</p>
        <button class="ui orange button">
          <i class="download icon"></i> Download
        </button>
      </div>
      <div class="ui vertical segment article">
        <h1 class="ui header">Ketentuan PKL Periode VII</h1>
        <div class="ui horizontal small divided link list meta">
          <span class="item"><i class="wait icon"></i>29 Maret 2016 15:13</span>
          <span class="item"><i class="download icon"></i>27 download</span>
          <span class="item"><i class="write icon"></i>1 revisi</span>
        </div>     
        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aliquam in suscipit ante, ut cursus diam. Sed et fermentum mi, sed semper tellus. Etiam convallis pharetra libero, varius vestibulum magna tempor vel. Aenean convallis velit gravida quam lobortis, id finibus sapien tempor.</p>
        <button class="ui orange button">
          <i class="download icon"></i> Download
        </button>
      </div>-->

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
  