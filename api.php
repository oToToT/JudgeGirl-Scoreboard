<?php
if(!isset($_GET['cid'])){
    die('cid needed');
}
header('Content-type: application/json;');
$submissions = json_decode(exec('python3 crawler.py '.escapeshellarg($_GET['cid'])));

$res2text = ['running', 'ce', 'ole', 'mle', 're', 'tle', 'wa', 'ac', 'uploading', 'pe'];

$problems = array();
$users = array();
$parsed = array();
$parsed['stat']['submissions'] = count($submissions);
foreach($res2text as $key) $parsed['stat'][$key] = 0;
foreach($submissions as $submission) {
    $parsed['stat'][$res2text[$submission->res]] += 1;

    $problems[$submission->pid]['name'] = $submission->ttl;
    $problems[$submission->pid]['total'] += 1;
    if($submission->res == 7)
        $problems[$submission->pid]['ac'] += 1;

    $users[$submission->uid]['uid'] = $submission->lgn;
    $users[$submission->uid]['trials'] += 1;
    $users[$submission->uid]['scores'][$submission->pid] = max($users[$submission->uid]['scores'][$submission->pid], $submission->scr);
    $users[$submission->uid]['last'] = max($users[$submission->uid]['last'], $submission->ts);
}
$problem2id = array();
$parsed['problems'] = array();
foreach($problems as $id => $data){
    $problem2id[$id] = count($parsed['problems']);
    array_push($parsed['problems'], $data);
}

$parsed['users'] = array();
foreach($users as $user){
    $user['score'] = array_sum($user['scores']);
    $tmp = $user['scores'];
    unset($user['scores']);
    foreach($tmp as $pid=>$score){
        if(!$score) $score = 0;
        $user['scores'][$problem2id[$pid]]=$score;
    }
    array_push($parsed['users'], $user);
}

echo json_encode($parsed);
?>
