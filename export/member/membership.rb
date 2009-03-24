class Membership
  attr_reader :group, :members
  
  def initialize(group)
    @group = group
    @members = Array.new
  end
  
  def add_member(member)
    @members[@members.length] = member
  end
end