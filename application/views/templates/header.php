<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html>
<head>
  <!-- Standard Meta -->
  <meta charset="utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">

  <!-- Site Properties -->
  <title><?php echo $title; ?> - CS PKL</title>

  <link rel="shortcut icon" href="<?php echo base_url();?>favicon.ico" type="image/x-icon">
  <link rel="icon" href="<?php echo base_url();?>favicon.ico" type="image/x-icon">

  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/semantic/semantic.min.css">

<?php if (ENVIRONMENT==='production'):?>
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/css/cs-pkl.min.css" />
<?php else:?>
  <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>assets/css/cs-pkl.css" />
<?php endif;?>

  <script src="<?php echo base_url(); ?>assets/library/jquery.min.js"></script>  

</head>
