require 'db'
require 'icon/icon'

class IconFactory
  FILENAME = 2
  DESCRIPTION = 3
  
  def initialize(db)
    @db = db
  end
  
  def load_icon(user_ident, username)
    puts "loading icon : #{username}"
    results = @db.query("select * from " + @db.table_prefix + "icons where owner = '" + user_ident + "'")
    result = @db.get_first_result(results)
    if (result != nil)
      Icon.new(username, result[FILENAME], result[DESCRIPTION], "sdfdsfdsfdsfsdfsfsdfsdfdsfsdfsdfsdfsdsd")
    end
  end
end