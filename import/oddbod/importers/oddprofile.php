<?php
/**
 * This class is the importer for user and community profiles. 
 * 
 * 0.9 -> 1.5 user metadata mappings:
 * 
 * biography -> description (about me)
 * minibio -> briefdescription
 * town -> location
 * interests -> interests
 * skills -> skills
 * emailaddress -> contactemail
 * workphone -> phone
 * mobphone -> mobile
 * workweb -> website
 * 
 * 0.9 -> 1.5 community metadata mappings:
 * 
 * biography -> description
 * minibio -> briefdescription
 * interests -> interests
 * ??? -> website
 * 
 * @author alistair
 *
 */
class ODDProfile {
  const MODE_USERS = "MODE_USERS";
  const MODE_COMMUNITIES = "MODE_COMMUNITIES";
  
  /** 0.9 to 1.5 metadata mappings */
  private $user_metadata_mappings;
  /** 0.9 to 1.5 community mappings */
  private $comm_metadata_mappings;
  private $mode;
  
  public function __construct($mode) {
    $this->mode = $mode;
  }

  public function import($odddoc) {
    global $CONFIG;
    $this->user_metadata_mappings = array("biography"    => "description",
                                          "minibio"      => "briefdescription",
                                          "town"         => "location",
                                          "interests"    => "interests",
                                          "skills"       => "skills",
                                          "emailaddress" => "contactemail",
                                          "workphone"    => "phone",
                                          "mobphone"     => "mobile",
                                          "workweb"      => "website");
    
    $this->comm_metadata_mappings = array("biography"    => "description",
                                          "minibio"      => "briefdescription",
                                          "interests"    => "interests");
    
    $odd_entities = array();
    
    $xpath = new DOMXpath($odddoc);
	$metadata_elements = $xpath->query("//metadata");
	if (!is_null($metadata_elements)) {
	  // Build up the set of users to which the metadata refers
	  foreach ($metadata_elements as $metadata_element) {
	    $username = get_username_from_entity_uuid($metadata_element->getAttribute("entity_uuid"));
	    if (!array_key_exists($username, $odd_entities)) {
	      $odd_entities[$username] = $metadata_element->getAttribute("entity_uuid");
	    }
	  }
	  
	  // Update each entity's metadata in ELGG
  	  foreach ($odd_entities as $username => $entity_uuid) {
  	    $community_name = "";
  	    
	    $metadata_elements = $xpath->query("//metadata[@entity_uuid='$entity_uuid']");
	    foreach ($metadata_elements as $metadata_element) {
	      $metadata_name = clean($metadata_element->getAttribute("name"));
	      
	      if ($metadata_name == "community-name") {
	        $community_name = clean($metadata_element->nodeValue);
	      }
	      
          if (($metadata_name == "interests") || ($metadata_name == "skills")) {
            $data = string_to_tag_array(clean($metadata_element->nodeValue));
          }
          else {
            $data = clean($metadata_element->nodeValue);
          }
	      
	      if ($this->mode == MODE_USERS) {
  	        // Don't create users, only update them
  	        $elgg_user = get_user_by_username($username);
  	        if (!$elgg_user) {
  	          oddlog("SKIPPING: ".$username);
  	          continue;
  	        }
  	      
  	        // Can we map the 0.9 metadata to 1.5?
  	        if (array_key_exists($metadata_name, $this->user_metadata_mappings)) {
  	          oddlog("METADATA UPDATE: ".$elgg_user->name." : ".$this->user_metadata_mappings[$metadata_name]);
  	          
              if (is_array($data)) {
                $i = 0;
                foreach($data as $interval) {
                  $i++;
                  if ($i == 1) { $multiple = false; } else { $multiple = true; }
                  create_metadata($elgg_user->guid, $metadata_name,
                                  $interval, 'text',
                                  $elgg_user->guid, ACCESS_PRIVATE, $multiple);
                }
              } 
  	          else {
  	            remove_metadata($elgg_user->guid, $this->user_metadata_mappings[$metadata_name]);
      	        create_metadata($elgg_user->guid, $this->user_metadata_mappings[$metadata_name],
      	                        $data, "text",
                                $elgg_user->guid, ACCESS_PRIVATE);
  	          }
              $elgg_user->save();
  	        }
	      } // if ($this->mode == $this->MODE_USERS)
	      
	      if ($this->mode == MODE_COMMUNITIES) {
	        oddlog("---------------------------------- $community_name");
	        $comm = find_group_by_name($community_name);
	        if (!$comm) {
  	          oddlog("SKIPPING: ".$username);
  	          continue;
  	        }
  	        
  	        // Can we map the 0.9 metadata to 1.5?
  	        if (array_key_exists($metadata_name, $this->comm_metadata_mappings)) {
  	          oddlog("METADATA UPDATE: ".$comm->name." : ".$this->comm_metadata_mappings[$metadata_name]);
  	          $metadata_field = $this->comm_metadata_mappings[$metadata_name];
  	          $comm->$metadata_field = $data;
  	          $comm->save();
  	        }
	      } // if ($this->mode == $this->MODE_COMMUNITIES)
	    } // foreach ($metadata_elements as $metadata_element)
	  } // foreach ($odd_users as $username => $entity_uuid)
	} // if (!is_null($metadata_elements))
  } // public function import($odddoc)
}
?>