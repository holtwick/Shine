<?PHP
	require 'includes/master.inc.php';
	$Auth->requireAdmin('login.php');

	if(isset($_POST['btnNewApp']) && strlen($_POST['name']))
	{
		$a = new Application();
		$a->name = $_POST['name'];
		$a->insert();
		redirect('application.php?id=' . $a->id);
	}

	$apps   = DBObject::glob('Application', 'SELECT * FROM applications ORDER BY name');
	$orders = DBObject::glob('Order', 'SELECT * FROM orders ORDER BY dt DESC LIMIT 5');

	$db = Database::getDatabase();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>Shine</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
    <link rel="stylesheet" href="http://yui.yahooapis.com/2.7.0/build/reset-fonts-grids/reset-fonts-grids.css" type="text/css">
    <link rel="stylesheet" href="css/yuiapp.css" type="text/css">
	<link rel="stylesheet" href="js/jquery.fancybox.css" type="text/css" media="screen">
</head>
<body class="rounded">
    <div id="doc3" class="yui-t6">

        <div id="hd">
            <h1>Shine</h1>
            <div id="navigation">
                <ul id="primary-navigation">
                    <li class="active"><a href="index.php">Applications</a></li>
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="feedback.php">Feedback</a></li>
                    <li><a href="stats.php">Sparkle Stats</a></li>
                </ul>

                <ul id="user-navigation">
                    <li><a href="settings.php">Settings</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
                <div class="clear"></div>
            </div>
        </div>

        <div id="bd">
            <div id="yui-main">
                <div class="yui-b"><div class="yui-g">

                    <div class="block">
                        <div class="hd">
                            <h2>Your Applications</h2>
                        </div>
                        <div class="bd">
                            <table>
                                <thead>
                                    <tr>
                                        <td>Name</td>
                                        <td>Current Version</td>
										<td>Last Release Date</td>
										<td>Downloads / Updates</td>
										<td align="right">Orders (net)</td>
                                        <td align="right">Feedback</td>
                                    </tr>
                                </thead>
                                <tbody>
									<?PHP foreach($apps as $a) : ?>
									<tr>
	                                    <td><a href="versions.php?id=<?PHP echo $a->id;?>"><?PHP echo $a->name; ?></a></td>
	                                    <td><?PHP echo $a->strCurrentVersion(); ?></td>
										<td><?PHP echo $a->strLastReleaseDate(); ?></td>
										<td><a href="versions.php?id=<?PHP echo $a->id; ?>"><?PHP


										$versions = DBObject::glob('Version', "SELECT * FROM versions WHERE app_id = '{$a->id}' ORDER BY dt DESC LIMIT 1");
										foreach($versions as $v) {
										    $total = $v->downloads + $v->updates;
                                            echo ("<strong>$total</strong> = ");
                                            echo number_format($v->downloads);
                                            if($v->downloads) {
                                                $pd = round($v->downloads / $total * 100);
                                                echo (" ($pd%)");
                                            }
                                            echo (" + ");
                                            echo number_format($v->updates);
                                            if($v->updates) {
                                                $pu = round($v->updates / $total * 100);
                                                echo (" ($pu%)");
                                            }
										}


										?></a></td>
										<td align="right"><strong><?PHP
echo "<a href='orders.php?id=$a->id'>";
$n = $db->getValue("SELECT SUM(mc_gross-mc_fee) FROM orders WHERE type = 'PayPal' AND app_id = $a->id");
echo number_format($n, 2)." €";
echo "</a>";

