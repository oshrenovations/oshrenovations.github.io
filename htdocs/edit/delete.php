<?php include("login.php"); ?>
<?php include("config.php"); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">

<html>

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
    		<li><a href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/">Content Blocks</a></li>
    		<li class="current"><a href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/blocks/img/manage.php">Image Manager</a></li>
    		<li><a href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/list-backups.php">Backup</a></li>
    		</ul>
    		<a href="."><img src="img/new-logo2.gif" alt="Content Manager"></a>
    	</div>
    </div>

<div class="main">
	<div class="main-inner">
			
			<?php 
$filename = $_GET['f'];
$upload_dir = './blocks/img/';
$file_path = $upload_dir . $filename;
if(is_file($file_path)) {
	unlink($file_path);
	echo "<p>The file: <b>" . $filename . "</b> has been deleted.</p>";
	echo "<p>Back to <a href=\"http://$domain/$pulse_dir/blocks/img/manage.php\">Image Manager</a></p>";
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