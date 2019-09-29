<?php
if(!isset($_GET['cid'])){
    die('cid required');
}
// include student_info.php
$user_info = array();
if(file_exists('./student_info.php')){
    require './student_info.php';
}

// call crawler.py to crawl submissions
header('Content-type: application/json;');
$submissions = json_decode(exec('python3 crawler.py '.escapeshellarg($_GET['cid'])));

$res2text = ['running', 'ce', 'ole', 'mle', 're', 'tle', 'wa', 'ac', 'uploading', 'pe'];

// initiallize some variable to analyze submissions
$trials = array(); // statistics for each problem's trial count
$problems = array(); // data for each problem
$users = array(); // some user information
$parsed = array(); // parsed data
$parsed['stat']['submissions'] = count($submissions);
foreach($res2text as $key) $parsed['stat'][$key] = 0;
foreach($submissions as $submission) {
    $parsed['stat'][$res2text[$submission->res]] += 1;

    $problems[$submission->pid]['name'] = $submission->ttl;
    $problems[$submission->pid]['total'] += 1;
    $trials[$submission->uid] += 1;
    // if this submission is AC
    if($submission->res == 7){
        $problems[$submission->pid]['ac'] += 1;
        if(!$problems[$submission->pid]['ac_users'])
            $problems[$submission->pid]['ac_users'] = array();
        if(!in_array($submission->uid, $problems[$submission->pid]['ac_users'], true)){
            $problems[$submission->pid]['ac_trials'] += $trials[$submission->uid];
            array_push($problems[$submission->pid]['ac_users'], $submission->uid);
        }
    }
    // process total score and total for a problem
    if(!$problems[$submission->pid]['total_users'])
        $problems[$submission->pid]['total_users'] = array();
    if(!in_array($submission->uid, $problems[$submission->pid]['total_users'], true))
        array_push($problems[$submission->pid]['total_users'], $submission->uid);
    $problems[$submission->pid]['total_score'] -= $users[$submission->uid]['scores'][$submission->pid];
    // process user's data
    $users[$submission->uid]['uid'] = $submission->lgn;
    $users[$submission->uid]['trials'] += 1;
    $users[$submission->uid]['scores'][$submission->pid] = max($users[$submission->uid]['scores'][$submission->pid], $submission->scr);
    $users[$submission->uid]['last'] = max($users[$submission->uid]['last'], $submission->ts);
    // calculate total score for a single problem
    $problems[$submission->pid]['total_score'] += $users[$submission->uid]['scores'][$submission->pid];
}
// add problems into parsed
$problem2id = array();
$parsed['problems'] = array();
foreach($problems as $id => $data){
    $problem2id[$id] = count($parsed['problems']);
    $data['ac_users'] = count($data['ac_users']);
    $data['total_users'] = count($data['total_users']);
    array_push($parsed['problems'], $data);
}

// add users into parsed
$parsed['users'] = array();
foreach($users as $user){
    $user['score'] = array_sum($user['scores']);
    $tmp = $user['scores'];
    unset($user['scores']);
    foreach($tmp as $pid=>$score){
        if(!$score) $score = 0;
        $user['scores'][$problem2id[$pid]]=$score;
    }
    if(isset($user_info[$user['uid']]))
        $user['uid'] = $user_info[$user['uid']];
    array_push($parsed['users'], $user);
}

echo json_encode($parsed);
?>
