<?php

session_start();

function get_scheme() {
    $scheme = 'http';
    if( !empty($_SERVER['HTTPS'] ) ) {
        $scheme .= 's';
    }
    return $scheme;
}

$query_params = '';
$application=false;
if(isset($_REQUEST['facebook_app'])){
    $application=true;
    $query_params="facebook_app=true";
}
$facebook_app_page = get_scheme().'://apps.facebook.com/pilotpad/';

if( ! empty( $_SESSION['user'] ) ) {
    header('Location: /?'.$query_params);
    exit;
}

$edit_url = get_scheme().'://'.$_SERVER['SERVER_NAME'].'/?'.$query_params;
if( $application ) {
    $edit_url=$facebook_app_page;
}

if( isset($_REQUEST['login']) && $_REQUEST['login']=='facebook') {
    $app_id = "157974860936709";
    $settings = parse_ini_file("pilotpad.ini");
    $app_secret = $settings['facebook.app_secret'];

    $my_url = get_scheme().'://'.$_SERVER['SERVER_NAME'].'/login.php?login=facebook&'.$query_params;
    if( $application ) {
        $my_url = $facebook_app_page;
    }
    $dialog_base_url = 'http://www.facebook.com/dialog/oauth';
    $token_base_url = 'https://graph.facebook.com/oauth/access_token';
}

if( isset($app_id ) ) {
    $code = null;
    if( isset( $_REQUEST['code'] ) ) {
        $code = $_REQUEST["code"];
    }

    if(empty($code)) {
        $_SESSION['state'] = md5(uniqid(rand(), TRUE)); //CSRF protection
        $dialog_url = $dialog_base_url . "?client_id="
            . $app_id . "&redirect_uri=" . urlencode($my_url) . "&state="
            . $_SESSION['state'];

        echo("<script> top.location.href='" . $dialog_url . "'</script>");
        exit;
    }

    if(isset($_REQUEST['state']) && isset($_SESSION['state']) && $_REQUEST['state'] == $_SESSION['state']) {
        $token_url = $token_base_url 
            . "?client_id=" . $app_id . "&redirect_uri=" . urlencode($my_url)
            . "&client_secret=" . $app_secret . "&code=" . $code;

        $response = file_get_contents($token_url);
        $params = null;
        parse_str($response, $params);

        $graph_url = "https://graph.facebook.com/me?access_token="
           . $params['access_token'];

        $user = json_decode(file_get_contents($graph_url));
        $_SESSION['user'] = serialize($user);
        //login OK, redirect to main page.
//        header('Location: /');
        echo("<script> top.location.href='".$edit_url."'</script>");
    } else {
        echo("The state does not match. You may be a victim of CSRF.");
    }
} else {

require_once('common.php');

//login forms to post to self.
?>
<html xmlns="http://www.w3.org/1999/xhtml" 
      xmlns:og="http://ogp.me/ns#"
      xmlns:fb="http://www.facebook.com/2008/fbml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

<link rel="stylesheet" type="text/css" href="main.css"/>
<title>PilotPad.com -- The simplest way to save a private note to the cloud.</title>
<meta property="og:title" content="PilotPad.com"/>

<meta property="og:type" content="website"/>

<meta property="og:url" content="<?php echo get_scheme();?>://pilotpad.com/"/>
<meta property="og:image" content="http://pilotpad.com/pilotpad.gif"/>

<meta property="og:site_name" content="PilotPad"/>
<meta property="fb:app_id" content="157974860936709"/>

<meta property="fb:admins" content="513714656" />

<meta name="keywords" content="notes,notepad,notepad.cc,scratchpad,cloud pad,cloud,note taking,记事本,笔记本"/>

<meta property="description" content="Do you ever email something to yourself? Open a draft email just to copy down some links or a quote you see? Try Pilotpad instead.  Think of it as an extension to cut/paste, an extra clipboard, a simple place to save a note for later."/>
<link href="./css/redmond/jquery-ui-1.8.13.custom.css" rel="stylesheet" type="text/css"/>

<script type="text/javascript" src="/js/jquery-1.5.1.min.js"></script>
<script type="text/javascript" src="/js/jquery-ui-1.8.13.custom.min.js" ></script>
<script type="text/javascript">
$(document).ready(function() {
    $("#tabs").tabs({
            ajaxOptions: {
                error: function( xhr, status, index, anchor ) {
                    $( anchor.hash ).html(
                        "Couldn't load this tab. We'll try to fix this as soon as possible. ");
                }
            }
    });
});
</script>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-23883084-1']);
  _gaq.push(['_trackPageview','/login']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>


</head>
<body>
<div class="about" >
  <div id="tabs">
    <ul>
    <?php if(!$application) { ?>
      <li><a href="#tab-1">Edit your Pilotpad</a></li>
    <?php }?>
      <li><a href="about.php">About</a></li>
      <li><a href="faq.html">FAQ</a></li>
      <li><a href="screenshot.html">Screenshot</a></li>
    </ul>

<?php if(!$application) {?>
<div id="tab-1">
  <h3>Edit your PilotPad:</h3>
  <div class="left-auth">
    <br/>
    <a href="/try_auth.php?action=verify&openid_identifier=yahoo.com">
    <!--<img src="http://l.yimg.com/a/i/reg/openid/buttons/16_new.png"/>-->
      <img src="http://l.yimg.com/a/i/reg/openid/buttons/13.png"/>
    </a>
    <br/>
    <a href="/try_auth.php?action=verify&openid_identifier=https://www.google.com/accounts/o8/id"><img src="googleLoginButton.png"/></a>
    <br/>
    <a href="/login.php?login=facebook&<?php echo $query_params;?>" class="fb_button fb_button_medium"><span class="fb_button_text">Login with Facebook</span></a>
    <br/><br/>
    Click <a href="http://apps.facebook.com/pilotpad/">Here</a> to use on Facebook as a Facebook Application.
  </div>
  <div class="verify-form" >
      <form method="get" action="try_auth.php">
        <div>
            <img src="logo_openid.png" alt="OpenID"/>
            <input type="hidden" name="action" value="verify" /><br/>
            <input type="text" name="openid_identifier" id="identifier" value=""/>
<!--        <input type="submit" value="Verify"/>-->
        </div>

      </form>
  </div>
  <div class="recommend">
  <hr/>
  <h3>Recommend PilotPad:</h3>
  <div id="fb-root">
  </div>

   <script src="http://connect.facebook.net/en_US/all.js"></script>
   <script>
     FB.init({ 
        appId:'157974860936709', cookie:true, 
        status:true, xfbml:true 
     });
  </script>

  <fb:like href="http://pilotpad.com/" send="true" width="450" show_faces="false" font="">
  </fb:like>
  <br/>
  <!-- Place this tag where you want the +1 button to render -->
  <g:plusone></g:plusone>

  <!-- Place this tag in your head or just before your close body tag -->
  <script type="text/javascript" src="http://apis.google.com/js/plusone.js"></script>
  <br/>
  <a href="http://twitter.com/share" class="twitter-share-button" data-url="http://pilotpad.com" data-count="horizontal">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script>

  <br/><br/><br/>
  </div>

</div>
<?php }?>



  </div>
</div>

<br/>

<br/>
<br/>
<br/>
<br/>
<br/>
<br/>
</body>
</html>
<?php
}

