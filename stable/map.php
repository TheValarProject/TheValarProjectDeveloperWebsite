<?php
include('pageContent.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title>Map - The Valar Project</title>
  <?php head(); ?>
 </head>
 <body>
  <?php bodyStart(); ?>
  <iframe src="http://<?php echo gethostbyname('tvp.squarechair.net') == $_SERVER['REMOTE_ADDR'] ? 'localhost' : "tvp.squarechair.net"; ?>:8123"></iframe>
  <?php bodyEnd(); ?>
 </body>
</html>