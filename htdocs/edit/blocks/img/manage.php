<?php include("../../login.php"); ?>
<?php include("../../config.php"); ?>
<?php
	# SETTINGS
	$max_width = 65;
	$max_height = 65;
	$per_page = 100;
		
	$page = $_GET['page'];
	
	$has_previous = false;
	$has_next = false;
	
	
	
	function getPictures() {
		global $page, $per_page, $has_previous, $has_next;
		if ( $handle = opendir(".") ) {
			
			include("../../config.php");
			
			$count = 0;
			$skip = $page * $per_page;
			
			if ( $skip != 0 )
				$has_previous = true;
			
			while ( $count < $skip && ($filename = readdir($handle)) !== false ) {
				if ( !is_dir($filename) && ($type = getPictureType($filename)) != '' )
					$count++;
			}
			$count = 0;
			
			$dir = "blocks/img";			
	
			while ( $count < $per_page && ($filename = readdir($handle)) !== false ) {
				if ( !is_dir($filename) && ($type = getPictureType($filename)) != '' ) {
					if ( ! is_dir('manage') ) {
						mkdir('manage');
					}
					if ( ! file_exists('manage/'.$filename) ) {
						makeThumb( $filename, $type );
					}
					echo "<div class=\"thumb\">";
					echo '<a href="'.$filename.'" rel="lightbox">';
					echo '<img src="manage/'.$filename.'"  />';
					echo '</a>';
					echo "<input value=\"http://"; 
					echo "$domain/$pulse_dir/$dir/$filename\" ";	
					echo 'size="30" onclick="select_all(this)">';
					echo "<br><a href=\"http://$domain/$pulse_dir/delete.php?f=$filename\" onclick=\"return confirm('Are you sure you want to delete?')\" >Delete</a>";
					echo "</div>";
					$count++;
				}
			}
			
			
			while ( ($filename = readdir($handle)) !== false ) {
				if ( !is_dir($filename) && ($type = getPictureType($filename)) != '' ) {
					$has_next = true;
					break;
				}
			}
		}
	}
	
	function getPictureType($filename) {
		$split = explode('.', $filename); 
		$ext = $split[count($split) - 1];
		if ( preg_match('/jpg|jpeg/i', $ext) ) {
			return 'jpg';
		} else if ( preg_match('/png/i', $ext) ) {
			return 'png';
		} else if ( preg_match('/gif/i', $ext) ) {
			return 'gif';
		} else {
			return '';
		}
	}
	
	function makeThumb( $filename, $type ) {
		global $max_width, $max_height;
		if ( $type == 'jpg' ) {
			$src = imagecreatefromjpeg($filename);
		} else if ( $type == 'png' ) {
			$src = imagecreatefrompng($filename);
		} else if ( $type == 'gif' ) {
			$src = imagecreatefromgif($filename);
		}
		if ( ($oldW = imagesx($src)) < ($oldH = imagesy($src)) ) {
			$newW = $oldW * ($max_width / $oldH);
			$newH = $max_height;
		} else {
			$newW = $max_width;
			$newH = $oldH * ($max_height / $oldW);
		}
		$new = imagecreatetruecolor($newW, $newH);
		imagecopyresampled($new, $src, 0, 0, 0, 0, $newW, $newH, $oldW, $oldH);
		if ( $type == 'jpg' ) {
			imagejpeg($new, 'manage/'.$filename);
		} else if ( $type == 'png' ) {
			imagepng($new, 'manage/'.$filename);
		} else if ( $type == 'gif' ) {
			imagegif($new, 'manage/'.$filename);
		}
		imagedestroy($new);
		imagedestroy($src);
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
"http://www.w3.org/TR/html4/strict.dtd">

<html>

<head>
    <title>Content Manager</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <link rel="stylesheet" type="text/css" href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/style.css" media="all">
    <meta name="ROBOTS" content="NOINDEX, NOFOLLOW">
	<script type="text/javascript" src="http://<?php echo $domain?>/<?php echo $pulse_dir?>/slimbox/js/mootools.js"></script>
	<script type="text/javascript" src="http://<?php echo $domain?>/<?php echo $pulse_dir?>/slimbox/js/slimbox.js"></script>
	<link rel="stylesheet" href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/slimbox/css/slimbox.css" type="text/css" 			media="screen" />
</head>

<body>


<script type="text/javascript"> 
function select_all(obj) 
{ var text_val=eval(obj); 
text_val.select(); } 
</script> 

    <div class="header">
    	<div>
    		
    		
    		<ul>
    		<li><a href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/">Content Blocks</a></li>
    		<li class="current"><a href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/blocks/img/manage.php">Image Manager</a></li>
    		<li><a href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/list-backups.php">Backup</a></li>
    		</ul>
    		
    		<a href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/"><img src="http://<?php echo $domain?>/<?php echo $pulse_dir?>/img/new-logo2.gif" alt="Content Manager"></a>
    		
    	</div>
    </div>

<div class="main">
	<div class="main-inner">		
		
		<div class="upload-button"> <a href="http://<?php echo $domain?>/<?php echo $pulse_dir?>/choose.php"><img src="http://<?php echo $domain?>/<?php echo $pulse_dir?>/img/upload.png"></a></div>		
		
		
		<?php getPictures(); ?>	
		

<div style="clear:both"></div>

<?php
	if ( $has_previous )
		echo '<p class="prev"><a href="?page='.($page - 1).'">&larr; Previous Page</a></p>';

	if ( $has_next )
		echo '<p class="next"><a href="?page='.($page + 1).'">Next Page &rarr;</a></p>';
?>

<div style="clear:both"></div>
		
		
		
		
		
		
            <div class="clear"> </div>  
            
            <div class="howto">
            <b>Help ></b> To place an image into a block:
            <br>Copy the Image URL. Open a content block. Paste URL into insert image tool. 
            </div> 
            
            <div class="howto">
            <b>Help ></b> To create a gallery of all images, embed this code into a web page:<br>
            <input value='&lt;?php include("<?php echo $rootpath ?>/<?php echo $pulse_dir ?>/blocks/img/gallery.php")?&gt;' onclick="select_all(this)" size="50">            
           &nbsp; <a href="http://<?php echo $domain?>/<?php echo $pulse_dir ?>/blocks/img/gallery.php">Rebuild / Preview</a>
            </div>
            
                 
            </div>
    </div>

    <div class="footer">
            <div class="version">
            <a href="http://kmastin.sasktelwebsite.net">&copy; 2010 Keith Mastin</a>
        </div>
    </div>
    </div>
    
</body>
</html>
