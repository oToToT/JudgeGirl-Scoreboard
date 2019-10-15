<?php
if(!isset($_GET['cid'])){
    die('cid needed');
}
if(!isset($_GET['end'])){
    die('end needed');
}
?>
<!DOCTYPE html>
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
.ts.tle.statistics .statistic>.value, .ts.statistics .tle.statistic>.value, .ts.tle.statistic>.value {
    color: #9b00b7;
}
.ts.mle.statistics .statistic>.value, .ts.statistics .mle.statistic>.value, .ts.mle.statistic>.value {
    color: #ffd94f;
}
.ts.running.statistics .statistic>.value, .ts.statistics .running.statistic>.value, .ts.running.statistic>.value {
    color: #000000;
}
</style>
</head>
<body>
    <div class="ts active dimmer" id="dimmer">
        <div class="ts text loader">載入中</div>
    </div>
    <div class="ts inverted fluid top attached link basic menu navmenu">
        <div class="ts narrow container"><a class="item router-link-active" href="./">JudgeGirl Scoreboard</a>
            <div class="right menu"><a class="item" href="https://github.com/oToToT/JudgeGirl-Scoreboard">Github</a></div>
        </div>
    </div>
    <div class="ts fluid vertically very padded heading borderless slate">
        <div class="ts divided fluid statistics" id="statistics">
            <div class="primary statistic">
                <div class="value">{{submissions}}</div>
                <div class="label">submissions</div>
            </div>
            <div class="secondary statistic">
                <div class="value">{{Math.max(0, Math.ceil((<?= htmlspecialchars($_GET['end']) ?>-last_update)/60000*10)/10)}}</div>
                <div class="label">mins left</div>
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
            <div class="running statistic">
                <div class="value">{{running}}</div>
                <div class="label">Running</div>
            </div>
        </div>
    </div>
    <table class="ts sortable selectable single line celled table" id="scoreboard">
        <thead>
            <tr>
                <th class="two wide" v-on:click="sortBy('uid')" v-bind:class="{sorted: sorting.key=='uid', ascending: sorting.key=='uid'&&sorting.state==1, descending: sorting.key=='uid'&&sorting.state==-1}">User</th>
                <th class="one wide" v-on:click="sortBy('trials')" v-bind:class="{sorted: sorting.key=='trials', ascending: sorting.key=='trials'&&sorting.state==1, descending: sorting.key=='trials'&&sorting.state==-1}">Trials</th>
                <th class="one wide" v-on:click="sortBy('score')" v-bind:class="{sorted: sorting.key=='score', ascending: sorting.key=='score'&&sorting.state==1, descending: sorting.key=='score'&&sorting.state==-1}">Total Score</th>
                <th class="two wide" v-on:click="sortBy('last')" v-bind:class="{sorted: sorting.key=='last', ascending: sorting.key=='last'&&sorting.state==1, descending: sorting.key=='last'&&sorting.state==-1}">Last Submission</th>
                <th class="center aligned" v-for="(problem,index) in problems" v-on:click="sortBy(index)" v-bind:class="{sorted: sorting.key==index, ascending: sorting.key==index&&sorting.state==1, descending: sorting.key==index&&sorting.state==-1}">{{problem.name}} ({{problem.ac}}/{{problem.total}})</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="user in users">
                <td>{{user.uid}}</td>
                <td>{{user.trials}}</td>
                <td>{{user.score}}</td>
                <td>{{user.last.getHours()}}:{{String(user.last.getMinutes()).padStart(2, '0')}}:{{String(user.last.getSeconds()).padStart(2, '0')}}</td>
                <td class="center aligned" v-for="score in user.scores" v-bind:class="{positive: score==100, error: score==0}">{{score}}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr v-if="users.length!=0">
                <th class="right aligned" colspan="4">Average Trials to AC / Average Score / Number of AC Users</th>
                <th class="center aligned" v-for="problem in problems">{{problem.ac_users == 0 ? "No AC" : Math.ceil(problem.ac_trials/problem.ac_users*100)/100}} / {{Math.ceil(problem.total_score/problem.total_users*100)/100}} / {{problem.ac_users}}</th>
            </tr>
            <tr v-else="v-else">
                <th class="center aligned" colspan="4">No Submissions!</th>
            </tr>
        </tfoot>
    </table>
    <script src='https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.10/vue.min.js'></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script src="javascripts/scoreboard.js"></script>
    <script>
        scoreboard.sorting.key = 'score';
        scoreboard.sorting.state = -1;
        setInterval(()=>render(<?= htmlspecialchars($_GET['cid']) ?>), 2500);
    </script>
</body>
</html>
