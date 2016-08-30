<?php
echo get_header();
?>
<div class="container">
<?php 
if(isset($_GET['app'])){

	$app = base64_decode(base64_decode($_GET['app']));
	global $wpdb;

	$appointments = $wpdb->get_row("select * from ".$wpdb->prefix."app_appointments where ID=".$app);
	if(count($appointments) && $appointments->status == 'confirmed')
	{
		$apptime = strtotime($appointments->start);
		if($apptime < strtotime("now")){
		?>
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
		      	jQuery.post('<?php echo site_url();?>/wp-admin/admin-ajax.php',{action: "join_chat", meeting: {name: "<?= $appointments->name; ?>", email: "<?= $appointments->email; ?>", status: 1}}, function(res){
					console.log(res);
					setTimeout(function(){window.location.assign("<?php echo site_url();?>");}, 3000);
				});
		    }
		  }

		  updateClock();
		  var timeinterval = setInterval(updateClock, 1000);
		}

		var deadline = new Date(<?= date("Y", $apptime);?>, <?= date("m", $apptime)-1;?>, <?= date("d", $apptime);?>, <?= date("H", $apptime);?>, <?= date("i", $apptime);?>);
		initializeClock('clockdiv', deadline);
		</script>
		<style>
		#clockdiv{
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
			background: #00BF96;
			display: inline-block;
		}

		#clockdiv div > span{
			padding: 15px;
			border-radius: 3px;
			background: #00816A;
			display: inline-block;
		}

		.smalltext{
			padding-top: 5px;
			font-size: 16px;
		}
		</style>
		<?php
		}
		else
		{?>
			<script>
				jQuery(document).ready(function(){
					jQuery.post('<?php echo site_url();?>/wp-admin/admin-ajax.php',{action: "join_chat", meeting: {name: "<?= $appointments->name; ?>", email: "<?= $appointments->email; ?>", status: 1}}, function(res){
						console.log(res);
						setTimeout(function(){window.location.assign("<?php echo site_url();?>");}, 3000);
					});
				});
			</script>
		<?php
		}
	}
	else
		echo '<p>No meetings found for you!!!</p>';
}
else
echo '<p>No meetings found for you!!!</p>';
?>
</div>
<?php
echo get_footer();
?>