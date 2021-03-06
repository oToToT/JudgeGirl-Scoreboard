<?php
require 'config.php';
if (!isset($_GET['cid'])) {
    die('cid required');
}
if (!filter_var($_GET['cid'], FILTER_VALIDATE_INT)) {
    die('Invalid cid');
}
header('Content-type: application/json;');

function sort_user_by_last($a, $b) {
    return $a['last'] - $b['last'];
}

function crawl_submissions($cid) {
    // call crawler.py to crawl submissions
    return json_decode(exec('python3 libs/crawler.py '.escapeshellarg($cid)));
}

function process_submissions($submissions) {
    // process submissions
    // initiallize some variable to analyze submissions
    $res2text = ['Running', 'CE', 'OLE', 'MLE', 'RE', 'TLE', 'WA', 'AC', 'Uploading', 'PE'];
    $trials = array(); // statistics for each problem's trial count
    $problems = array(); // data for each problem
    $users = array(); // some user information
    $parsed = array(); // parsed data
    $parsed['stat']['submissions'] = count($submissions);
    foreach ($res2text as $key) $parsed['stat'][$key] = 0;
    foreach ($submissions as $submission) {
        // add verdict to stat
        $parsed['stat'][$res2text[$submission->res]] += 1;
        // get problem title
        $problems[$submission->pid]['name'] = $submission->ttl;
        // count submissions of problem with pid
        $problems[$submission->pid]['total'] += 1;
        // count trials of problem with pid and user with uid
        if (!$trials[$submission->pid])
            $trials[$submission->pid] = array();
        $trials[$submission->pid][$submission->uid] += 1;

        if ($submission->res == 7) {
            // count number of ac users of problem with pid
            $problems[$submission->pid]['ac'] += 1;
            // initiallize ac_users array
            if (!$problems[$submission->pid]['ac_users'])
                $problems[$submission->pid]['ac_users'] = array();
            // count if user doesn't exist
            if (!in_array($submission->uid, $problems[$submission->pid]['ac_users'], true)) {
                $problems[$submission->pid]['ac_trials'] += $trials[$submission->pid][$submission->uid];
                array_push($problems[$submission->pid]['ac_users'], $submission->uid);
            }
            // user with uid got AC at problem with pid
            $users[$submission->uid]['scores'][$submission->pid]['type'] = max($users[$submission->uid]['scores'][$submission->pid]['type'], 3);
        } else if($submission->res !== 0 && $subumission->res !== 8) {
            // not running and not uploading
            if ($submission->scr === 0) {
                // score = 0
                $users[$submission->uid]['scores'][$submission->pid]['type'] = max($users[$submission->uid]['scores'][$submission->pid]['type'], 1);
            } else {
                // have some points
                $users[$submission->uid]['scores'][$submission->pid]['type'] = max($users[$submission->uid]['scores'][$submission->pid]['type'], 2);

            }
        }
        // process total score for a problem
        $problems[$submission->pid]['total_score'] -= $users[$submission->uid]['scores'][$submission->pid]['score'];
        // process user's data
        $users[$submission->uid]['uid'] = $submission->lgn;
        $users[$submission->uid]['trials'] += 1;
        $users[$submission->uid]['scores'][$submission->pid]['score'] = max($users[$submission->uid]['scores'][$submission->pid]['score'], $submission->scr);
        $users[$submission->uid]['scores'][$submission->pid]['type'] = max($users[$submission->uid]['scores'][$submission->pid]['type'], 0);
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
        $problems[$submission->pid]['total_score'] += $users[$submission->uid]['scores'][$submission->pid]['score'];
    }

    // add problems into parsed
    $problem2id = array();
    $parsed['problems'] = array();
    $keys = array_keys($problems);
    sort($keys);
    foreach ($keys as $id) {
        $data = $problems[$id];
        $problem2id[$id] = count($parsed['problems']);
        $data['ac_users'] = count($data['ac_users']);
        array_push($parsed['problems'], $data);
    }

    // add users into parsed
    $parsed['users'] = array();
    foreach ($users as $user) {
        $user['score'] = 0;
        $tmp = $user['scores'];
        unset($user['scores']);
        foreach ($problems as $pid=>$s) {
            if(!$tmp[$pid]) $tmp[$pid] = array('score'=>0, 'type'=>0);
            $tmp[$pid]['score'] = intval($tmp[$pid]['score']);
            $user['scores'][$problem2id[$pid]] = $tmp[$pid];
            $user['score'] += $tmp[$pid]['score'];
        }
        foreach ($user['submissions'] as $sid=>$submission)
            $user['submissions'][$sid]['pid'] = $problem2id[$submission['pid']];
        array_push($parsed['users'], $user);
    }
    usort($parsed['users'], sort_user_by_last);
    return $parsed;
}

function result2json($result, $user_info) {
    // use $user_info to process $result
    // return a json encoded string of $result
    foreach ($result['users'] as &$user) {
        if (isset($user_info[$user['uid']])) {
            $user['uid'] = $user_info[$user['uid']];
        }
    }
    // when using reference it is better to unset it
    // however it will disappear normally, here
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
    $result = process_submissions(crawl_submissions($_GET['cid']));
    // only cached crawled and processed data to avoid privilege escalation
    fwrite($LOCK_FILE, json_encode($result));
    ftruncate($LOCK_FILE, ftell($LOCK_FILE));
    flock($LOCK_FILE, LOCK_UN);
    fclose($LOCK_FILE);
    die(result2json($result, $user_info));
}
if(flock($LOCK_FILE, LOCK_SH)){
    // return saved content from json to array to str
    $result = json_decode(fread($LOCK_FILE, filesize($LOCK_FILENAME)), true);
    fclose($LOCK_FILE);
    die(result2json($result, $user_info));
} else {
    die('Error opening lock file.');
}
?>
