<?php
global $CONFIG;

require_once($CONFIG->pluginspath . "oddbod/importers/odduser.php");
require_once($CONFIG->pluginspath . "oddbod/importers/oddprofile.php");
require_once($CONFIG->pluginspath . "oddbod/importers/oddfriend.php");
require_once($CONFIG->pluginspath . "oddbod/importers/oddcommunity.php");
require_once($CONFIG->pluginspath . "oddbod/importers/oddblog.php");
require_once($CONFIG->pluginspath . "oddbod/importers/oddmembership.php");
require_once($CONFIG->pluginspath . "oddbod/importers/oddfile.php");

/**
 * This is the main Oddbod importer action class
 * 
 * @author alistair
 *
 */
class ODDImport {
  private $odddoc;
  private $import_class;
  
  public function __construct($odd_file_path) {
   	$this->odddoc = new DOMDocument();
   	
   	if (is_dir($odd_file_path)) {
   		$dh = opendir($odd_file_path);
   		while (false !== ($file = readdir($dh))) {
   			if ($file != "." && $file != "..") {
   				// Just need the first ODD file to find what types are in the directory
   				$odd_file = $odd_file_path."/".$file;
   				break;
   			}
   		}
   		closedir($dh);
   	}
   	else {
      $odd_file = $odd_file_path;
   	}
   	
   	$this->odddoc->loadXML(utf8_encode(implode("", file($odd_file))));
    $last_error = libxml_get_last_error();
    if ($last_error) {
      throw new OddbodException($last_error->message);
    }
    
    switch ($this->getImportType()) {
      case "user":
        $this->import_class = new ODDUser();
        break;
        
      case "community":
        $this->import_class = new ODDCommunity();
        break;
      
      case "profile-users":
        $this->import_class = new ODDProfile(ODDProfile::MODE_USERS);
        break;
        
      case "profile-communities":
        $this->import_class = new ODDProfile(ODDProfile::MODE_COMMUNITIES);
        break;
        
      case "friend":
        $this->import_class = new ODDFriend();
        break;
        
      case "user-blogs":
        $this->import_class = new ODDBlog(ODDBlog::MODE_USER_BLOGS);
        break;
        
      case "community-blogs":
        $this->import_class = new ODDBlog(ODDBlog::MODE_COMMUNITY_BLOGS);
        break;
        
      case "memberships":
        $this->import_class = new ODDMembership();
        break;
        
      case "files":
        $this->import_class = new ODDFile($odd_file_path);
        break;
    }
  }
  
  public function import() {
    $this->import_class->import($this->odddoc);
  }
  
  public function getImportType() {
    return clean($this->odddoc->documentElement->getAttribute("type"));
  }
}
?>