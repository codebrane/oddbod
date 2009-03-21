class Profile
  attr_reader :items
  
  def initialize
    @items = Array.new
  end
  
  def add_item(profile_item)
    @items[@items.length] = profile_item
  end
end