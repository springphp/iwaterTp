{__NOLAYOUT__}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>跳转提示</title>
    <style type="text/css">
        *{ padding: 0; margin: 0; }
        body{ background: #fff; font-family: "Microsoft Yahei","Helvetica Neue",Helvetica,Arial,sans-serif; color: #333; font-size: 16px; }
        .system-message{ padding: 24px 48px; }
        .system-message h1{ font-size: 100px; font-weight: normal; line-height: 120px; margin-bottom: 12px; }
        .system-message .jump{ padding-top: 10px; }
        .system-message .jump a{ color: #333; }
        .system-message .success,.system-message .error{ line-height: 1.8em; font-size: 36px; }
        .system-message .detail{ font-size: 12px; line-height: 20px; margin-top: 12px; display: none; }
    </style>
</head>
<body>
    <div class="system-message" style="text-align: center;margin:210px auto;border:1px solid #eee;">
        <?php switch ($code) {  ?>
            <?php case 1:?>
            <p class="success"  style="font-size:24px;color:#999;"><?php echo(strip_tags($msg));?></p>
            <?php break;?>
            <?php case 0:?>
            <p class="error"    style="font-size:24px;color:#999;"><?php echo(strip_tags($msg));?></p>
            <?php break;?>
        <?php } ?>
        <p class="detail"></p>
        <p class="jump" <?php if($url==0){echo "style='display:none;'";} ?>>
            页面自动 <a id="href" href="<?php echo($url);?>" style="color:#600;text-decoration: none;">跳转</a> 等待时间： <b id="wait"><?php echo($wait);?></b>
        </p>
    </div>
    <script type="text/javascript">
        (function(){
            var wait = document.getElementById('wait'),
                href = document.getElementById('href').getAttribute('href');
            var interval = setInterval(function(){
                var time = --wait.innerHTML;
                if(time <= 0) {
                    if (href == 0 || href == '0') {
                        parent.layer && parent.layer.getFrameIndex(window.name) && parent.layer.closeAll('iframe');
                    } else {
                        location.href = href;
                    }
                    clearInterval(interval);
                };
            }, 1000);
        })();
    </script>
</body>
</html>