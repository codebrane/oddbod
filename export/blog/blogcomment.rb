class BlogComment
  attr_reader :username, :body, :posted
  
  def initialize(username, body, posted)
    @username = username
    @body = body
    @posted = posted
  end
end