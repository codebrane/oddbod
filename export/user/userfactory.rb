require 'config'
require "db"
require 'user/user'

class UserFactory
  attr_reader :users
  
  # user
  IDENT = 0
  USERNAME = 1
  PASSWORD = 2
  EMAIL = 3
  NAME = 4
  ICON = 5
  ACTIVE = 6
  ALIAS = 7
  FILE_QUOTA = 10
  USER_TYPE = 13
  
  # icon
  ICON_FILENAME = 2
  ICON_DESCRIPTION = 3
  
  # user flags
  FLAG_NAME = 2
  FLAG_VALUE = 3
  
  def initialize(db)
    @db = db
    @users = Array.new
  end
  
  def load_users
    users = @db.query("select * from " + @db.table_prefix + "users where user_type = '" + DB::USER_TYPE + "'")
    users.each do |user|
      icon_description = ""
      icon_filename = ""
      if (user[ICON] != "-1")
        icons = @db.query("select * from " + @db.table_prefix + "icons where ident = '" + user[ICON] + "'")
        icon = @db.get_first_result(icons)
        if (icon != nil)
          icon_filename = icon[ICON_FILENAME]
          icon_description = icon[ICON_DESCRIPTION]
        end
      end
      
      flags = @db.query("select * from " + @db.table_prefix + "user_flags where user_id = '" + user[IDENT] + "'")
      admin = "no"
      flags.each do |flag|
        if ((flag[FLAG_NAME] == "admin") && (flag[FLAG_VALUE] == "1"))
          admin = "yes"
        end
      end
      
      @users[@users.length] = User.new(user[IDENT],
                                       user[USERNAME],
                                       user[PASSWORD],
                                       user[EMAIL],
                                       user[NAME],
                                       icon_filename,
                                       icon_description,
                                       user[ACTIVE],
                                       user[ALIAS],
                                       admin)
    end
    @users
  end
end