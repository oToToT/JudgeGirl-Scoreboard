<!DOCTYPE html>
<html lang="zh_TW">
<head>
<title>JudgeGirl Scoreboard</title>
<meta charset='utf-8'>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href='https://cdnjs.cloudflare.com/ajax/libs/tocas-ui/2.3.3/tocas.css' rel='stylesheet' type='text/css'>
<link href="stylesheets/flatpickr.min.css" rel='stylesheet' type='text/css'>
<link href="stylesheets/index.css" rel='stylesheet' type='text/css'>
</head>
<body>
    <!--Hey! This is the original version
of Simple CSS Waves-->

<div class="header">

<!--Content before waves-->
<div class="inner-header flex">
  <div>
    <h1 class="unstyled">JudgeGirl Scoreboard</h1>
    <hr>
    <form method="GET" action="./scoreboard.php" class="ts form" id="form">
      <div class="field">
        <label>Contest ID:</label>
        <input type="number" name="cid">
      </div>
      <div class="field">
        <label>End Time:</label>
        <input type="text" class="flatpickr" name="end" id="end">
      </div>
      <input type="submit" class="ts button" value="Get!"/>
    </form>
  </div>
</div>

<!--Waves Container-->
<div>
  <svg class="waves" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28" preserveAspectRatio="none" shape-rendering="auto">
<defs>
<path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18 v44h-352z" />
</defs>
<g class="parallax">
<use xlink:href="#gentle-wave" x="48" y="0" fill="rgba(255,255,255,0.7" />
<use xlink:href="#gentle-wave" x="48" y="3" fill="rgba(255,255,255,0.5)" />
<use xlink:href="#gentle-wave" x="48" y="5" fill="rgba(255,255,255,0.3)" />
<use xlink:href="#gentle-wave" x="48" y="7" fill="#fff" />
</g>
</svg>
</div>
<!--Waves end-->

</div>
<!--Header ends-->

<!--Content starts-->
<div class="content flex">
<p><a href="https://codepen.io/goodkatz/pen/LYPGxQz" target="_blank">Daniel Österman</a> | 2019 | Modified by oToToT</p>
</div>
<!--Content ends-->
</body>
<script src="javascripts/moment.min.js"></script>
<script src="javascripts/flatpickr.min.js"></script>
<script>
flatpickr(".flatpickr", {enableTime: true});
document.getElementById('form').addEventListener('submit',function(e){
  e.preventDefault();
  let d = moment(document.getElementById("end").value);
  document.getElementById("end").value = d.valueOf();
  this.submit();
});
</script>
</html>