// echo $db->getValue("SELECT COUNT(*) FROM orders WHERE type = 'PayPal' AND app_id = $a->id");


										?></strong></td>
                                        <td align="right"><a href="feedback.php"><?PHP echo $a->numFeedbacksUnread(); ?></a></td>
									</tr>
									<?PHP endforeach; ?>
                                </tbody>
                            </table>
						</div>
					</div>

                </div></div>
            </div>
            <div id="sidebar" class="yui-b">

					<?PHP

					$db = Database::getDatabase();

					// Downloads
					$sel = "TIME_FORMAT(dt, '%Y%m%d%H')";
					$order_totals    = $db->getRows("SELECT $sel as dtstr, COUNT(*) FROM downloads WHERE  NOT(user_agent LIKE '%cfnetwork/%') AND DATE_ADD(dt, INTERVAL 24 HOUR) > NOW() GROUP BY dtstr ORDER BY $sel ASC");
					$opw             = new googleChart(implode(',', gimme($order_totals, 'COUNT(*)')), 'bary');
					$opw->showGrid   = 1;
					$opw->dimensions = '280x100';
					$opw->setLabelsMinMax(4,'left');
					$opw_fb = clone $opw;
					$opw_fb->dimensions = '640x400';

					?>

				<div class="block">
					<div class="hd">
						<h2>New Downloads 24 Hours</h2>
					</div>
					<div class="bd">
						<a href="<?PHP echo $opw_fb->draw(false); ?>" class="fb"><?PHP $opw->draw(); ?></a>
					</div>
				</div>

<?PHP

    $db = Database::getDatabase();

    // Downloads
    $sel = "TIME_FORMAT(dt, '%Y%m%d%H')";
    $order_totals    = $db->getRows("SELECT $sel as dtstr, COUNT(*) FROM downloads WHERE user_agent LIKE '%cfnetwork/%' AND  DATE_ADD(dt, INTERVAL 24 HOUR) > NOW() GROUP BY dtstr ORDER BY $sel ASC");
    $opw             = new googleChart(implode(',', gimme($order_totals, 'COUNT(*)')), 'bary');
    $opw->showGrid   = 1;
    $opw->dimensions = '280x100';
    $opw->setLabelsMinMax(4,'left');
    $opw_fb = clone $opw;
    $opw_fb->dimensions = '640x400';

    ?>

<div class="block">
<div class="hd">
<h2>Updates 24 Hours</h2>
</div>
<div class="bd">
<a href="<?PHP echo $opw_fb->draw(false); ?>" class="fb"><?PHP $opw->draw(); ?></a>
</div>
</div>

					<?PHP

					$db = Database::getDatabase();

					// Downloads
					$sel = "TO_DAYS(dt)";
					$order_totals    = $db->getRows("SELECT $sel as dtstr, COUNT(*) FROM downloads WHERE DATE_ADD(dt, INTERVAL 30 DAY) > NOW() GROUP BY $sel ORDER BY $sel ASC");
					$opw             = new googleChart(implode(',', gimme($order_totals, 'COUNT(*)')), 'bary');
					$opw->showGrid   = 1;
					$opw->dimensions = '280x100';
					$opw->setLabelsMinMax(4,'left');
					$opw_fb = clone $opw;
					$opw_fb->dimensions = '640x400';

					?>

				<div class="block">
					<div class="hd">
						<h2>Downloads 30 Days</h2>
					</div>
					<div class="bd">
						<a href="<?PHP echo $opw_fb->draw(false); ?>" class="fb"><?PHP $opw->draw(); ?></a>
					</div>
				</div>

<?php /*
				<div class="block">
					<div class="hd">
						<h2>Recent Orderes (<?PHP echo Order::totalOrders(); ?> total)</h2>
					</div>
					<div class="bd">
						<ul class="biglist">
							<?PHP foreach($orders as $o) : ?>
							<li><a href="order.php?id=<?PHP echo $o->id; ?>"><?PHP echo $o->payer_email; ?></a></li>
							<?PHP endforeach; ?>
						</ul>
					</div>
				</div>

*/ ?>

				<div class="block">
					<div class="hd">
						<h2>Create an Application</h2>
					</div>
					<div class="bd">
						<form action="index.php" method="post">
		                    <p>
								<label for="test1">Application Name</label>
		                        <input type="text" class="text" name="name" id="appname" value="">
		                    </p>
							<p><input type="submit" name="btnNewApp" value="Create Application" id="btnNewApp"></p>
						</form>
					</div>
				</div>

            </div>
        </div>

        <div id="ft"></div>
    </div>
	<script type="text/javascript" src="js/jquery-1.3.2.min.js"></script>
	<script type="text/javascript" src="js/jquery.fancybox-1.2.1.pack.js"></script>
	<script type="text/javascript" charset="utf-8">
 		$(".fb").fancybox({ 'zoomSpeedIn': 300, 'zoomSpeedOut': 300, 'overlayShow': false });
	</script>
</body>
</html>
