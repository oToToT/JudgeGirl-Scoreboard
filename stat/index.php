<?php
include '../config.php';
setlocale(LC_ALL, 'zh_TW');
function cid_to_url($cid) {
    return "<a href='".URL_BASE."scoreboard.php?cid=$cid&end=1'>$cid</a>";
}
?>
<!DOCTYPE html>
<html lang="zh_TW">
<head>
    <title>JudgeGirl Scoreboard</title>
    <meta charset='utf-8'>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href='https://cdnjs.cloudflare.com/ajax/libs/tocas-ui/2.3.3/tocas.css' rel='stylesheet' type='text/css'>
    <link href='../stylesheets/stat.css' rel='stylesheet' type='text/css'>
</head>
<body>
    <div class="ts inverted fluid top attached link basic menu navmenu">
        <div class="ts narrow container">
            <a class="item header" href="../">JudgeGirl Scoreboard</a>
            <a class="item" href='./'>Statistics</a>
            <div class="right menu">
                <a class="item header" href="https://github.com/oToToT/JudgeGirl-Scoreboard">Github</a>
            </div>
        </div>
    </div>
    <div class="ts container" style="padding-top: 50px;">
        <div class="ts list">
<?php
$files = scandir('.');
foreach($files as $file) {
    if (is_dir($file) and $file !== '.' and $file !== '..') {
        $config = json_decode(file_get_contents("$file/config.json"), true);
?>
            <div class="item">
                <i class="tags icon"></i>
                <div class="content">
                    <a class="header" href='<?= urlencode($file) ?>'><?= $config['name'] ?></a>
                    <div class="description">
                    Last Update: <?= date("F d Y H:i:s.", filemtime("$file/index.html")) ?>
                    <br>
                    Contests: <?= implode(', ', array_map(cid_to_url, $config['contests'])) ?>
                    </div>
                </div>
            </div>
<?php
    }
}
?>
        </div>    
    </div>
</body>
</html>
