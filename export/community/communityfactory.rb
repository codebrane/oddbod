require 'db'
require 'community/community'

class CommunityFactory
  # community
  IDENT = 0
  USERNAME = 1
  NAME = 4
  ICON = 5
  ACTIVE = 6
  ALIAS = 7
  FILE_QUOTA = 10
  OWNER = 12
  MODERATION = 14
  
  # icon
  ICON_FILENAME = 2
  ICON_DESCRIPTION = 3
  
  def initialize(db)
    @db = db
    @comms = Array.new
  end
  
  def load_communities
    comms = @db.query("select * from " + @db.table_prefix + "users where user_type = '" + DB::COMMUNITY_TYPE + "'")
    comms.each do |comm|
      results = @db.query("select * from " + @db.table_prefix + "users where ident = '" + comm[OWNER] + "'")
      owner = @db.get_first_result(results)
      
      icon_filename = ""
      icon_description = ""
      if (comm[ICON] != "-1")
        icons = @db.query("select * from " + @db.table_prefix + "icons where ident = '" + comm[ICON] + "'")
        icon = @db.get_first_result(icons)
        if (icon != nil)
          icon_filename = icon[ICON_FILENAME]
          icon_description = icon[ICON_DESCRIPTION]
        end
      end
      
      @comms[@comms.length] = Community.new(comm[IDENT],
                                            comm[USERNAME],
                                            comm[NAME],
                                            icon_filename,
                                            icon_description,
                                            comm[ACTIVE],
                                            comm[ALIAS],
                                            comm[FILE_QUOTA],
                                            owner[1],
                                            comm[MODERATION])
    end
    @comms
  end
end