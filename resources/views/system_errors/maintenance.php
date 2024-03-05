<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="shortcut icon" type="image/png" href="<?php echo $this->_base??'/';?>favicon.png">
    <title>Maintenance Mood</title>
</head>
<body>
	<h1>Maintenance Going On</h1>
  <?php 
  $currentTimezone = date_default_timezone_get();
  echo date('Y-m-d H:i:s');
  echo "<br/>Current Timezone: $currentTimezone";
  ?>
  </body>
</html>
