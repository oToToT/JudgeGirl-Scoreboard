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
let scoreboard = new Vue({
    el: "#scoreboard",
    data: {
        sorting: { key: undefined, state: 0 },
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
                    return (a.scores[key] - b.scores[key]) * that.sorting.state;
                });
            }
        },
        make_sorted: function() {
            if(typeof this.sorting.key === 'undefined') return;
            let that = this, key = this.sorting.key;
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
        }
    }
});
function render(cid) {
    axios({
        method: 'GET',
        url: './api.php',
        timeout: 2000,
        params: {
            cid: cid
        },
        responseType: 'json'
    }).then(function(res){
        let stat = res.data.stat;
        statistics.submissions = stat.submissions;
        statistics.last_update = Date.now();
        statistics.ac = stat.ac;
        statistics.wa = stat.wa;
        statistics.ce = stat.ce;
        statistics.re = stat.re;
        statistics.tle = stat.tle;
        statistics.mle = stat.mle;
        statistics.running = stat.running;

        scoreboard.problems = res.data.problems;

        let users = res.data.users;
        users.forEach(function(user){
            user.last = new Date(user.last);
        });

        let sort_key = scoreboard.sorting.key;
        if (typeof sort_key === "string") {
            scoreboard.users.sort(function(a, b) {
                if (a[sort_key] < b[sort_key]) return -1 * scoreboard.sorting.state;
                if (a[sort_key] > b[sort_key]) return 1 * scoreboard.sorting.state;
                return 0;
            });
        } else if (typeof sort_key === "number") {
            scoreboard.users.sort(function(a, b) {
                return (a.scores[sort_key] - b.scores[sort_key]) * scoreboard.sorting.state;
            });
        }

        scoreboard.users = users;
        scoreboard.make_sorted();
    });
}