class BlogTag
  attr_reader :name, :access
  
  def initialize(name, access)
    @name = name
    @access = access
  end
end