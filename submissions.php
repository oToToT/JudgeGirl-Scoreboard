<?php
if(!isset($_GET['cid'])){
    die('cid needed');
}
header('Content-type: application/json;');
