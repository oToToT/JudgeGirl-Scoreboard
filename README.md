# JudgeGirl Scoreboard #

## Intro ##

A scoreboard for [JudgeGirl](https://judgegirl.csie.org) contest.
It will crawl submissions during the contest with [JudgeGirl API](https://judgegirl.csie.org/api/submission).

## Features ##

- Support multiple problems in a single contest

You can view lots of problems in a single contest in a single web page.

- Dynamic scoreboard using [Vue.js](https://vuejs.org)

The score distribution will be displayed dymaically, and so does those statistics.

- Custom sorting in scoreboard

You can sort scoreboard with last submissions, total score, users' id, and so on.

- Fancy UI

We use [Tocas UI](https://github.com/TeaMeow/TocasUI) to displayed our scoreboard, it's a RWD-friendly UI library.

- Customizable display name

You can change the display name (e.g. add users' name after his uid) simply.

- A static statistics page

By setting some config, you could generate some staic statistics page to overview contests.

## Usage ##

First of all, check you have Python with version >= 3 and PHP

Then `git clone https://github.com/oToToT/JudgeGirl-Scoreboard.git`, now those source code will be cloned into `JudgeGirl-Scoreboard`.

Run `pip install -r requirements.txt` to install dependencies for those python scripts.

Edit `URL_BASE` to the url of your website, `TMP_PATH` to the path which could store a temp file, and `JUDGEGIRL_URL` to JudgeGirl's url in `config.example.json` and rename it to `config.json` (notice that a `/` behind any path is important).

To customize display name, you should modify `student_info.example.php` which stores a mapping from uid to what you want to display.

After that, you should rename it to `student_info.php`.

Now, you could enjoy the fancy scoreboard of JudgeGirl.

To modify the statistics page, you should go to `stat/` and add some folder like `2019-all` with a `config.json` inside it.

Then, run `python libs/gen_stat.py` to generate static html files. (Notice that you can't customize names in statistics page.)

## Special Thanks ##

Thanks [@brianbbsu](https://github.com/brianbbsu) for the `crawler.py`.

Without his first attempt, I won't start this project.

Thanks [@bluemorbo](https://github.com/bluemorbo) for adding some XSS protection.

Also, thanks those who have make suggestion for me.
