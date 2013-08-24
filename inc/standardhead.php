<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
<meta name="viewport" content="width=device-width">

<link href="<?=$path?>img/icon/favicon.ico" rel="icon" type="image/x-icon" />

<link rel="stylesheet" href="<?=$path?>css/normalize.css">
<link rel="stylesheet" href="<?=$path?>css/main.css">
<link rel="stylesheet" href="<?=$path?>css/bootstrap.min.css"/>
<link rel="stylesheet" href="<?=$path?>css/styles.css" />

<script src="<?=$path?>js/vendor/modernizr-2.6.2.min.js"></script>
<!--[if (gte IE 6)&(lte IE 8)]>
  <script type="text/javascript" src="<?=$path?>js/vendor/selectivizr.js"></script>
  <noscript><link rel="stylesheet" href="<?=$path?>css/fallback.css" /></noscript>
<![endif]-->	

<script>
	var path = <?=$path?>;
	<?php if(isset($jsdata)): ?>
	var jsdata = <?= json_encode($jsdata) ?>;
	<?php endif; ?>
</script>