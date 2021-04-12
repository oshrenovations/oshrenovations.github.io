<?php include("login.php"); ?>
<?php include("config.php"); ?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">

<html>

<head>
<style type="text/css">

</style>
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
if ((($_FILES["file"]["type"] == "image/gif")
|| ($_FILES["file"]["type"] == "image/jpeg")
|| ($_FILES["file"]["type"] == "image/png")
|| ($_FILES["file"]["type"] == "image/pjpeg"))
&& ($_FILES["file"]["size"] < 100000))
  {
  if ($_FILES["file"]["error"] > 0)
    {
    echo "Return Code: " . $_FILES["file"]["error"] . "<br />";
    }
  else
    {
    echo "Upload: " . $_FILES["file"]["name"] . "<br />";
    echo "Type: " . $_FILES["file"]["type"] . "<br />";
    echo "Size: " . ($_FILES["file"]["size"] / 1024) . " Kb<br />";
    

    if (file_exists("blocks/img/" . $_FILES["file"]["name"]))
      {
      echo $_FILES["file"]["name"] . " already exists. ";
      }
    else
      {
      move_uploaded_file($_FILES["file"]["tmp_name"],
      "blocks/img/" . $_FILES["file"]["name"]);
      echo "Stored in: " . "blocks/img/" . $_FILES["file"]["name"];
      }
    }
  }
else
  {
  echo "<b>Invalid file:</b> 300K max size and JPG, GIF, PNG only";
  }
?>

<p><a href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/blocks/img/manage.php">View Images</a></p>


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
