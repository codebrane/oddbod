require 'kconv'

class MembershipBod < GenericBod
  def initialize(memberships)
    super(TYPE_MEMBERSHIPS)
    @memberships = memberships
  end
  
  def odd
    @memberships.each do |membership|
      membership.members.each do |member|
        # Need the Kanji converter of all things in case we hit accented characters (Gaelic)
        group_name = Kconv.toutf8 membership.group.name
        @root_node.add_element("relationship",
                               { "uuid" => "",
                                 "uuid_one" => UUID_PERSON + member.username,
                                 "type" => RELATIONSHIP_MEMBER_CLASS,
                                 "uuid_two" => UUID_COMMUNITY +  group_name})
      end
    end
  end
  
  def how_many
    @memberships.length
  end
end