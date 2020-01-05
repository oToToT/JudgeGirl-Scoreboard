#!/usr/bin/env python3
import crawler
import json
import argparse
import colorama as co
import copy
import lzstring

html = '''<!DOCTYPE html>
<html lang="zh_TW">
<head>
<title>JudgeGirl Scoreboard</title>
<meta charset='utf-8'>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href='https://cdnjs.cloudflare.com/ajax/libs/tocas-ui/2.3.3/tocas.css' rel='stylesheet' type='text/css'>
<style>
.navmenu {
  font-size: 16px !important;
}
.navmenu a.item {
  color: #aaa !important;
}
.navmenu a.header.item {
  color: #fff !important;
}
.navmenu a.header.item:hover {
  color: #efefef !important;
}
.navmenu a.item:hover {
  color: #8f8f8f !important;
}
.ts.tle.statistics .statistic > .value,
.ts.statistics .tle.statistic > .value,
.ts.tle.statistic > .value {
  color: #9b00b7;
}
.ts.mle.statistics .statistic > .value,
.ts.statistics .mle.statistic > .value,
.ts.mle.statistic > .value {
  color: #ffd94f;
}
.ts.running.statistics .statistic > .value,
.ts.statistics .running.statistic > .value,
.ts.running.statistic > .value {
  color: #000000;
}
.clickable {
  cursor: pointer;
}
.horizontal.scrollable {
  display: block;
  overflow-x: auto;
}
.ts.table tr.tle:not(.indicated),
.ts.table td.tle:not(.indicated) {
  color: #9b00b7 !important;
  background: #ecbaf5 !important;
}
.ts.table tr.mle:not(.indicated),
.ts.table td.mle:not(.indicated) {
  color: #ffd94f !important;
  background: #fff6d4 !important;
}
.ts.table tr.running:not(.indicated),
.ts.table td.running:not(.indicated) {
  color: #000000 !important;
}
.ts.table tr.tle.indicated,
.ts.table td.tle.indicated {
  box-shadow: 2px 0 0 #be02e0 inset !important;
}
.ts.table tr.mle.indicated,
.ts.table td.mle.indicated {
  box-shadow: 2px 0 0 #fdcf28 inset !important;
}
.ts.table tr.running.indicated,
.ts.table td.running.indicated {
  box-shadow: 2px 0 0 #000000 inset !important;
}
</style>
</head>
<body>
<div class="ts inverted fluid top attached link basic menu navmenu">
  <div class="ts narrow container"><a class="header item" href="./">JudgeGirl Scoreboard</a><a class="item" href="./stat.html">Statistics</a>
    <div class="right menu"><a class="header item" href="https://github.com/oToToT/JudgeGirl-Scoreboard">Github</a></div>
  </div>
</div>
<div class="ts fluid vertically very padded heading borderless slate">
  <div class="ts divided fluid statistics" id="statistics">
    <div class="primary statistic">
      <div class="value">{{submissions}}</div>
      <div class="label">submissions</div>
    </div>
    <div class="positive statistic">
      <div class="value">{{ac}}</div>
      <div class="label">AC</div>
    </div>
    <div class="negative statistic">
      <div class="value">{{wa}}</div>
      <div class="label">WA</div>
    </div>
    <div class="info statistic">
      <div class="value">{{ce}}</div>
      <div class="label">CE</div>
    </div>
    <div class="warning statistic">
      <div class="value">{{re}}</div>
      <div class="label">RE</div>
    </div>
    <div class="tle statistic">
      <div class="value">{{tle}}</div>
      <div class="label">TLE</div>
    </div>
    <div class="mle statistic">
      <div class="value">{{mle}}</div>
      <div class="label">MLE</div>
    </div>
  </div>
</div>
<div class="horizontal scrollable">
  <table class="ts sortable selectable single line celled table" id="scoreboard">
    <thead style='position: sticky; top: 0;'>
      <tr>
        <th class="one wide">#</th>
        <th class="two wide" v-on:click="sortBy('uid')" v-bind:class="{sorted: sorting.key=='uid', ascending: sorting.key=='uid'&&sorting.state==1, descending: sorting.key=='uid'&&sorting.state==-1}">User</th>
        <th class="one wide" v-on:click="sortBy('trials')" v-bind:class="{sorted: sorting.key=='trials', ascending: sorting.key=='trials'&&sorting.state==1, descending: sorting.key=='trials'&&sorting.state==-1}">Trials</th>
        <th class="one wide" v-on:click="sortBy('score')" v-bind:class="{sorted: sorting.key=='score', ascending: sorting.key=='score'&&sorting.state==1, descending: sorting.key=='score'&&sorting.state==-1}">Total Score</th>
        <th class="center aligned" v-for="(problem,index) in problems" v-on:click="sortBy(index)" v-bind:class="{sorted: sorting.key==index, ascending: sorting.key==index&&sorting.state==1, descending: sorting.key==index&&sorting.state==-1}">{{problem.name}} ({{problem.ac}}/{{problem.total}})</th>
      </tr>
    </thead>
    <tbody>
      <template v-for="(user, index) in users">
        <tr>
          <td>{{index+1}}</td>
          <td>{{user.uid}}</td>
          <td>{{user.trials}}</td>
          <td>{{user.score}}</td>
          <template v-for="(data, pid) in user.scores">
            <td class="center aligned" v-bind:class="{clickable: data.type!=0, positive: data.type==3, error: data.type==1}" v-on:click="submissionDetail(user.uid, pid)">{{data.type == 0 ? '-' : data.score}}</td>
          </template>
        </tr>
      </template>
    </tbody>
    <tfoot>
      <tr v-if="users.length!=0">
        <th class="right aligned" colspan="4">Average Trials to AC / Average Score / Number of AC Users</th>
        <th class="center aligned" v-for="problem in problems">{{problem.ac_users == 0 ? &quot;No AC&quot; : Math.ceil(problem.ac_trials/problem.ac_users*100)/100}} / {{Math.ceil(problem.total_score/users.length*100)/100}} / {{problem.ac_users}}</th>
      </tr>
      <tr v-else="v-else">
        <th class="center aligned" colspan="4">No Submissions!</th>
      </tr>
    </tfoot>
  </table>
</div>
<div class="ts modals dimmer">
  <dialog class="ts closable modal" id="sDetail"><i class="close icon"></i>
    <div class="ts header">Submission Detail for {{username}}{{problem_name === ''?'':"'s "+problem_name}}</div>
    <div class="content horizontal scrollable">
      <table class="ts celled table">
        <thead>
          <tr>
            <th>Problem Name</th>
            <th>Submission ID</th>
            <th>Result</th>
            <th>Score</th>
            <th>Submit Time</th>
            <th>Time</th>
            <th>Memory</th>
            <th>Code Length</th>
          </tr>
        </thead>
        <tbody>
          <template v-for="submission in submissions">
            <tr v-bind:class="{indicated: true, positive: submission.result=='AC', negative: submission.result=='WA', info: submission.result=='CE', warning: submission.result=='RE', tle: submission.result=='TLE', mle: submission.result=='MLE', runnning: submission.result=='Running'}">
              <td>{{scoreboard.problems[submission.pid].name}}</td>
              <td><a v-bind:href="'https://judgegirl.csie.org/submission?sid='+submission.sid" target="_blank">{{submission.sid}}</a></td>
              <td v-bind:class="{positive: submission.result=='AC', negative: submission.result=='WA', info: submission.result=='CE', warning: submission.result=='RE', tle: submission.result=='TLE', mle: submission.result=='MLE', runnning: submission.result=='Running'}">{{submission.result}}</td>
              <td>{{submission.score}}</td>
              <td>{{submission.timestamp.toLocaleString()}}</td>
              <td>{{submission.cpu_time}} ms</td>
              <td>{{submission.memory/1024}} KiB</td>
              <td>{{submission.length}} Bytes</td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>
    <div class="actions">
      <button class="ts button" onclick="ts('#sDetail').modal('hide')">Close</button>
    </div>
  </dialog>
</div>
<script src='https://cdnjs.cloudflare.com/ajax/libs/tocas-ui/2.3.3/tocas.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.10/vue.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/lz-string/1.4.4/lz-string.min.js'></script>
<script>
let statistics = new Vue({
    el: "#statistics",
    data: {
        submissions: 0,
        ac: 0,
        wa: 0,
        ce: 0,
        re: 0,
        tle: 0,
        mle: 0,
    }
});
let submission_detail = new Vue({
    el: '#sDetail',
    data: {
        submissions: [],
        username: '',
        problem_name: ''
    }
});
let scoreboard = new Vue({
    el: "#scoreboard",
    data: {
        sorting: { key: 'score', state: -1 },
        problems: [],
        users: []
    },
    methods: {
        sortBy: function(key) {
            if (this.sorting.key == key) {
                this.sorting.state *= -1;
            } else {
                this.sorting.key = key;
                this.sorting.state = -1;
            }
            let that = this;
            if (typeof key === "string") {
                this.users.sort(function(a, b) {
                    if (a[key] < b[key]) return -1 * that.sorting.state;
                    if (a[key] > b[key]) return 1 * that.sorting.state;
                    return 0;
                });
            } else if (typeof key === "number") {
                this.users.sort(function(a, b) {
                    return (a.scores[key].score - b.scores[key].score) * that.sorting.state;
                });
            }
        },
        submissionDetail : function(uid, pid) {
            let s = this.users.find(user=>user.uid === uid).submissions;
            s = s.filter(ss=>ss.pid == pid)
            if (s.length == 0) return;
            submission_detail.submissions = s;
            submission_detail.username = uid;
            submission_detail.problem_name = this.problems[pid].name;
            ts('#sDetail').modal('show');
        }
    }
});

/* use LZString from https://github.com/pieroxy/lz-string/ */
let data = JSON.parse(LZString.decompressFromBase64('%s'));

let stat = data.stat;
statistics.submissions = stat.submissions;
statistics.last_update = Date.now();
statistics.ac = stat.AC;
statistics.wa = stat.WA;
statistics.ce = stat.CE;
statistics.re = stat.RE;
statistics.tle = stat.TLE;
statistics.mle = stat.MLE;
statistics.running = stat.Running;

scoreboard.problems = data.problems;

// transform timestamp to Date
let users = data.users;
users.forEach(function(user) {
    user.last = new Date(user.last);
    user.submissions.forEach(function(submission) {
        submission.timestamp = new Date(submission.timestamp);
    });
});

// sort users as scoreboard.sortBy
let sort_key = scoreboard.sorting.key;
users.sort(function(a, b) {
    if (a[sort_key] < b[sort_key]) return -1 * scoreboard.sorting.state;
    if (a[sort_key] > b[sort_key]) return 1 * scoreboard.sorting.state;
    return 0;
});

scoreboard.users = users;
</script>
</body>
</html>
'''

