<?php
include('pageContent.php');
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
 <head>
  <title>How to Help - The Valar Project</title>
  <?php head(); ?>
  <style>
#paypalDonate {
 padding: 5px;
}
  </style>
 </head>
 <body>
  <?php bodyStart(); ?>
  <p>This website is still under construction and this page is not available. Please be patient while we work on getting everything online. Thank you for your patience.</p>
  <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
   <input type="hidden" name="cmd" value="_s-xclick">
   <input type="hidden" name="hosted_button_id" value="GACJUSR8788C8">
   <div class="button-wrapper">
    <input id="paypalDonate" type="image" src="http://tvp.elementfx.com/resources/images/donation-buttons/paypal.png" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
   </div>
   <img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
  </form>
  <?php bodyEnd(); ?>
 </body>
</html>