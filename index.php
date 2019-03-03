<?php
    setlocale (LC_ALL, 'en_US.UTF-8');

    include 'php/http.php';
    include 'php/xhtml.php';
    include 'php/crockford32.php';
    include 'cfg.php';
    
    $mysqli = new mysqli (cfg::localsql, cfg::username, cfg::password, cfg::database);
    if ($mysqli->connect_errno) {
        echo "DB CONNECT ERROR: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        exit;
    }
    
    session_start ();
    
    if (isset ($_COOKIE['identity'])) {
        $identity = $_COOKIE['identity'];
    } else {
        $identity = openssl_random_pseudo_bytes (10);
        setcookie ('identity', $identity, time() + (10 * 365 * 24 * 60 * 60));
    }
    
    if (isset ($_COOKIE['peer'])) {
        $peer = $_COOKIE['peer'];
    } else {
        $peer = '';
    }

    header ('content-type: '.xhtml::content_type().'; charset=utf-8');
    header ("cache-control: no-cache, must-revalidate");
    header ("pragma: no-cache");

    if (xhtml::supported ())
        echo '<?xml version="1.0" encoding="utf-8"?'.">\n";
?>
<!DOCTYPE html PUBLIC
          "-//W3C//DTD XHTML 1.1//EN"
          "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<!-- saved from url=<?php printf("(%04d)%s", strlen(xhtml::$location), xhtml::$location); ?> -->
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="content-language"     content="en" />
    <meta http-equiv="content-style-type"   content="text/css" />
    <meta http-equiv="content-script-type"  content="text/javascript" />
    <meta http-equiv="content-type"         content="application/xhtml+xml; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible"      content="IE=edge" />
    
    <meta name="viewport"   content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no" />
    <meta name="author"     content="RADDI.net Developer" />
    <meta name="requested"  content="<?php echo date('Y-m-d H:i:s'); ?>" scheme="Y-m-d H:i:s" />
    <meta name="language"   content="en" />

<!--[if lt IE 9]>
    <script src="<?php echo xhtml::$baseref; ?>js/jquery-1.12.4.min.js" type="text/javascript"></script>
<![endif]-->
<!--[if gte IE 9]><!-->
    <script src="<?php echo xhtml::$baseref; ?>js/jquery-3.3.1.min.js" type="text/javascript"></script>
<!--<![endif]-->
    <script src="<?php echo xhtml::$baseref; ?>js/js.cookie.js" type="text/javascript"></script>

    <title>EPHEMERA on RADDI.net</title>

<!-- TODO: comm indicator -->
<!-- TODO: on desktop add left/right padding -->
<!-- TODO: on mobile text on newline? -->

<style type="text/css">
html, body, table {
    height:     100%;
}
html {
    overflow:   auto;
}
body {
    margin:     0;
    padding:    0;
    cursor:     default;
}
body, div, input {
    font-family: monospace;
}
body, div {
    position:   relative;
    display:    block;
}
table {
    width:  100%;
}
#received {
    overflow-y: scroll;
    vertical-align: bottom;
    max-height: 100%;
}
#received, #input {
    padding: 1eM;
}
tfoot {
    background: #09f;
}
.time {
    color: #09f;
}
.from {
    font-weight: bold;
}
.outbound {
    color: #aaa;
}
</style>

<script type="text/javascript">
//<![CDATA[
$(document).ready(function () {
    heartbeat ();
    setInterval (heartbeat, 1000);
    return;
});
function heartbeat() {
    $.ajax({
        type: "GET",
        url: "ops.php",
        cache: false,
        timeout: 999,
        data: {
            'user': '<?php echo crockford32_encode ($identity); ?>', 
        },
        success: function (html) {
            // TODO: don't change body if page hash stays the same
            $("#received").html (html);
            $('#received').scrollTop($('#received')[0].scrollHeight - $('#received')[0].clientHeight);
        }
    });
};
function send() {
    $('#submit').prop('disabled', true);
    $('#text').prop('disabled', true);
    $('#peer').prop('disabled', true);
    $('#time').prop('disabled', true);

    Cookies.set('peer',$('#peer').val());
    Cookies.set('ttl',$('#time').val());

    $.ajax({
        type: "POST",
        url: "ops.php",
        cache: false,
        timeout: 9999,
        data: {
            'action': 'send',
            'user': '<?php echo crockford32_encode ($identity); ?>', 
            'peer': $('#peer').val(),
            'text': $('#text').val(),
            'time': $('#time').val()
        },
        success: function (html) {
            $('#peer').prop('disabled', false);
            $('#text').prop('disabled', false);
            $('#time').prop('disabled', false);
            $('#submit').prop('disabled', false);
            
            $("#text").val ('');
            $("#text").focus ();
        }
    });
}
//]]>
</script>

</head>
<body>

<table cellspacing="0" cellpadding="0">
<tbody>
<tr>
    <td style="vertical-align: bottom; border-bottom: 1px solid black;">
    <div id="received">
        ...
    </div>
    </td>
</tr>
</tbody>
<tfoot>
<tr>
    <td id="input">
    <b><?php echo crockford32_encode (substr ($identity, 0, 5)); ?></b>
        <label for="peer">TO:</label>
        <input type="text" name="peer" id="peer" maxlength="8" style="width: 5eM" placeholder="recipient" value="<?php if (isset ($_COOKIE['peer'])) echo $_COOKIE['peer']; ?>" />
        <label for="time">TTL:</label>
        <input type="number" name="time" id="time" maxlength="6" max="525600" style="width: 4eM" value="<?php if (isset ($_COOKIE['ttl'])) echo $_COOKIE['ttl']; else echo "2880"; ?>" />&nbsp;min
        <input type="text" name="text" id="text" style="width: 99%; margin-top: 0.5eM; margin-bottom: 0.5eM;" onkeypress="if (event.keyCode == 13) send ();" />
        <input type="button" value="SEND" id="submit" onclick="send();" />
    </td>
</tr>
</tfoot>
</table>
</body>
</html>
