<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><body class="admin-manage-informasi">

<?php $this->load->view('templates/menu'); ?>

<!-- Main content -->
<div class="ui basic top main vertical segment">
	<div class="ui stackable divided grid container">

		<?php $this->load->view('admin/menu_sidebar') ?>

		<!-- Main content -->
		<div class="thirteen wide column main-content">
		</div>
	  
	</div>

</div>

<script src="<?php echo base_url();?>assets/library/tinymce/tinymce.min.js"></script>
