<?PHP
	require 'includes/master.inc.php';
	$Auth->requireAdmin('login.php');
	
	$app = new Application($_GET['id']);
	if(!$app->ok()) redirect('index.php');

	if (!function_exists("sys_get_temp_dir")) {
	   function sys_get_temp_dir() {
	      # check possible alternatives
	      ($temp = ini_get("temp_dir"))
	      or
	      ($temp = $_SERVER["TEMP"])
	      or
	      ($temp = $_SERVER["TMP"])
	      or
	      ($temp = "/tmp");
	      # fin
	      return($temp);
	   }
	}

	if(isset($_POST['btnCreateVersion']))
	{
		$Error->blank($_POST['version_number'], 'Version Number');
		$Error->blank($_POST['human_version'], 'Human Readable Version Number');
		
		if(!$_POST['url']) {
			$Error->upload($_FILES['file'], 'file');
		}
		
		if($Error->ok())
		{			
			$v = new Version();
			$v->app_id         = $app->id;
			$v->version_number = $_POST['version_number'];
			$v->human_version  = $_POST['human_version'];
			$v->release_notes  = $_POST['release_notes'];
			$v->url            = $_POST['url'];
			$v->dt             = dater();
			$v->downloads      = 0;
			$v->updates        = 0;
			
			$tmpfile = "";
			if($v->url) {
				$tmpfile = tempnam(sys_get_temp_dir(), 'sparkle_stdin');
				
				$data = get_data_from_url($v->url);
				if(!$data) {
				 	die("The file at <a href='$v->url'>$v->url</a> does not exist or is empty!");	
				}
				
				file_put_contents($tmpfile, $data);
				
			} else {
				$tmpfile = $_FILES['file']['tmp_name'];	
			}
			
			$v->filesize       = filesize($tmpfile);
			$v->signature      = sign_file($tmpfile, $app->sparkle_pkey);
			
			if(!$v->url) {
				$object = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $app->name)) . "_" . $v->version_number . "." . substr($_FILES['file']['name'], -3);
				if($app->s3bucket && $app->s3path) {
					$v->url = slash($app->s3path) . $object;
					$info   = parse_url($app->s3path);
					$object = slash($info['path']) . $object;
					chmod($tmpfile, 0755);
					$s3 = new S3($app->s3key, $app->s3pkey);
					$s3->putObject($app->s3bucket, $object, $tmpfile, true);
				} else {
					die ("Configure your Amazon S3 account or modify version-new.php file.");
				
					/*
					$v->url = '/Users/dirk/work/wordpress/shine/' . $object;
					copy($_FILES['file']['tmp_name'], '/Users/dirk/work/wordpress/shine/' . $object);
					*/
				}
			} else {
				
				// Cleanup download
				unlink($tmpfile);
			}
			
			$v->insert();

			redirect('versions.php?id=' . $app->id);
		}
		else
		{
			$version_number = $_POST['version_number'];
			$human_version  = $_POST['human_version'];
			$release_notes  = $_POST['release_notes'];
			$url            = $_POST['url'];
		}
	}
	else
	{
		$version_number = '';
		$human_version  = '';
		$release_notes  = '';
		$url            = '';
	}

	function get_data_from_url($url) 
	{ 
	   $ch = curl_init();
	   $timeout = 5;
	   curl_setopt($ch,CURLOPT_URL,$url);
	   curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
	   curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
	   $data = curl_exec($ch);
	   curl_close($ch);
	   return $data;
	}
	
	function sign_file($filename, $keydata)
    {
	
		/* 
		If shell_exec() throws an error you may need to add
		the following line to your VirtualHost config for apache:
		
		php_admin_flag safe_mode Off    
		*/
	
		$cmd1 = 'openssl dgst -sha1 -binary < ' . $filename;
		$binary = shell_exec($cmd1);
		$stdin = tempnam(sys_get_temp_dir(), 'sparkle_stdin');
		file_put_contents($stdin, $binary);

		$keyin = tempnam(sys_get_temp_dir(), 'sparkle_keyin');
		file_put_contents($keyin, $keydata);

		$cmd2 = "openssl dgst -dss1 -sign $keyin < $stdin";
		$signed = shell_exec($cmd2);

		// Cleanup
		unlink($stdin);
		unlink($keyin);
		
		return base64_encode($signed);		
    }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
 "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <title>Shine</title>
    <meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
    <link rel="stylesheet" href="http://yui.yahooapis.com/2.7.0/build/reset-fonts-grids/reset-fonts-grids.css" type="text/css">
    <link rel="stylesheet" href="css/yuiapp.css" type="text/css">
	<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
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
						<?PHP echo $Error; ?>
                        <div class="hd">
                            <h2>Applications</h2>
							<ul>
								<li><a href="application.php?id=<?PHP echo $app->id; ?>"><?PHP echo $app->name; ?></a></li>
								<li><a href="versions.php?id=<?PHP echo $app->id; ?>">Versions</a></li>
								<li class="active"><a href="version-new.php?id=<?PHP echo $app->id; ?>">Release New Version</a></li>
							</ul>
							<div class="clear"></div>
                        </div>
                        <div class="bd">
							<form action="version-new.php?id=<?PHP echo $app->id; ?>" method="post" enctype="multipart/form-data">
								<p><label for="version_number">Sparkle Version Number</label> <input type="text" name="version_number" id="version_number" value="<?PHP echo $version_number;?>" class="text"></p>
								<p><label for="human_version">Human Readable Version Number</label> <input type="text" name="human_version" id="human_version" value="<?PHP echo $human_version;?>" class="text"></p>
								<p><label for="release_notes">Release Notes</label> <textarea class="text ckeditor" name="release_notes" id="release_notes"><?PHP echo $release_notes; ?></textarea></p>
								<h3>You have to provide only one of the following informations</h3>
								<p><label for="file">Option 1: Application Archive</label> <input type="file" name="file" id="file"></p>
								<p>
									<label for="url">Option 2: Download URL (File needs to exist at this location already!)</label> 
									<input type="text" name="url" id="url" value="<?PHP echo $url;?>" class="text">
									<span class="info">The file will be downloaded to calculate the Sparkle signature.</span>
								</p>
								<p><input type="submit" name="btnCreateVersion" value="Create Version" id="btnCreateVersion"></p>
							</form>
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
