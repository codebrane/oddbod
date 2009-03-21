<?php
    
 /**
	 * Oddbod view page
	 *
	 * @package Oddbod
	 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
	 * @author Alistair Young <alistair@codebrane.com>
	 * @copyright codeBrane 2009
	 * @link http://codebrane.com/blog/
	 */
	 
	global $CONFIG;
	$action = $vars['url']."action/oddbod/import"
?>

<div>
	<form action="<? echo $action; ?>" method="post">
		<input type="text" name="file" value="enter file path" /><br />
		<input type="submit" value="Import" />
		<input type="hidden" name="mode" value="import" />
	</form>
</div>
