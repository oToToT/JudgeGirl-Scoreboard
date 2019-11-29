let statistics = new Vue({
    el: "#statistics",
    data: {
        submissions: 0,
        last_update: Date.now(),
        ac: 0,
        wa: 0,
        ce: 0,
        re: 0,
        tle: 0,
        mle: 0,
        running: 0
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
        sorting: { key: undefined, state: 0 },
        problems: [],
        users: [],
        submission_detail: [],
        selected_user: ''
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
                    return (a.scores[key] - b.scores[key]) * that.sorting.state;
                });
            }
        },
        submissionDetail : function(uid, pid) {
            let s = this.users.find(user=>user.uid === uid).submissions;
            if (typeof pid !== 'undefined')
                s = s.filter(submission=>submission.pid === pid);
            submission_detail.submissions = s;
            submission_detail.username = uid;
            submission_detail.problem_name = '';
            if (typeof pid !== 'undefined')
                submission_detail.problem_name = this.problems[pid].name;
            ts('#sDetail').modal('show');
        }
    }
});

function render(cid) {
    axios({
        method: 'GET',
        url: './api.php',
        timeout: 3000,
        params: {
            cid: cid
        },
        responseType: 'json'
    }).then(function(res){
        let stat = res.data.stat;
        statistics.submissions = stat.submissions;
        statistics.last_update = Date.now();
        statistics.ac = stat.AC;
        statistics.wa = stat.WA;
        statistics.ce = stat.CE;
        statistics.re = stat.RE;
        statistics.tle = stat.TLE;
        statistics.mle = stat.MLE;
        statistics.running = stat.Running;

        scoreboard.problems = res.data.problems;

        let users = res.data.users;
        users.forEach(function(user) {
            user.last = new Date(user.last);
            user.submissions.forEach(function(submission) {
                submission.timestamp = new Date(submission.timestamp);
            });
        });

        let sort_key = scoreboard.sorting.key;
        if (typeof sort_key === "string") {
            users.sort(function(a, b) {
                if (a[sort_key] < b[sort_key]) return -1 * scoreboard.sorting.state;
                if (a[sort_key] > b[sort_key]) return 1 * scoreboard.sorting.state;
                return 0;
            });
        } else if (typeof sort_key === "number") {
            users.sort(function(a, b) {
                return (a.scores[sort_key] - b.scores[sort_key]) * scoreboard.sorting.state;
            });
        }

        scoreboard.users = users;
        document.getElementById('dimmer').style.display = 'none';
        setTimeout(()=>render(cid), 100);
    }).catch(function(e){
        render(cid);
    });
}
