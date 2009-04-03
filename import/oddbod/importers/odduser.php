<?php
/**
 * This class is the importer for users and their icons.
 * 
 * @author alistair
 *
 */
class ODDUser {
  public function import($odddoc) {
	$xpath = new DOMXpath($odddoc);
	$entity_elements = $xpath->query("//entity");
	
	if (!is_null($entity_elements)) {
  	  foreach ($entity_elements as $entity_element) {
        $user_is_valid_for_create = true;
        $user_exists = false;
        $icon_data = "";
			
        // Check if the user already exists
        if (get_user_by_username(get_username_from_entity_uuid($entity_element->getAttribute("uuid")))) {
          $user = get_user_by_username(get_username_from_entity_uuid($entity_element->getAttribute("uuid")));
          $user_guid = $user->getGUID();
          $user_exists = true;
        }
			
		$metadata_for_entity_elements = $xpath->query("//metadata[@entity_uuid='".$entity_element->getAttribute("uuid")."']");
		foreach ($metadata_for_entity_elements as $metadata_for_entity_element) {
          if ($metadata_for_entity_element->getAttribute("name") == "username") {
            $username = ltrim(rtrim($metadata_for_entity_element->nodeValue));
          }
			
          if ($metadata_for_entity_element->getAttribute("name") == "name") {
            if ((strlen(rtrim($metadata_for_entity_element->nodeValue))) == 0) {
              $user_is_valid_for_create = false;
              oddlog("ERROR: ".get_username_from_entity_uuid($entity_element->getAttribute("uuid"))." : no name");
            }
            else {
              $name = ltrim(rtrim($metadata_for_entity_element->nodeValue));
            }
          }
			
          if ($metadata_for_entity_element->getAttribute("name") == "email") {
            if ((strlen(rtrim($metadata_for_entity_element->nodeValue))) == 0) {
              $user_is_valid_for_create = false;
              oddlog("ERROR: ".get_username_from_entity_uuid($entity_element->getAttribute("uuid"))." : no email");
            }
            else {
              $email = ltrim(rtrim($metadata_for_entity_element->nodeValue));
            }
          }
			
          if ($metadata_for_entity_element->getAttribute("name") == "icon_data") {
            $icon_data = base64_decode($metadata_for_entity_element->nodeValue);
          }
		} // foreach ($metadata_for_entity_elements as $metadata_for_entity_element)
			
		if ($user_is_valid_for_create) {
          if (!$user_exists) {
            oddlog("CREATE: $username");
            // Staff sometimes register as a student as well, with the same email
            $user_guid = register_user($username, uniqid(rand()), $name, $email, true);
            if (!$user_guid) {
              oddlog("ERROR: can't create user $username");
              continue;
            }
          } // if (!$user_exists)
          else {
            oddlog("UPDATE: ".$user->username);
          }
				
          $user = get_entity($user_guid);
				
          if ($icon_data != "") {
            $filehandler = new ElggFile();
            $filehandler->owner_guid = $user->getGUID();
            
						$filehandler->setFilename("profile/" . $user->username . "master.jpg");
						$filehandler->open(ÓwriteÓ);
						$filehandler->write($icon_data);
						$filename = $filehandler->getFilenameOnFilestore();

						$topbar = get_resized_image_from_existing_file($filename, 16, 16, true);
						$tiny = get_resized_image_from_existing_file($filename, 25, 25, true);
						$small = get_resized_image_from_existing_file($filename, 40, 40, true);
						$medium = get_resized_image_from_existing_file($filename, 100, 100, true);
						$large = get_resized_image_from_existing_file($filename, 200, 200);

						$filehandler->setFilename("profile/" . $user->username . "large.jpg");
						$filehandler->open("write");
						$filehandler->write($large);
						$filehandler->close();
						$filehandler->setFilename("profile/" . $user->username . "medium.jpg");
						$filehandler->open("write");
						$filehandler->write($medium);
						$filehandler->close();
						$filehandler->setFilename("profile/" . $user->username . "small.jpg");
						$filehandler->open("write");
						$filehandler->write($small);
						$filehandler->close();
						$filehandler->setFilename("profile/" . $user->username . "tiny.jpg");
						$filehandler->open("write");
						$filehandler->write($tiny);
						$filehandler->close();
						$filehandler->setFilename("profile/" . $user->username . "topbar.jpg");
						$filehandler->open("write");
						$filehandler->write($topbar);
						$filehandler->close();
            
            $user->icontime = time();
          } // if ($icon_data != "")
        } // if ($user_is_valid_for_create)
        else {
          oddlog("SKIP ".$user->username." due to bum data");
        }
      } // foreach ($entity_elements as $entity_element)
    } // if (!is_null($entity_elements))
	
    oddlog("FINISHED");
    
  } // public function import($odddoc)
}
?>