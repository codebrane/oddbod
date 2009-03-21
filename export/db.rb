require "mysql"

class DB
  USER_TYPE = "person"
  COMMUNITY_TYPE = "community"
  
  attr_reader :db, :table_prefix
  
  def initialize(host, user, passwd, db, table_prefix)
    @db = Mysql.new(host, user, passwd, db)
    @table_prefix = table_prefix
  end
  
  def query(query)
    @db.query(query)
  end
  
  def get_first_result(result)
    result.fetch_row
  end
  
  def close
    @db.close
  end
end