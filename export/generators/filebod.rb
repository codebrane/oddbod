require "generators/genericbod"

class FileBod < GenericBod
  def initialize(files)
    super(TYPE_FILES)
    @files = files
  end
  
  def odd
    @files.each do |file|
      puts "file: #{file.original_name}"
      @root_node.add_element("entity", {"uuid" => UUID_FILE + file.unique_id, "class" => FILE_CLASS})
    end
    
    @files.each do |file|
      puts "file metadata: #{file.original_name} -> #{file.owner}"
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_FILE + file.unique_id,
                                          "name" => "owner" })
      metadata.text = file.owner
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_FILE + file.unique_id,
                                          "name" => "community" })
      metadata.text = file.community
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_FILE + file.unique_id,
                                          "name" => "title" })
      metadata.text = file.title
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_FILE + file.unique_id,
                                          "name" => "original_name" })
      metadata.text = file.original_name
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_FILE + file.unique_id,
                                          "name" => "description" })
      metadata.text = file.description
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_FILE + file.unique_id,
                                          "name" => "access" })
      metadata.text = file.access
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_FILE + file.unique_id,
                                          "name" => "time_uploaded" })
      metadata.text = file.time_uploaded
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_FILE + file.unique_id,
                                          "name" => "mime_type" })
      metadata.text = file.mime_type
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_FILE + file.unique_id,
                                          "name" => "content" })
      metadata.text = REXML::CData.new(file.content)
    end
  end
  
  def how_many
    @files.length
  end
end