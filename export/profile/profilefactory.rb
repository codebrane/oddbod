require 'config'
require "db"
require "profile/profile"
require "profile/profileitem"

class ProfileFactory
  OWNER = 1
  ACCESS = 2
  NAME = 3
  VALUE = 4
  
  def initialize(db, profile_type)
    @db = db
    @profile_type = profile_type
  end
  
  def load_profile(user_ident, username)
    profile = Profile.new
    results = @db.query("select * from " + @db.table_prefix + "profile_data where owner = '" + user_ident + "'")
    
    # Need the name of the entity for the import if it's a community
    if (@profile_type == "community")
      comms_query = @db.query("select name from " + @db.table_prefix + "users where user_type = 'community' and ident = '" + user_ident + "'")
      comm = @db.get_first_result(comms_query)
      profile.add_item(ProfileItem.new(@profile_type, username, "N/A", "community-name", comm[0]))
    end
    
    results.each do |result|
      if (result[NAME] == "profilephoto")
        if (File.exist?(Config::ELGG_DATA_DIR + result[VALUE]))
          # Add an extra field for the profile photo filename...
          #profile.add_item(ProfileItem.new(@profile_type, username, result[ACCESS],
          #                                 "profilephoto_filename", result[VALUE]))
          # ...and the ProfileItem will read the photo data into this field
          #profile.add_item(ProfileItem.new(@profile_type, username, result[ACCESS],
          #                                result[NAME], Config::ELGG_DATA_DIR + result[VALUE]))
        end
      else
        # Normal item, no paths involved
        profile.add_item(ProfileItem.new(@profile_type, username, result[ACCESS], result[NAME], result[VALUE]))
      end
    end
    profile
  end
end