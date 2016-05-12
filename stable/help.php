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
  <p>There are many ways for you to help us out. Any of the things below that you can assist us with would be greatly appreciated. If you have suggestions for a different way you can assist us, we will gladly listen to your suggestion.</p>
  <h1>Building</h1>
  <p>There are many places in Middle Earth that we still have to build. If you like to design things in minecraft, this is for you. Ask a Maia or Valar on our server for a job.</p>
  <h1>Modding</h1>
  <p>If you are a skilled Java programmer, the Awaken Dreams Mod would appreciate your help. If you know web development, we would love some help with the website. If you cannot program, you must be willing to learn completely on your own. Our modding team does not have time to teach you java or general Minecraft modding. Our mod is not a forge mod and you should be willing to mod vanilla Minecraft if you want to contribute to the Awaken Dreams Mod. Talk to scribblemaniac for more information and tasks.</p>
  <h1>Specific Positions</h1>
  <p>We are looking to fill a few positions in our project. Most of these positions will only be given to high ranking members of our project. These include:<p>
  <ul>
   <li>Public relations. People in this position will be in charge of managing our social media accounts and assisting people on our forum with basic things)</li>
   <li>Quest writer. We will need a few people to build new quests for people to do.</li>
  </ul>
  <h1>Translations</h1>
  <p>We would love to translate the Awaken Dreams Mod and our website into different languages. If you would like to translate our project into a langauge you know we would be very thankful. We are currently working on a list of things to translate to make the process easier. Please be patient while we complete this.</p>
  <h1>Beta Testing</h1>
  <p>We are always working on new things and we will sometimes require people to test these things to assure that they are ready to be released. Beta testers will test a great variety of things: builds, mods, plugins, etc. Talk to scribblemaniac to be added to the beta testing mailing list.</p>
  <h1>Monetary Donations</h1>
  <p>If you want to give us a monetary donation, you can do so with the PayPal button below. At the moment we are thinking of rewarding donations with a RPG ladder. Larger donations will give you more rewards. There are currently three levels you can earn by donating. You can view the benefits of these ranks by hovering over Server in the navigation above and then selecting Ranks.</p>
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