<?php
require 'config.php';
if(!isset($_GET['cid'])){
    die('cid required');
}
if(!filter_var($_GET['cid'], FILTER_VALIDATE_INT)){
    die('Invalid cid');
}

function crawl_submissions($cid, $user_info){
    // call crawler.py to crawl submission
    $submissions = json_decode(exec('python3 crawler.py '.escapeshellarg($cid)));

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
        foreach($problems as $pid=>$s){
            if(!$tmp[$pid]) $tmp[$pid] = 0;
            $user['scores'][$problem2id[$pid]]=$tmp[$pid];
        }
        if(isset($user_info[$user['uid']]))
            $user['uid'] = $user_info[$user['uid']];
        array_push($parsed['users'], $user);
    }
    return $parsed;
}

// include student_info.php
$user_info = array();
if(file_exists('./student_info.php')){
    require './student_info.php';
}

// use a lock file to cache data
$LOCK_FILENAME = TMP_PATH.$_GET['cid'].'-jgsb.lock';
$LOCK_FILE = fopen($LOCK_FILENAME, 'c+');
// check already in use
if(flock($LOCK_FILE, LOCK_EX | LOCK_NB)){ 
    // update data if file is no locked
    $result = json_encode(crawl_submissions($_GET['cid'], user_info));
    fwrite($LOCK_FILE, $result);
    ftruncate($LOCK_FILE, ftell($LOCK_FILE));
    flock($LOCK_FILE, LOCK_UN);
    fseek($LOCK_FILE, 0, SEEK_SET);
}
header('Content-type: application/json;');
if(flock($LOCK_FILE, LOCK_SH)){
    // return saved content
    echo fread($LOCK_FILE, filesize($LOCK_FILENAME));
    fclose($LOCK_FILE);
} else {
    echo 'Error opening lock file.';
}
?>
