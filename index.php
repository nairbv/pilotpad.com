<?php 

session_start();

require_once('openIDUser.php');

$query_params = '';
if(isset($_REQUEST['facebook_app'])){
    $query_params="facebook_app=true";
}

if( empty( $_SESSION['user'] ) ) {
    header('Location: /login.php?'.$query_params);
    exit;
} else {
    $user = unserialize($_SESSION['user']);
    $filename = urlencode($user->id);
    $filename = '/var/www/paddata/'.$filename.'.html';
}
if( isset( $_REQUEST['content']) || isset($_REQUEST['editor_id'] ) ) {
    if( ! isset($_REQUEST['content'] ) ) {
        $content = $_REQUEST['editor_id'];
    } else {
        $content = $_REQUEST['content'];
    }
    if( strlen($content) > 5242880 ) {
        $error = 'Content must be less than 5MB!';
    } else {
        //$content =  mb_convert_encoding($content, 'UTF-8', 'HTML-ENTITIES');
        file_put_contents($filename,$content);
    }
} else {
    if( file_exists($filename) ) {
        $content = file_get_contents($filename);
    } else {
        $content = 'Hello '. $user->name .', '. file_get_contents('description.php');
    }
}
//avoid sending more than necessary in ajax requests.
if( isset($_REQUEST['ajax'] ) ) {
    exit;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr">

<head>

<title>PilotPad.com -- The simplest way to save a private note to the cloud.</title>

<meta http-equiv="Content-Type" content="text/html; charset=<?php if(defined('DEFAULT_CHARSET')) { echo DEFAULT_CHARSET; } else { echo 'utf-8'; }?>" />


<!--
<script src="http://connect.facebook.net/en_US/all.js#xfbml=1"></script>
-->
<script type="text/javascript" src="/jquery.js" ></script>
<!--
<script type="text/javascript" src="/jscripts/tiny_mce/tiny_mce.js" ></script>
-->

<script type="text/javascript" src="/ckeditor/ckeditor.js"></script>
<script type="text/javascript" src="/ckeditor/adapters/jquery.js"></script>

<link rel="stylesheet" type="text/css" href="main.css"/>

<style type="text/css">
.cke_skin_kama .cke_button_Logout span.cke_icon{ width:60px; }/*display:none !important;}*/
/*.cke_skin_kama .cke_button_Logout span.cke_label{display:inline;}*/
</style>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-23883084-1']);
  _gaq.push(['_trackPageview','/index']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>
<script type="text/javascript">

window.onbeforeunload = function() {
    editor = CKEDITOR.instances.editor_id;
    if( editor.checkDirty() ) {
        return "Don't you want to save before leaving?.";
    }
}

</script>
<?php
    if( isset($_REQUEST['facebook_app']) ) {
?>
<script type="text/javascript" src="ckeditor/config_fb.js"></script>
<?php
    }
?>
</head>
<body >
<div style="width: 98%; height: 100%; margin: 2px 0; padding:1%; ">
  
  <form method="post" onsubmit="javascript:saveit('editor_id');return false;" action="javascript:saveit('editor_id');">  

    <div id="message" style="width:140px; background-color: lightblue;position:fixed;z-index:500000;right:5px;"></div>
    <textarea cols="100" rows="35" 
              name="content" 
              class="content"
              id="editor_id">
              <?php 
              echo 
                        htmlentities($content,ENT_COMPAT,'UTF-8');
              ?>
    </textarea>
    <input type="submit" name="Save" value="Save"></input>
  </form>
</div>
<script type="text/javascript">
$(document).ready(function(){
<?php
    if( isset($_REQUEST['facebook_app']) ) {
        echo "CKEDITOR.config.customConfig='ckeditor/config_fb.js';";
    }
?>
    $("#editor_id").ckeditor();

    CKEDITOR.on('instanceReady',
      function( evt )
      {
         var editor = evt.editor;
         editor.execCommand('maximize');
      });
});
</script>
<script type="text/javascript">

var saving=false;

$(document).ready(function() {
    $(function() {
        // Here we have the auto_save() function run every 10 secs
        // We also pass the argument 'editor_id' which is the ID for the textarea tag
        setInterval("auto_save('editor_id')",5000);
    });
});
function saveit(editor_id) {
    if( saving ) {
        return ;
    }
    saving=true;
    $('#message').html('Saving');
    editor = CKEDITOR.instances.editor_id;
    var content = editor.getData();
    //var notDirty = tinyMCE.get(editor_id);
    content = encodeURIComponent(content);
    // We then start our jQuery AJAX function
    $.ajax({
        url: "/", // the path/name that will process our request
        type: "POST", 
        data: "ajax=true&content=" + content, 
        success: function(msg) {
            //alert(msg);
            // Here we reset the editor's changed (dirty) status
            // This prevents the editor from performing another auto-save
            // until more changes are made
            //notDirty.isNotDirty = true;
            
            editor.resetDirty();
            saving=false;
            setTimeout( "$('#message').html('');",200);
        },
        error: function(xhr,text,err) {
            if( xhr.status == 403 ) {
                console.log("blocked by mod_evasive?");
                $("#message").html("Saving too rapidly");
                setTimeout("resetSaver();",1000);
                setTimeout( "$('#message').html('');",1100);
                return;
            } else {
                $("#message").html(text + ":" +err);
            }
            saving=false;
        }
    });
}
function resetSaver() {
    saving = false;
}

function auto_save(editor_id) {
    // First we check if any changes have been made to the editor window
    editor = CKEDITOR.instances.editor_id;
    if( editor.checkDirty() ) {
        saveit(editor_id);
    } else {
        return false;
    }
}

</script>
<!--logout_small.gif -->
<a href="/logout.php"><img alt="logout" src="ckeditor/plugins/Logout/logout.gif"/></a>

<br/>
<br/>



<br/>
<br/>

</body>
</html>



