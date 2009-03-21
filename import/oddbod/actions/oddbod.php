<?php
/**
 * This is the main action for Oddbod.
 */

global $CONFIG;

require_once($CONFIG->pluginspath . "oddbod/util/utils.php");
require_once($CONFIG->pluginspath . "oddbod/importers/oddimport.php");
require_once($CONFIG->pluginspath . "oddbod/importers/oddbodexception.php");

// The ODD files can be huge!
set_time_limit(0);

try {
  $import = new ODDImport(get_input("file"));
  oddlog("importing: ".$import->getImportType());
  $import->import();
}
catch(Exception $ex) {
  oddlog($ex->getMessage());
}

?>