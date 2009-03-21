<?
/**
 * Logger function
 * 
 * @param $m The message to log
 */
function oddlog($m) {
	$fd = fopen("/tmp/oddbod-log", "a+");
	fwrite($fd, $m."\n");
	fclose($fd);
}

/**
 * Extracts the username from an ODD entity_uuid. This can then be used
 * in a call to Elgg core function get_user_by_username().
 * 
 * @param $entity_uuid e.g. https://elgg.domain.com/oddbod/person/0234891
 * @return the last bit, i.e. '0234891' as a string.
 */
function get_username_from_entity_uuid($entity_uuid) {
	$parts = explode("/", $entity_uuid);
	$userid_index = count($parts) - 1;
	return $parts[$userid_index];
}

/**
 * Tidies up the contents of a text node from an ODD doc.
 * 
 * @param $data The text to be cleaned
 * @return The cleaned text as a string
 */
function clean($data) {
  return str_replace("\n", "", rtrim(ltrim($data)));
}

/**
 * Retrieves an ElggGroup based on the name.
 * 
 * @param $group_name The name of the group to lookup, e.g. "Interesting Community"
 * @return ElggGroup object or NULL if the group can't be found
 */
function find_group_by_name($group_name) {
  global $CONFIG;
  try {
    $db_link = get_db_link("read");
    $sanitised_group_name = mysql_real_escape_string($group_name);
    $query = "select guid from {$CONFIG->dbprefix}groups_entity where name = '{$sanitised_group_name}'";
    $result = execute_query($query, $db_link);
    $row = mysql_fetch_assoc($result);
    if ($row) {
      return new ElggGroup($row['guid']);
    }
    else {
      return false;
    }
  }
  catch(DatabaseException $de) {
    oddlog("find_group_by_name error: ".$de->getMessage());
    return false;
  }
}

/**
 * Blocks outgoing emails. This is useful when the Friend network is imported as otherwise
 * users would be hit with multiple emails when their networks are built up. You might not
 * want this if you're testing a migration.
 * If you want emails to be sent, disable the registration of this function in start.php,
 * then disable/enable the plugin to reload the config.
 * 
 * @param $from
 * @param $to
 * @param $subject
 * @param $message
 * @param $params
 * @return unknown_type
 */
function oddbod_notify_handler(ElggEntity $from, ElggUser $to, $subject, $message, array $params = NULL) {
  oddlog("blocking email from {$from->name} to $to->name");
  return;
}
?>
