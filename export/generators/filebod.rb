require "generators/genericbod"
require 'fileutils'

# This bod works a bit differently to the others as the exported
# files can be huge, in the Gb range. This isn't so good for
# importing, so the bod splits the exported files into 10 file
# blocks, each one stored in an ODD file in the specified directory.

class FileBod < GenericBod
  def initialize(files)
    super(TYPE_FILES)
    @files = files
  end
  
  def odd(output_dir)
    FileUtils.makedirs(output_dir)
    bod_count = 1
    odd_file_count = 1
    
    @files.each do |file|
      if (bod_count > 20)
        # Dump the current batch of exported files to ODD...
        odd_file("#{output_dir}/09_files-#{odd_file_count}.xml")
        # ...and start a new ODD file
        init_new_bod_doc
        bod_count = 1
        odd_file_count += 1
      end
      
      @root_node.add_element("entity", {"uuid" => UUID_FILE + file.unique_id, "class" => FILE_CLASS})
      
      puts "file: #{file.original_name} -> #{file.owner}"
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
      
      bod_count += 1
    end
    
    # Dump the leftovers to ODD
    odd_file("#{output_dir}/09_files-#{odd_file_count}.xml")
  end
  
  def how_many
    @files.length
  end
end