<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><body class="login">
<div class="ui middle aligned center aligned grid">
  <div class="column">
    <h2 class="ui blue image header">
      <!--<i class="bordered inverted blue send icon"></i>-->
      <img class="ui tiny image" src="<?php echo base_url();?>assets/images/unud.png" alt="Logo Universitas Udayana"/>
      <br/>
      <div class="content">
        Silakan melakukan log-in
      </div>
    </h2>
    <!-- Container untuk React component LoginForm -->
    <div class="form-container"></div>
  </div>
</div>
<script src="<?php echo base_url(); ?>assets/library/jquery.min.js"></script>
<script src="<?php echo base_url(); ?>assets/semantic/semantic.min.js"></script>

<?php if (ENVIRONMENT==='production'):?>
  <!-- <script src="<?php echo base_url(); ?>assets/js/bundle.min.js"></script> -->
<?php else:?>
  <script src="<?php echo base_url(); ?>assets/js/bundle.js"></script>
<?php endif;?>

</body>
</html>
