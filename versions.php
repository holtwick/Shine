<?PHP
	require 'includes/master.inc.php';
	$Auth->requireAdmin('login.php');
	
	$app = new Application($_GET['id']);
	if(!$app->ok()) redirect('index.php');
	$versions = $app->versions();
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>Shine</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
    <link rel="stylesheet" href="http://yui.yahooapis.com/2.7.0/build/reset-fonts-grids/reset-fonts-grids.css" type="text/css">
    <link rel="stylesheet" href="css/yuiapp.css" type="text/css">
	<link rel="alternate" type="application/rss+xml" title="Appcast Feed" href="appcast.php?id=<?PHP echo $app->id; ?>" />
</head>
<body class="rounded">
    <div id="doc3" class="yui-t0">

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

                    <div class="block tabs spaces">
                        <div class="hd">
                            <h2>Applications</h2>
							<ul>
								<li><a href="application.php?id=<?PHP echo $app->id; ?>"><?PHP echo $app->name; ?></a></li>
								<li class="active"><a href="versions.php?id=<?PHP echo $app->id; ?>">Versions</a></li>
								<li><a href="version-new.php?id=<?PHP echo $app->id; ?>">Release New Version</a></li>
							</ul>
							<div class="clear"></div>
                        </div>
                        <div class="bd">
							<table>
								<thead>
									<tr>
										<th>Human Readable Version</th>
										<th>Sparkle Version Number</th>
										<th>Release Date</th>
										<th>Downloads + Updates</th>
									</tr>
								</thead>
								<tbody>
									<?PHP foreach($versions as $v) : ?>
									<tr>
										<td><a href="version-edit.php?id=<?PHP echo $v->id; ?>"><?PHP echo $v->human_version; ?></a></td>
										<td><?PHP echo $v->version_number; ?></td>
										<td><?PHP echo dater($v->dt, 'Y-m-d h:i'); ?></td>
										<td><?PHP 
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
										?></td>
									</tr>
									<?PHP endforeach; ?>
								</tbody>
							</table>
						</div>
					</div>
              
                </div></div>
            </div>
            <div id="sidebar" class="yui-b">

            </div>
        </div>

        <div id="ft"></div>
    </div>
</body>
</html>
