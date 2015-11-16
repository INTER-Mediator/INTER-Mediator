<?php
/*
 * Originally from http://www.johnboyproductions.com/php-upload-progress-bar/
 * Simplefied the above sample code by Masayuki Nii (msyk@msyk.net) for INTER-Mediator
 * Nov 4, 2013 - http://inter-mediator.org
 */
$url = basename($_SERVER['SCRIPT_FILENAME']);

//Get file upload progress information.
if (function_exists('apc_fetch') && isset($_GET['progress_key'])) {
    $status = apc_fetch('upload_'.$_GET['progress_key']);
    echo intval($status['current']) / intval($status['total']) * 100;
    die;
}
?>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.0/jquery.js" type="text/javascript"></script>
<style>
    #progress_container {
        width: 200px;
        height: 20px;
        border: 1px solid #CCCCCC;
        background-color:#EBEBEB;
        display: block;
        margin:5px 0px -15px 0px;
    }

    #progress_bar {
        position: relative;
        height: 20px;
        background-color: #cc9733;
        width: 0%;
        z-index:10;
    }

    #progress_completed {
        font-size:14px;
        z-index:40;
        line-height:20px;
        padding-left:4px;
        color:#FFFFFF;
    }
</style>

<script>
    $(document).ready(function() {
        setInterval(function()
        {
            $.get("<?php echo urlencode($url); ?>?progress_key=<?php echo isset($_GET['up_id']) ? urlencode($_GET['up_id']) : ''; ?>&randval="+ Math.random(), {},
                function(data)
                {
                    $('#progress_container').fadeIn(100);
                    $('#progress_bar').width(data +"%");
                    $('#progress_completed').html(parseInt(data) +"%");
                }
            )},500);
    });
</script>

<body style="margin:0px">
<!--Progress bar divs-->
<div id="progress_container">
    <div id="progress_bar">
        <div id="progress_completed"></div>
    </div>
</div>
</body>
