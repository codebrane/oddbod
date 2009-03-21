<?
 /**
  * Oddbod migrator
  * 
  * @package Oddbod
  * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
  * @author Alistair Young <alistair@codebrane.com>
  * @copyright codeBrane 2009
  * @link http://codebrane.com/blog/
  */

function oddbod_init() {
	global $CONFIG;
	add_widget_type('oddbod', "Oddbod", "Oddbod Migrator");
}

register_elgg_event_handler('init', 'system', 'oddbod_init');
register_action("oddbod/import", false, $CONFIG->pluginspath . "oddbod/actions/oddbod.php");

?>
