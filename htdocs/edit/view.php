<?php include("login.php"); ?>
<?php include("config.php"); ?>
<?php 

if (isset($_POST["filename"])) {
	$fname = $_POST["filename"];
	$block = stripslashes($_POST["block"]);
	$fp = @fopen($fname, "w");
	if ($fp) {
		fwrite($fp, $block);
		fclose($fp);
	}
	
}
if (isset($_GET["f"])) 
	$fname = stripslashes($_GET["f"]);
	if (file_exists($fname)) 
		$fp = @fopen($fname, "r");
		if (filesize($fname) !== 0) 
			$loadblock = fread($fp, filesize($fname));
			$loadblock = htmlspecialchars($loadblock);
			fclose($fp);
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">

<html>
	<head>
		<title>Content Manager</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
		<link rel="stylesheet" type="text/css" href="style.css" media="all">
		<link rel="stylesheet" type="text/css" href="block-styles.css" media="all">
	<script type="text/javascript" src="ckeditor/ckeditor.js"></script> 
	</head>
	<body>
	
	<script type="text/javascript"> 
function select_all(obj) 
{ var text_val=eval(obj); 
text_val.select(); } 
</script>
	
			<div class="header"><div>
			
			
			<ul>
    		<li class="current"><a href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/">Content Blocks</a></li>
    		<li><a href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/blocks/img/manage.php">Image Manager</a></li>
    		<li><a href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/list-backups.php">Backup</a></li>
    		</ul>
			<a href="."><img src="img/new-logo2.gif" alt="Content Manager"></a>
			</div></div>
			
			
	
			<div class="main">	
			<div class="main-inner">
				<div class="breadcrumb"><a href=".">Home</a> | <?php echo $fname; ?></div>					
		<form method="post" action="">	
		<input type="hidden" name="filename" value="<?php echo $fname; ?>" />
		<textarea class="ckeditor" id="area2" name="block" cols="105" rows="20"><?php echo $loadblock; ?></textarea><br>
		<input type="submit" name="save_file" value="Save" /> &nbsp;
		Saved: <?php echo date("M j, Y g:i a", filemtime($fname)); ?>
	</form>
		
	

	<div class="howto">
	Embed Code:
	<input value='&lt;?php include("<?php echo $rootpath ?>/<?php echo $pulse_dir ?>/<?php echo $fname; ?>"); ?&gt;' onclick="select_all(this)" size="45">
	</div>	
				
				
					
			</div>	
			</div>
			
			<div class="footer">
                <div class="version">
            <a href="http://kmastin.sasktelwebsite.net">&copy; 2010 Keith Mastin;</a>
        </div>
    </div>
    
 </body>
</html>
