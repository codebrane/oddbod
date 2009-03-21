class Blog
  attr_reader :owner, :posts
  
  def initialize(owner)
    @owner = owner
    @posts = Array.new
  end
  
  def add_post(post)
    @posts[@posts.length] = post
  end
end