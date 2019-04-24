<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>  <div class="ui inverted vertical footer segment">
    <div class="ui center aligned container">
      <!-- <div class="ui stackable inverted divided grid">
        <div class="three wide column">
          <h4 class="ui inverted header">Group 1</h4>
          <div class="ui inverted link list">
            <a href="#" class="item">Link One</a>
            <a href="#" class="item">Link Two</a>
            <a href="#" class="item">Link Three</a>
            <a href="#" class="item">Link Four</a>
          </div>
        </div>
        <div class="three wide column">
          <h4 class="ui inverted header">Group 2</h4>
          <div class="ui inverted link list">
            <a href="#" class="item">Link One</a>
            <a href="#" class="item">Link Two</a>
            <a href="#" class="item">Link Three</a>
            <a href="#" class="item">Link Four</a>
          </div>
        </div>
        <div class="three wide column">
          <h4 class="ui inverted header">Group 3</h4>
          <div class="ui inverted link list">
            <a href="#" class="item">Link One</a>
            <a href="#" class="item">Link Two</a>
            <a href="#" class="item">Link Three</a>
            <a href="#" class="item">Link Four</a>
          </div>
        </div>
        <div class="seven wide column">
          <i class="bordered inverted blue send icon"></i>
          <h4 class="ui inverted header">CS PKL <div class="sub header">Sistem Informasi Praktik Kerja Lapangan</div></h4>
          <p>Jurusan Ilmu Komputer<br/>Fakultas Matematika dan Ilmu Pengetahuan Alam<br/>Universitas Udayana</p>
        </div>
      </div> -->
      <!-- <div class="ui inverted section divider"></div>
      <img src="assets/images/logo.png" class="ui centered mini image">
      <div class="ui horizontal inverted small divided link list">
        <a class="item" href="#">Site Map</a>
        <a class="item" href="#">Contact Us</a>
        <a class="item" href="#">Terms and Conditions</a>
        <a class="item" href="#">Privacy Policy</a>
      </div> 
      <i class="bordered inverted blue send icon logo"></i> -->
      <img class="ui tiny logo image" src="<?php echo base_url(); ?>assets/images/unud.png" alt="Logo Universitas Udayana"/>
      <h4 class="ui inverted header logo">CS PKL <div class="sub header">Sistem Informasi Praktek Kerja Lapangan</div> <div class="sub header">v 0.4.1 Beta</div></h4>
      <p>Jurusan Ilmu Komputer<br/>Fakultas Matematika dan Ilmu Pengetahuan Alam<br/>Universitas Udayana</p>
    </div>
  </div>  
  <script src="<?php echo base_url(); ?>assets/semantic/semantic.min.js"></script>

<?php if (ENVIRONMENT==='production'):?>
  <script src="<?php echo base_url(); ?>assets/js/bundle.min.js"></script>
<?php else:?>
  <script src="<?php echo base_url(); ?>assets/js/bundle.js"></script>
<?php endif;?>

</body>

</html>