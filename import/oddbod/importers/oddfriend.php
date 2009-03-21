<?php
/**
 * This class is the importer for the entire friend network.
 * 
 * @author alistair
 *
 */
class ODDFriend {
  public function import($odddoc) {
    $xpath = new DOMXpath($odddoc);
	$relationship_elements = $xpath->query("//relationship");
	
	if (!is_null($relationship_elements)) {
      $odd_users = array();
      
   	  // Build up the set of users who have friends
	  foreach ($relationship_elements as $relationship_element) {
	    $username = get_username_from_entity_uuid($relationship_element->getAttribute("uuid_one"));
	    if (!array_key_exists($username, $odd_users)) {
	      $odd_users[$username] = $relationship_element->getAttribute("uuid_one");
	    }
	  } // foreach ($relationship_elements as $relationship_element)
	  
	  // Update each user's friends network
	  foreach ($odd_users as $username => $entity_uuid) {
	    $relationship_elements = $xpath->query("//relationship[@uuid_one='$entity_uuid']");
	    foreach ($relationship_elements as $relationship_element) {
	      $friend_username = get_username_from_entity_uuid($relationship_element->getAttribute("uuid_two"));
	      
	      $elgg_befriender = get_user_by_username($username);
	      $elgg_befriendee = get_user_by_username($friend_username);
	      
	      // Don't network spacemen!
	      if (((!$elgg_befriender) || (!$elgg_befriendee)) ||
	          ($elgg_befriender == $elgg_befriendee)) {
	        oddlog("SKIPPING: ".$elgg_befriender." ->".$elgg_befriendee);
	        continue;
	      }
	      
	      // Make 'em friends!
	      oddlog("FRIEND: ".$elgg_befriender->name." -> ".$elgg_befriendee->name);
	      add_entity_relationship($elgg_befriender->getGUID(), "friend", $elgg_befriendee->getGUID());
	    }
	  } // foreach ($odd_users as $username => $entity_uuid)
	} // if (!is_null($relationship_elements))
  } // public function import($odddoc)
}
?>