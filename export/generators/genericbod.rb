require 'rexml/document'

class GenericBod
  UUID = "https://communities.uhi.ac.uk/oddbod/"
  UUID_PERSON = UUID + "person/"
  UUID_COMMUNITY = UUID + "community/"
  UUID_BLOG = UUID + "blog/"
  PERSON_CLASS = "person"
  COMMUNITY_CLASS = "community"
  BLOG_POST_CLASS = "blogpost"
  RELATIONSHIP_FRIEND_CLASS = "hasfriend"
  RELATIONSHIP_MEMBER_CLASS = "ismemberof"
  
  TYPE_USER = "user"
  TYPE_PROFILE_USERS = "profile-users"
  TYPE_PROFILE_COMMUNITIES = "profile-communities"
  TYPE_FRIEND = "friend"
  TYPE_COMMUNITIY = "community"
  TYPE_USER_BLOGS = "user-blogs"
  TYPE_COMMUNITY_BLOGS = "community-blogs"
  TYPE_MEMBERSHIPS = "memberships"
  
  def initialize(type)
    @odd_doc = REXML::Document.new()
    @root_node = @odd_doc.add_element("odd", { "version" => "1.0", "type" => type })
  end
  
  def odd_file(filename)
    puts "writing file : #{filename}"
    File.open(filename, "w") { |file|
      @odd_doc.write(file, 2)
    }
  end
end