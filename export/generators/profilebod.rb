require 'generators/genericbod'

class ProfileBod < GenericBod
  def initialize(profiles, mode)
    if (mode == "user-profiles")
      super(TYPE_PROFILE_USERS)
    end
    if (mode == "community-profiles")
      super(TYPE_PROFILE_COMMUNITIES)
    end
    @profiles = profiles
  end
  
  def odd
    @profiles.each do |profile|
      profile.items.each do |profile_item|
        puts "profile : #{profile_item.username} -> #{profile_item.name}"
        uuid = UUID_PERSON
        if (profile_item.item_type == "community")
          uuid = UUID_COMMUNITY
        end
        metadata = @root_node.add_element("metadata",
                                          { "uuid" => "", "entity_uuid" => uuid + profile_item.username,
                                            "name" => profile_item.name })
        if ((profile_item.name == "profilephoto") && (profile_item.value != ""))
          metadata.text = REXML::CData.new(profile_item.value)
        else
          metadata.text = profile_item.value
        end
      end
    end
  end
  
  def how_many
    @profiles.length
  end
end