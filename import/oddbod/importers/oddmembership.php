<?php
/**
 * This class is the importer for community and group memberships
 * 
 * @author alistair
 *
 */
class ODDMembership {
	public function __construct() {
	}
	
	public function import($odddoc) {
		$xpath = new DOMXpath($odddoc);
    $relationship_elements = $xpath->query("//relationship");
    foreach ($relationship_elements as $relationship_element) {
    	$user = get_user_by_username(get_username_from_entity_uuid($relationship_element->getAttribute("uuid_one")));
    	$group = find_group_by_name(get_username_from_entity_uuid($relationship_element->getAttribute("uuid_two")));
    	
    	if (($user) && ($group)) {
    		if ($group->name == get_username_from_entity_uuid($relationship_element->getAttribute("uuid_two"))) {
    			// Add the user to the community or group
    			$group->join($user);
    			$group->save();
    			oddlog($group->name." -> ".$user->username);
    		}
    	}
    }
	}
}
?>