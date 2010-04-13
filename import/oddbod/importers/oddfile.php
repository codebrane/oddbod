<?php
/**
 * This class is the importer for files. It's different from the other classes
 * as it takes a directory path instead of a file path. This is due to the
 * large content of the exported ODD files.
 * 
 * @author alistair
 *
 */
class ODDFile {
	/** The path to the directory containing the ODD files */
	private $odd_file_path;
	/** Mappings for access */
  private $file_access_mode;
	
	function __construct($odd_file_path) {
		$this->odd_file_path = $odd_file_path;
		
    $this->file_access_mode = array("PUBLIC"    => ACCESS_PUBLIC,
		                                "LOGGED_IN" => ACCESS_LOGGED_IN,
		                                "PRIVATE"   => ACCESS_PRIVATE);
	}
	
	/**
	 * This takes a directory path instead of a file path as the
	 * exported files are too big to fit in one ODD file.
	 * 
	 * @param $odd_file_path Ignored. Uses $this->odd_file_path instead
	 */
	public function import($odd_file_path) {
		$dh = opendir($this->odd_file_path);
		while (false !== ($file = readdir($dh))) {
			if ($file != "." && $file != "..") {
				$this->process($this->odd_file_path."/".$file);
			}
		}
		closedir($dh);
	}
	
	/**
	 * Imports files, one ODD at a time
	 * 
	 * @param $odddoc Full path to the ODD file
	 */
	public function process($odd_file) {
		global $CONFIG;
		
		$odddoc = new DOMDocument();
    $odddoc->loadXML(utf8_encode(implode("", file($odd_file))));
    $last_error = libxml_get_last_error();
    if ($last_error) {
      throw new OddbodException($last_error->message);
    }
		
		$xpath = new DOMXpath($odddoc);
    $entity_elements = $xpath->query("//entity");
    foreach ($entity_elements as $entity_element) {
    	$filename = "";
    	$mime_type = "";
    	$group = "";
    	$access = "";
    	$title = "";
    	$description = "";
    	$time = "";
    	$file_content = "";
    	
    	$metadata_elements = $xpath->query("//metadata[@entity_uuid='".$entity_element->getAttribute("uuid")."']");
    	foreach ($metadata_elements as $metadata_element) {
    		if ($metadata_element->getAttribute("name") == "original_name") {
    			$filename = clean($metadata_element->nodeValue);
    		}
    		
        if ($metadata_element->getAttribute("name") == "mime_type") {
          $mime_type = clean($metadata_element->nodeValue);
        }
        
        if ($metadata_element->getAttribute("name") == "access") {
        	$access = $this->file_access_mode[clean($metadata_element->nodeValue)];
        }
        
        if ($metadata_element->getAttribute("name") == "owner") {
          $user = get_user_by_username(clean($metadata_element->nodeValue));
        }
        
        if ($metadata_element->getAttribute("name") == "community") {
        	if (clean($metadata_element->nodeValue) != "") {
            $group = get_user_by_username(clean($metadata_element->nodeValue));
        	}
        }
        
        if ($metadata_element->getAttribute("name") == "title") {
          $title = clean($metadata_element->nodeValue);
        }
        
        if ($metadata_element->getAttribute("name") == "description") {
          $description = clean($metadata_element->nodeValue);
        }
        
        if ($metadata_element->getAttribute("name") == "time_uploaded") {
          $time = clean($metadata_element->nodeValue);
        }
        
        if ($metadata_element->getAttribute("name") == "content") {
          $file_content = base64_decode($metadata_element->nodeValue);
        }
    	} // foreach ($metadata_elements as $metadata_element)
    	
    	if ($file_content != "") {
    		if ($title == "") $title = $filename;
    		oddlog("CREATE: $filename");
    		$file = new ElggFile();
    		$file->setFilename("file/".$filename);
    		$file->originalfilename = $filename;
    		$file->setMimeType($mime_type);
    		$file->subtype="file";
    		$file->access_id = $access;
	    	$file->title = $title;
	    	$file->description = $description;
	    	$file->owner_guid = $user->getGUID();
	    	if ($group != "") {
	    		$file->container_guid = $group->getGUID();
	    	}
	    	else {
	    		$file->container_guid = $user->getGUID();
	    	}
	    	$file->simpletype = get_general_file_type($mime_type);
        $file->open("write");
        $file->write($file_content);
        $file->close();
	    	$result = $file->save();
	    	
	    	if ($result) {
          if (substr_count($file->getMimeType(),'image/')) {
			      $thumbnail = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 60, 60, true);
			      $thumbsmall = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 153, 153, true);
			      $thumblarge = get_resized_image_from_existing_file($file->getFilenameOnFilestore(), 600, 600, false);
			      
			      if ($thumbnail) {
			        $thumb = new ElggFile();
			        $thumb->owner_guid = $user->getGUID();
			        $thumb->setMimeType($mime_type);
			        
			        $thumb->setFilename("file/"."thumb".$filename);
			        $thumb->open("write");
			        $thumb->write($thumbnail);
			        $thumb->close();
			        
			        $file->thumbnail = "file/"."thumb".$filename;
			        
			        $thumb->setFilename("file/"."smallthumb".$filename);
			        $thumb->open("write");
			        $thumb->write($thumbsmall);
			        $thumb->close();
			        $file->smallthumb = "file/"."smallthumb".$filename;
			        
			        $thumb->setFilename("file/"."largethumb".$filename);
			        $thumb->open("write");
			        $thumb->write($thumblarge);
			        $thumb->close();
			        $file->largethumb = "file/"."largethumb".$filename;
			      }
          }
          
          // Update the times on the file
          $db_link = get_db_link("write");
          $query = "update {$CONFIG->dbprefix}entities set time_created = '$time', time_updated = '$time' where guid = '{$file->getGUID()}'";
          execute_query($query, $db_link);
	    	} // if ($result)
	    	else {
	    		oddlog("FILE ERROR: can't save file {$filename} for {$user->name}");
	    	}
    	} // if ($file_content != "") {
    } // foreach ($entity_elements as $entity_element)
	}
}
?>