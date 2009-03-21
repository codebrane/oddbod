<?php
/**
 * This class is the importer for user and community blogs and the comments
 * that go with each post. The original date and time of each post and
 * comment is preserved.
 * 
 * @author alistair
 *
 */
class ODDBlog {
  const MODE_USER_BLOGS = "MODE_USER_BLOGS";
  const MODE_COMMUNITY_BLOGS = "MODE_COMMUNITY_BLOGS";
  
  /** Users or communities */
  private $mode;
  /** Mappings for access */
  private $blog_access_mode;
  
  public function __construct($mode) {
    $this->mode = $mode;
    $blog_access_mode = array("PUBLIC"    => ACCESS_PUBLIC,
                              "LOGGED_IN" => ACCESS_LOGGED_IN,
                              "PRIVATE"   => ACCESS_PRIVATE);
  }
  
  public function import($odddoc) {
    global $CONFIG;
    
    $xpath = new DOMXpath($odddoc);
	$entity_elements = $xpath->query("//entity");
	if (!is_null($entity_elements)) {
	  foreach ($entity_elements as $entity_element) {
	    $valid = true;
	    $title = "";
	    $body = "";
	    $tags = "";
	    $time = "";
	    $username = "";
	    $access = ACCESS_PRIVATE;
	    $community_name = "";
	    $comments_count = 0;
	    $comments = array();
	    
	    $metadata_for_entity_elements = $xpath->query("//metadata[@entity_uuid='".$entity_element->getAttribute("uuid")."']");
	    foreach ($metadata_for_entity_elements as $metadata_for_entity_element) {
	      if ($metadata_for_entity_element->getAttribute("name") == "community-name") {
	        $community_name = clean($metadata_for_entity_element->nodeValue);
	      }
          if ($metadata_for_entity_element->getAttribute("name") == "post-owner") {
            $username = clean($metadata_for_entity_element->nodeValue);
            if (!get_user_by_username($username)) {
              // Don't post to a non existent user's blog
              $valid = false;
            }
          }
          if ($metadata_for_entity_element->getAttribute("name") == "title") {
            $title = clean($metadata_for_entity_element->nodeValue);
            if ($title == "") {
            	$title = "UNTITLED";
            }
          }
          if ($metadata_for_entity_element->getAttribute("name") == "body") {
            $body = clean($metadata_for_entity_element->nodeValue);
          }
          if ($metadata_for_entity_element->getAttribute("name") == "tags") {
            $tags = clean($metadata_for_entity_element->nodeValue);
          }
          if ($metadata_for_entity_element->getAttribute("name") == "time") {
            $time = clean($metadata_for_entity_element->nodeValue);
          }
          if ($metadata_for_entity_element->getAttribute("name") == "access") {
            $access = clean($metadata_for_entity_element->nodeValue);
          }
          if ($metadata_for_entity_element->getAttribute("name") == "comment") {
            $comments[$comments_count] = clean($metadata_for_entity_element->nodeValue);
            $comments_count++;
          }
	    } // foreach ($metadata_for_entity_elements as $metadata_for_entity_element)
	    
	    // Don't import bum blog posts
	    if ($valid) {
  	      if ($body != "") {
            $blog = new ElggObject();
            if ($this->mode == MODE_COMMUNITY_BLOGS) {
              $blog->subtype = "groupforumtopic";
            }
            else {
              $blog->subtype = "blog";
            }
            $owner = get_user_by_username($username);
            $blog->owner_guid = $owner->getGUID();
            
            /* If we're processing community blogs then we need to set the
             * container to be the group but the owner to be the user.
             */
            if ($this->mode == MODE_COMMUNITY_BLOGS) {
              $container = find_group_by_name($community_name);
            }
            else {
              $container = $owner;
            }
            $blog->container_guid = $container->getGUID();
            
            if (array_key_exists($access, $this->blog_access_mode)) {
            	$blog->access_id = $this->blog_access_mode[$access];
            }
            /*
            // Is the access community based? community::Community Name
            else if (strstr($access, "::")) {
            	$cname = str_replace("community::", "", $access);
            	$owner_community = find_group_by_name($cname);
            	if (!$owner_community) {
            		// If the community doesn't exist, make the post private
            		$blog->access_id = $this->blog_access_mode["PRIVATE"];
            	}
            	else {
            	}
            }
            */
            else {
            	$blog->access_id = $this->blog_access_mode["PRIVATE"];
            }
            
            $blog->title = $title;
            $blog->save();
            if ($this->mode == MODE_COMMUNITY_BLOGS) {
              $blog->annotate('group_topic_post', $body, ACCESS_PUBLIC, $owner->getGUID());
            }
            else {
              $blog->description = $body;
            }
            if ($tags != "") {
              $blog->tags = string_to_tag_array($tags);
            }
            $blog->comments_on = "On";
            $blog->save();

            // Have to alter the db directly to change the posted time
            $db_link = get_db_link("write");
            if ($this->mode == MODE_COMMUNITY_BLOGS) {
            	// This changes the date/time on the display in the community
              $query  = "update {$CONFIG->dbprefix}annotations ";
              $query .= "set time_created = '{$time}' ";
              $query .= "where entity_guid = '{$blog->getGUID()}'";
              execute_query($query, $db_link);
            }

            // This changes the date/time on the main blog
						$query  = "update {$CONFIG->dbprefix}entities ";
						$query .= "set time_created = '{$time}', time_updated = '{$time}' ";
						$query .= "where guid = '{$blog->getGUID()}'";
            execute_query($query, $db_link);
                        
            // Add any comments
            if ($comments_count > 0) {
            	foreach ($comments as $comment) {
            		// usernameVVVVVVVVVVVVVVcommentVVVVVVVVVVVVVVtime
            		$parts = split("VVVVVVVVVVVVVV", $comment);
            		// Mark the text so we can find it in the db to alter the time
            		$comment_text_marked = "VVVVVVVVVVVVVV".$parts[1];
            		$comment_owner = get_user_by_username($parts[0]);
            		// Ignore bum users
            		if (!$comment_owner) {
            			continue;
            		}
            		oddlog("comment from ".$comment_owner->getGUID(). " -> ".$parts[1]);
            		$blog->annotate('generic_comment', $comment_text_marked, $blog->access_id, $comment_owner->getGUID());
            		
            		// Find the comment in the database to update its time
            		$db_link = get_db_link("read");
            		$query = "select id,value_id from {$CONFIG->dbprefix}annotations where entity_guid = '{$blog->getGUID()}'";
            		$result = execute_query($query, $db_link);
            		while ($row = mysql_fetch_assoc($result)) {
            			$query = "select string from {$CONFIG->dbprefix}metastrings where id = '{$row['value_id']}'";
            			$string_result = execute_query($query, $db_link);
            			$string_row = mysql_fetch_assoc($string_result);
            			if ($string_row['string'] == $comment_text_marked) {
            				// We've found the newly added comment, remove the marker from its text...
            				$db_link = get_db_link("write");
            				$comment_text_unmarked = mysql_real_escape_string(str_replace("VVVVVVVVVVVVVV", "", $comment_text_marked));
            				$query = "update {$CONFIG->dbprefix}metastrings set string = '$comment_text_unmarked' where id = '{$row['value_id']}'";
            				execute_query($query, $db_link);
            				
            				// ...and update its time
            				$query = "update {$CONFIG->dbprefix}annotations set time_created = '$parts[2]' where id = '{$row['id']}'";
            				execute_query($query, $db_link);
            			}
            		}
            	}
            }
            
            oddlog("CREATE POST: ".$owner->name." ".$container->username." -> ".$container->name);
  	      } // if (($title != "") && ($body != ""))
	    } // if ($valid)
	  } // foreach ($entity_elements as $entity_element)
	} // if (!is_null($entity_elements))
  } // public function import($odddoc)
}
?>