<?php
$conf = json_decode(file_get_contents(__DIR__.'/config.json'), true);
define(JUDGEGIRL_URL, $conf['JUDGEGIRL_PATH']);
define(URL_BASE, $conf['URL_BASE']);
define(TMP_PATH, $conf['TMP_PATH']);
?>
