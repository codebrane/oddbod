require 'generators/genericbod'
require 'user/user'

class UserBod < GenericBod
  # array of User objects
  def initialize(users)
    super(TYPE_USER)
    @users = users
  end
  
  def odd
    # ODD recommends data be in processing order, so we need to add the users first...
    @users.each do |user|
      puts "entity : #{user.username}"
      @root_node.add_element("entity", {"uuid" => UUID_PERSON + user.username, "class" => PERSON_CLASS})
    end
    
    # ...then add their metadata, which depends on the users being parsed
    @users.each do |user|
      puts "metadata : #{user.username}"
      # Username, i.e. their login id
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_PERSON + user.username,
                                          "name" => "username" })
      metadata.text = user.username
      # Hashed password
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_PERSON + user.username,
                                          "name" => "password" })
      metadata.text = user.password
      # email
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_PERSON + user.username,
                                          "name" => "email" })
      metadata.text = user.email
      # name, i.e. their full name
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_PERSON + user.username,
                                          "name" => "name" })
      metadata.text = user.name
      # icon filename
      if (user.icon_filename != "")
        metadata = @root_node.add_element("metadata",
                                          { "uuid" => "", "entity_uuid" => UUID_PERSON + user.username,
                                            "name" => "icon_filename" })
        metadata.text = user.icon_filename
      end
      # icon data
      if (user.icon_data != "")
        metadata = @root_node.add_element("metadata",
                                          { "uuid" => "", "entity_uuid" => UUID_PERSON + user.username,
                                            "name" => "icon_data" })
        metadata.text = REXML::CData.new(user.icon_data)
      end
      # active
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_PERSON + user.username,
                                          "name" => "active" })
      metadata.text = user.active
      # alias
      if (user.user_alias != "")
        metadata = @root_node.add_element("metadata",
                                          { "uuid" => "", "entity_uuid" => UUID_PERSON + user.username,
                                            "name" => "alias" })
        metadata.text = user.user_alias
      end
      # admin
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_PERSON + user.username,
                                          "name" => "admin" })
      metadata.text = user.admin
    end
  end
  
  def how_many
    @users.length
  end
end
