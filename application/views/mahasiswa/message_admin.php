<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pesan dari admin untuk peserta.
 */
if ($peserta_data['pes_pesan_admin']):?>
  
    <div class="ui warning message">
      <div class="header">Pesan Administrator</div>
      <p><?php echo $peserta_data['pes_pesan_admin'];?></p>
    </div>

<?php endif;?>