def get_submissions(cid):
    print('getting submissions from contest ' + co.Fore.GREEN + f'{cid}' + co.Style.RESET_ALL)
    return crawler.get_submissions(cid)

def parse_submissions(submissions):
    res2text = ['Running', 'CE', 'OLE', 'MLE', 'RE', 'TLE', 'WA', 'AC', 'Uploading', 'PE']
    parsed = dict({
        'stat': {},
        'problems': [],
        'users': []
    })
    
    parsed['stat']['submissions'] = len(submissions)
    for x in res2text:
        parsed['stat'][x] = sum(map(lambda s: res2text[s['res']] == x, submissions))

    problems = {}
    trials = {}
    users = {}
    for s in submissions:
        problems[s['pid']] = {'total': 0, 'ac': 0, 'ac_trials': 0, 'ac_users': set(), 'total_score': 0}
        trials[s['pid']] = {}
        users[s['uid']] = {'trials': 0, 'scores': {}, 'uid': s['lgn'], 'submissions': []}

    for s in submissions:
        problems[s['pid']]['total'] += 1
        problems[s['pid']]['name'] = s['ttl']

        if s['uid'] not in trials[s['pid']]:
            trials[s['pid']][s['uid']] = 1
        else:
            trials[s['pid']][s['uid']] += 1

        if s['pid'] not in users[s['uid']]['scores']:
            users[s['uid']]['scores'][s['pid']] = {'type': 0, 'score': 0}

        if s['res'] == 7:
            problems[s['pid']]['ac'] += 1
            if s['uid'] not in problems[s['pid']]['ac_users']:
                problems[s['pid']]['ac_users'].add(s['uid'])
                problems[s['pid']]['ac_trials'] += trials[s['pid']][s['uid']]
            users[s['uid']]['scores'][s['pid']]['type'] = max(users[s['uid']]['scores'][s['pid']]['type'], 3)
        elif s['scr'] == 0:
            users[s['uid']]['scores'][s['pid']]['type'] = max(users[s['uid']]['scores'][s['pid']]['type'], 1)
        else:
            users[s['uid']]['scores'][s['pid']]['type'] = max(users[s['uid']]['scores'][s['pid']]['type'], 2)

        problems[s['pid']]['total_score'] -= users[s['uid']]['scores'][s['pid']]['score'];
        users[s['uid']]['trials'] += 1
        users[s['uid']]['scores'][s['pid']]['score'] = max(users[s['uid']]['scores'][s['pid']]['score'], s['scr']);
        users[s['uid']]['submissions'].append({
            'sid': s['sid'],
            'score': s['scr'],
            'memory': s['mem'],
            'cpu_time': s['cpu'],
            'timestamp': s['ts'],
            'result': res2text[s['res']],
            'pid': s['pid'],
            'length': s['len']
        })
        problems[s['pid']]['total_score'] += users[s['uid']]['scores'][s['pid']]['score'];
    
    problem2id = {}
    for key in sorted(problems.keys()):
        problem2id[key] = len(parsed['problems'])
        problems[key]['ac_users'] = len(problems[key]['ac_users'])
        parsed['problems'].append(problems[key])

    for key in users:
        u = copy.deepcopy(users[key])
        u['scores'] = {}
        u['score'] = 0
        for p in problem2id:
            if p not in users[key]['scores']:
                users[key]['scores'][p] = {'score': 0, 'type': 0}
            u['score'] += users[key]['scores'][p]['score']
            u['scores'][problem2id[p]] = users[key]['scores'][p]
        for s in u['submissions']:
            s['pid'] = problem2id[s['pid']]
        parsed['users'].append(u)
    return parsed

def html_to_file(cids, ou):
    co.init()
    print(co.Style.BRIGHT + 'Ready to crawl ' + co.Fore.RED + f'{len(cids)}' + co.Fore.RESET + ' contest(s)' + co.Style.RESET_ALL)
    submissions = sum(map(get_submissions, cids), [])
    print(co.Style.BRIGHT + 'total submission(s): ' + co.Fore.MAGENTA + f'{len(submissions)}' + co.Style.RESET_ALL)
    
    parsed = parse_submissions(submissions)
    parsed_json = json.dumps(parsed)
    lz = lzstring.LZString()
    b64 = lz.compressToBase64(parsed_json)
    
    ou.write(html%b64)

if __name__ == '__main__':
    co.init()
    parser = argparse.ArgumentParser(description='Generate a statistics page for JudgeGirl')
    parser.add_argument('f', metavar='CONFIG', type=argparse.FileType(), help='Path to config file.')
    parser.add_argument('ou', metavar='SAVE', type=argparse.FileType(mode='w'), help='Path to output file')
    args = parser.parse_args()

    cids = list(map(int, args.f.read().split()))
    args.f.close()

    html_to_file(cids, args.ou)
