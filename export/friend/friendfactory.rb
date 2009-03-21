require "db"
require "friend/friend"

class FriendFactory
  FRIEND_IDENT = 2
  STATUS = 3
  USERNAME = 1
  
  def initialize(db)
    @db = db
  end
  
  def load_friends(owner_ident, owner_username)
    friends = Array.new
    results = @db.query("select * from " + @db.table_prefix + "friends where owner = '" + owner_ident + "'")
    results.each do |result|
      user_result = @db.query("select * from " + @db.table_prefix + "users where ident = '" + result[FRIEND_IDENT] + "'")
      user = @db.get_first_result(user_result)
      if (user != nil)
        puts "Friend: #{owner_username} -> #{user[USERNAME]}"
        friends[friends.length] = Friend.new(owner_username, user[USERNAME], result[STATUS])
      end
    end
    friends
  end
end