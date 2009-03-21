require 'generators/genericbod'

class CommunityBod < GenericBod
  def initialize(comms)
    super(TYPE_COMMUNITIY)
    @comms = comms
  end
  
  def odd
    # ODD recommends data be in processing order, so we need to add the communities first...
    @comms.each do |comm|
      puts "entity : #{comm.username}"
      @root_node.add_element("entity", {"uuid" => UUID_COMMUNITY + comm.username, "class" => COMMUNITY_CLASS})
    end
    
    # ...then add the metadata, which depends on the communities being parsed
    @comms.each do |comm|
      puts "metadata : #{comm.username}"
      # Username, i.e. the id of the community
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_COMMUNITY + comm.username,
                                          "name" => "username" })
      metadata.text = comm.username
      # name
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_COMMUNITY + comm.username,
                                          "name" => "name" })
      metadata.text = comm.name
      # icon filename
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_COMMUNITY + comm.username,
                                          "name" => "icon_filename" })
      metadata.text = comm.icon_filename
      # icon data
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_COMMUNITY + comm.username,
                                          "name" => "icon_data" })
      metadata.text = REXML::CData.new(comm.icon_data)
      # active
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_COMMUNITY + comm.username,
                                          "name" => "active" })
      metadata.text = comm.active
      # alias
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_COMMUNITY + comm.username,
                                          "name" => "alias" })
      metadata.text = comm.comm_alias
      # file_quota
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_COMMUNITY + comm.username,
                                          "name" => "file_quota" })
      metadata.text = comm.file_quota
      # owner
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_COMMUNITY + comm.username,
                                          "name" => "owner" })
      metadata.text = comm.owner
      # moderation
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_COMMUNITY + comm.username,
                                          "name" => "moderation" })
      metadata.text = comm.moderation
    end
  end
  
  def how_many
    @comms.length
  end
end