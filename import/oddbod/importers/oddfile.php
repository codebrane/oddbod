<?php
/**
 * This class is the importer for files
 * 
 * @author alistair
 *
 */
class ODDFile {
	/** Mappings for access */
  private $file_access_mode;
	
	function __construct() {
    $file_access_mode = array("PUBLIC"    => ACCESS_PUBLIC,
                              "LOGGED_IN" => ACCESS_LOGGED_IN,
                              "PRIVATE"   => ACCESS_PRIVATE);
	}
	
	public function import($odddoc) {
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
    		oddlog("CREATE: $filename");
    		$file = new FilePluginFile();
    		$file->setFilename("file/".$filename);
    		$file->originalfilename = $filename;
    		$file->setMimeType($mime_type);
    		$file->subtype="file";
    		$file->access_id = $access;
	    	$file->open("write");
	    	$file->write($file_content);
	    	$file->close();
	    	$file->title = $title;
	    	$file->description = $description;
	    	if ($group == "") {
	    		$file->container_guid = $user->getGUID();
	    	}
	    	else {
	    		$file->container_guid = $group->getGUID();
	    	}
	    	$file->simpletype = get_general_file_type($mime_type);
	    	$result = $file->save();
	    	
	    	if ($result) {
          if (substr_count($file->getMimeType(),'image/')) {
			      $thumbnail = get_resized_image_from_existing_file($file->getFilenameOnFilestore(),60,60, true);
			      $thumbsmall = get_resized_image_from_existing_file($file->getFilenameOnFilestore(),153,153, true);
			      $thumblarge = get_resized_image_from_existing_file($file->getFilenameOnFilestore(),600,600, false);
			      
			      if ($thumbnail) {
			        $thumb = new ElggFile();
			        $thumb->setMimeType($mime_type);
			        
			        $thumb->setFilename($prefix."thumb".$filestorename);
			        $thumb->open("write");
			        $thumb->write($thumbnail);
			        $thumb->close();
			        
			        $file->thumbnail = $prefix."thumb".$filestorename;
			        
			        $thumb->setFilename($prefix."smallthumb".$filestorename);
			        $thumb->open("write");
			        $thumb->write($thumbsmall);
			        $thumb->close();
			        $file->smallthumb = $prefix."smallthumb".$filestorename;
			        
			        $thumb->setFilename($prefix."largethumb".$filestorename);
			        $thumb->open("write");
			        $thumb->write($thumblarge);
			        $thumb->close();
			        $file->largethumb = $prefix."largethumb".$filestorename;
			      }
          }
          
          // Update the times on the file
          $db_link = get_db_link("write");
          $query = "update elggentities set time_created = '$time', time_updated = '$time' where guid = '{$file->getGUID()}'";
          execute_query($query, $db_link);
	    	} // if ($result)
    	} // if ($file_content != "") {
    } // foreach ($entity_elements as $entity_element)
	}
}
?>