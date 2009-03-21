require 'generators/genericbod'
require 'icon/icon'

class IconBod < GenericBod
  def initialize(icons)
    super()
    @icons = icons
  end
  
  def odd
    @icons.each do |icon|
      puts "icon : #{icon.username}"
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_PERSON + icon.username,
                                          "Name" => "filename" })
      metadata.text = icon.filename
      metadata = @root_node.add_element("metadata",
                                        { "uuid" => "", "entity_uuid" => UUID_PERSON + icon.username,
                                          "Name" => "data" })
      metadata.text = REXML::CData.new(icon.data)
    end
  end
end