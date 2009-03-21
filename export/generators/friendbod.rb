require "generators/genericbod"

class FriendBod < GenericBod
  def initialize(friends)
    super(TYPE_FRIEND)
    @friends = friends
  end
  
  def odd
    @friends.each do |friends|
      friends.each do |friend|
#        puts "friend : #{friend.owner_username} -> #{friend.username}"
        @root_node.add_element("relationship",
                               { "uuid" => "",
                                 "uuid_one" => UUID_PERSON + friend.owner_username,
                                 "type" => "hasfriend",
                                 "uuid_two" => UUID_PERSON + friend.username })
      end
    end
  end
  
  def how_many
    @friends.length
  end
end