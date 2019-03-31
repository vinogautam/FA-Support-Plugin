<title>Waiting Hall</title>
<header>
	Waiting Hall
</header>
<div class="container">
<?php 
if(isset($_GET['waitinghall'])){

	$decode=explode('#', base64_decode(base64_decode($_GET['waitinghall'])));
  	$meeting_id = $decode[0];
  	$pid = $decode[1];
	global $wpdb;

	$counted = $_GET['waitinghall'];
	//$counted = base64_encode(base64_encode($decode[0].'#'.$decode[1].'#1'));

	$meeting = $wpdb->get_row("select * from ".$wpdb->prefix."meeting where id=".$meeting_id);
	if(strtotime('now') > strtotime($meeting->meeting_date)){
		wp_redirect(get_permalink('meeting').'?id='.$_GET['waitinghall']);exit;
	}
	else{
		$apptime = strtotime($meeting->meeting_date);
		if($pid != 0){
		    $participants = $wpdb->get_row("select * from ".$wpdb->prefix . "meeting_participants where id=".$pid);
		  }
		?>

		<p>Dear <?= $pid == 0 ? 'Admin' : $participants->name ?> your meeting scheduled on <?= $meeting->meeting_date;?>, Your meeting begin in</p>

		<div id="clockdiv">
			<div>
				<span class="days"></span>
				<div class="smalltext">Days</div>
			</div>
			<div>
				<span class="hours"></span>
				<div class="smalltext">Hours</div>
			</div>
			<div>
				<span class="minutes"></span>
				<div class="smalltext">Minutes</div>
			</div>
			<div>
				<span class="seconds"></span>
				<div class="smalltext">Seconds</div>
			</div>
		</div>
		<script>
		function getTimeRemaining(endtime) {
		  var t = Date.parse(endtime) - Date.parse(new Date());
		  var seconds = Math.floor((t / 1000) % 60);
		  var minutes = Math.floor((t / 1000 / 60) % 60);
		  var hours = Math.floor((t / (1000 * 60 * 60)) % 24);
		  var days = Math.floor(t / (1000 * 60 * 60 * 24));
		  return {
		    'total': t,
		    'days': days,
		    'hours': hours,
		    'minutes': minutes,
		    'seconds': seconds
		  };
		}

		function initializeClock(id, endtime) {
		  var clock = document.getElementById(id);
		  var daysSpan = clock.querySelector('.days');
		  var hoursSpan = clock.querySelector('.hours');
		  var minutesSpan = clock.querySelector('.minutes');
		  var secondsSpan = clock.querySelector('.seconds');

		  function updateClock() {
		    var t = getTimeRemaining(endtime);

		    daysSpan.innerHTML = t.days;
		    hoursSpan.innerHTML = ('0' + t.hours).slice(-2);
		    minutesSpan.innerHTML = ('0' + t.minutes).slice(-2);
		    secondsSpan.innerHTML = ('0' + t.seconds).slice(-2);

		    if (t.total <= 0) {
		      	clearInterval(timeinterval);
		      	window.location.assign("<?php echo get_permalink('meeting').'?id='.$counted;?>");
		    }
		  }

		  updateClock();
		  var timeinterval = setInterval(updateClock, 1000);
		}

		var deadline = new Date(<?= date("Y", $apptime);?>, <?= date("m", $apptime)-1;?>, <?= date("d", $apptime);?>, <?= date("H", $apptime);?>, <?= date("i", $apptime);?>, <?= date("s", $apptime);?>);
		initializeClock('clockdiv', deadline);
		</script>
		<style>
		body{margin: 0}
		p{text-align: center;}
		header {
		    background: #3c8dbc;
		    padding: 10px 0;
		    text-align: center;
		    color: #fff;
		    font-size: 25px;
		}
		#clockdiv{
			width: 100%;
			font-family: sans-serif;
			color: #fff;
			display: inline-block;
			font-weight: 100;
			text-align: center;
			font-size: 30px;
		}

		#clockdiv > div{
			padding: 10px;
			border-radius: 3px;
			background: #3c8dbc;
			display: inline-block;
		}

		#clockdiv div > span{
			padding: 15px;
			border-radius: 3px;
			background: #357CA5;
			display: inline-block;
		}

		.smalltext{
			padding-top: 5px;
			font-size: 16px;
		}
		</style>
		<?php
		
	}
}
else
echo '<p>No meetings found for you!!!</p>';
?>
</div>