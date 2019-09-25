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

## Usage ##

First of all, check you have Python with version >= 3 and PHP

then `git clone https://github.com/oToToT/JudgeGirl-Scoreboard.git`, now those source code will be cloned into JudgeGirl-Scoreboard.

To customize display name, you should add a file called `student_info.php` and define a array called `user_info` which stores a mapping from uid to what you want to display.

## Special Thanks ##

Thanks @brianbbsu for the `crawler.py`. Without his first attempt, I won't start this project.
Also, thanks those who have make suggestion for me.