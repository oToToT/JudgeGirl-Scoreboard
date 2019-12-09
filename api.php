<?php
require 'config.php';
if (!isset($_GET['cid'])) {
    die('cid required');
}
if (!filter_var($_GET['cid'], FILTER_VALIDATE_INT)) {
    die('Invalid cid');
}
header('Content-type: application/json;');

function crawl_submissions($cid) {
    // call crawler.py to crawl submission
    $submissions = json_decode(exec('python3 crawler.py '.escapeshellarg($cid)));
    
    $res2text = ['Running', 'CE', 'OLE', 'MLE', 'RE', 'TLE', 'WA', 'AC', 'Uploading', 'PE'];

    // initiallize some variable to analyze submissions
    $trials = array(); // statistics for each problem's trial count
    $problems = array(); // data for each problem
    $users = array(); // some user information
    $parsed = array(); // parsed data
    $parsed['stat']['submissions'] = count($submissions);
    foreach ($res2text as $key) $parsed['stat'][$key] = 0;
    foreach ($submissions as $submission) {
        $parsed['stat'][$res2text[$submission->res]] += 1;

        $problems[$submission->pid]['name'] = $submission->ttl;
        $problems[$submission->pid]['total'] += 1;
        $trials[$submission->uid] += 1;
        // if this submission is AC
        if ($submission->res == 7) {
            $problems[$submission->pid]['ac'] += 1;
            if (!$problems[$submission->pid]['ac_users'])
                $problems[$submission->pid]['ac_users'] = array();
            if (!in_array($submission->uid, $problems[$submission->pid]['ac_users'], true)) {
                $problems[$submission->pid]['ac_trials'] += $trials[$submission->uid];
                array_push($problems[$submission->pid]['ac_users'], $submission->uid);
            }
        }
        // process total score and total for a problem
        if (!$problems[$submission->pid]['total_users'])
            $problems[$submission->pid]['total_users'] = array();
        if (!in_array($submission->uid, $problems[$submission->pid]['total_users'], true))
            array_push($problems[$submission->pid]['total_users'], $submission->uid);
        $problems[$submission->pid]['total_score'] -= $users[$submission->uid]['scores'][$submission->pid];
        // process user's data
        $users[$submission->uid]['uid'] = $submission->lgn;
        $users[$submission->uid]['trials'] += 1;
        $users[$submission->uid]['scores'][$submission->pid] = max($users[$submission->uid]['scores'][$submission->pid], $submission->scr);
        $users[$submission->uid]['last'] = max($users[$submission->uid]['last'], $submission->ts);
        if (!$users[$submission->uid]['submissions'])
            $users[$submission->uid]['submissions'] = array();
        array_push($users[$submission->uid]['submissions'], array(
            'sid'=>$submission->sid,
            'score'=>$submission->scr,
            'memory'=>$submission->mem,
            'cpu_time'=>$submission->cpu,
            'timestamp'=>$submission->ts,
            'result'=>$res2text[$submission->res],
            'pid'=>$submission->pid,
            'length'=>$submission->len
        ));
        // calculate total score for a single problem
        $problems[$submission->pid]['total_score'] += $users[$submission->uid]['scores'][$submission->pid];
    }
    // add problems into parsed
    $problem2id = array();
    $parsed['problems'] = array();
    foreach ($problems as $id => $data) {
        $problem2id[$id] = count($parsed['problems']);
        $data['ac_users'] = count($data['ac_users']);
        $data['total_users'] = count($data['total_users']);
        array_push($parsed['problems'], $data);
    }

    // add users into parsed
    $parsed['users'] = array();
    foreach ($users as $user) {
        $user['score'] = array_sum($user['scores']);
        $tmp = $user['scores'];
        unset($user['scores']);
        foreach ($problems as $pid=>$s) {
            if(!$tmp[$pid]) $tmp[$pid] = 0;
            $user['scores'][$problem2id[$pid]]=$tmp[$pid];
        }
        foreach ($user['submissions'] as $sid=>$submission)
            $user['submissions'][$sid]['pid'] = $problem2id[$submission['pid']];
        array_push($parsed['users'], $user);
    }
    return $parsed;
}

function result2json($result, $student_info) {
    // use $student_info to process $result
    // return a json encoded string of $result
    foreach ($result['users'] as $user) {
        if (isset($student_info[$user['uid']])) {
            $user['uid'] = $student_info[$user['uid']];
        }
    }
    return json_encode($result);
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
    $result = crawl_submissions($_GET['cid']);
    // only cached crawled and processed data to avoid privilege escalation
    fwrite($LOCK_FILE, json_encode($result));
    ftruncate($LOCK_FILE, ftell($LOCK_FILE));
    flock($LOCK_FILE, LOCK_UN);
    fclose($LOCK_FILE);
    die(result2json($result, $student_info));
}
if(flock($LOCK_FILE, LOCK_SH)){
    // return saved content
    $result = json_decode(fread($LOCK_FILE, filesize($LOCK_FILENAME)));
    fclose($LOCK_FILE);
    die(result2json($result, $student_info));
} else {
    die('Error opening lock file.');
}
?>
