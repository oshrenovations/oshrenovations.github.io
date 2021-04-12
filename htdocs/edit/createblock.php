<?php include("login.php"); ?>
<?php include("config.php"); ?>

<?php

		@$filename = strip_tags($_POST['blockname']) . ".html";
		$blockname = str_replace(' ', '-', $filename);	

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>
    <title>Content Manager</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="style.css" media="all">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
</head>

<body>
    <div class="header">
    	<div>
    		
    		<ul>
    		<li class="current"><a href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/">Content Blocks</a></li>
    		<li><a href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/blocks/img/manage.php">Image Manager</a></li>
    		<li><a href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/list-backups.php">Backup</a></li>
    		</ul>
    		<a href="."><img src="img/new-logo2.gif" alt="Content Manager"></a>
    	</div>
    </div>

<div class="main">
	
	<div class="main-inner">

		<h1>Create a block</h1>						
			
			<div class="new-block">
			
			
			
            <form class="block-form" action="<?php $_SERVER['PHP_SELF']; ?>" method="post">
					<label for="blockname">Block Name:</label>
					<input type="text" name="blockname" id="blockname" /></label>
					<input type="submit" name="submit" value="Create" />			
			</form>
			</div><br>
			<?php
			
				if(strlen(@$_POST['blockname']) == 0) {
					
					
				}
				else {
					$block_total = "blocks/" . $blockname;
					$block_handle = fopen($block_total, 'w') or die("{$blockname} could not be created.");
					fclose($block_handle);
					echo "<p><b>" . $blockname . "</b> was successfully created. Go <a href=\"view.php?f=blocks/{$blockname}\">edit</a> it!</p>";
				}
			
			?>

			<div class="clear"></div> 

			</div>
    </div>

    <div class="footer">
        <div class="version">
            <a href="http://kmastin.sasktelwebsite.net">&copy; 2010 Keith Mastin</a>
        </div>
    </div>

</body>
</html>
