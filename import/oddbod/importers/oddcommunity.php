<?php
global $CONFIG;

/**
 * This class is the importer for communities, which are created as Elgg Groups.
 * 
 * @author alistair
 *
 */
class ODDCommunity {
  public function import($odddoc) {
    global $CONFIG;
    $xpath = new DOMXpath($odddoc);
	$entity_elements = $xpath->query("//entity");
	
	if (!is_null($entity_elements)) {
	  foreach ($entity_elements as $entity_element) {
	    $comm_is_valid = true;
        $icon_data = "";
        $moderation = "yes";
			
        $metadata_for_entity_elements = $xpath->query("//metadata[@entity_uuid='".$entity_element->getAttribute("uuid")."']");
        foreach ($metadata_for_entity_elements as $metadata_for_entity_element) {
          // active - don't touch inactive groups
          if ($metadata_for_entity_element->getAttribute("name") == "active") {
            if (clean($metadata_for_entity_element->nodeValue) == "no") {
              $comm_is_valid = false;
              oddlog("ERROR: not importing inactive group $username");
            }
          }
          
          // moderation
          if ($metadata_for_entity_element->getAttribute("name") == "moderation") {
            $moderation = clean($metadata_for_entity_element->nodeValue);
          }
          
          // username
          if ($metadata_for_entity_element->getAttribute("name") == "username") {
            $username = clean($metadata_for_entity_element->nodeValue);
          }
          
          // name
          if ($metadata_for_entity_element->getAttribute("name") == "name") {
            if ((strlen(clean($metadata_for_entity_element->nodeValue))) == 0) {
              $comm_is_valid = false;
              oddlog("ERROR: ".get_username_from_entity_uuid($entity_element->getAttribute("uuid"))." : no name");
            }
            else {
              $name = clean($metadata_for_entity_element->nodeValue);
            }
          }
          
          // owner
          if ($metadata_for_entity_element->getAttribute("name") == "owner") {
            if ((strlen(clean($metadata_for_entity_element->nodeValue))) == 0) {
              $comm_is_valid = false;
              oddlog("ERROR: ".get_username_from_entity_uuid($entity_element->getAttribute("uuid"))." : no owner");
            }
            else {
              $owner = get_user_by_username(clean($metadata_for_entity_element->nodeValue));
            }
          }
          
          // icon
          if ($metadata_for_entity_element->getAttribute("name") == "icon_data") {
            $icon_data = base64_decode($metadata_for_entity_element->nodeValue);
          }
        } // foreach ($metadata_for_entity_elements as $metadata_for_entity_element)
        
        if ($comm_is_valid) {
          $comm = find_group_by_name($name);
          if (!$comm) {
            oddlog("CREATE: $name owned by ".$owner->username);
            $comm = new ElggGroup();
            $comm->name = $name;
            $comm->membership = ACCESS_PUBLIC;
            $comm->owner_guid = $owner->getGUID();
            $comm->container_guid = $owner->getGUID();
            $comm->save();
            $comm->join($owner);
          }
          else {
            oddlog("UPDATE: ".$comm->getGUID()." ".$comm->name);
          }
          
          // We now have either a new or existing Community (Group) so add the icon
          if ($icon_data != "") {
            $prefix = "groups/".$comm->guid;
            $filehandler = new ElggFile();
            $filehandler->owner_guid = $comm->owner_guid;
            $filehandler->setFilename($prefix . ".jpg");
            $filehandler->open("write");
            $filehandler->write($icon_data);
            $filehandler->close();
            
            $thumbtiny = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),25,25, true);
            $thumbsmall = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),40,40, true);
            $thumbmedium = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),100,100, true);
            $thumblarge = get_resized_image_from_existing_file($filehandler->getFilenameOnFilestore(),200,200, false);
            
            if ($thumbtiny) {
              $thumb = new ElggFile();
              $thumb->owner_guid = $comm->owner_guid;
              $thumb->setMimeType('image/jpeg');
              
              $thumb->setFilename($prefix."tiny.jpg");
              $thumb->open("write");
              $thumb->write($thumbtiny);
              $thumb->close();
              
              $thumb->setFilename($prefix."small.jpg");
              $thumb->open("write");
              $thumb->write($thumbsmall);
              $thumb->close();
              
              $thumb->setFilename($prefix."medium.jpg");
              $thumb->open("write");
              $thumb->write($thumbmedium);
              $thumb->close();
              
              $thumb->setFilename($prefix."large.jpg");
              $thumb->open("write");
              $thumb->write($thumblarge);
              $thumb->close();
            } // if ($thumbtiny)
          } // if ($icon_data != "")
        } // if ($comm_is_valid_for_create)
	  } // foreach ($entity_elements as $entity_element)
	} // if (!is_null($entity_elements))
  } // public function import($odddoc)
}
?>