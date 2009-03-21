require 'blog/blogtag'

class BlogPost
  attr_reader :owner, :title, :body, :time, :access, :tags, :community_name, :comments
  
  def initialize(owner, title, body, time, access, community_name)
    @owner= owner
    @title = title
    @body = body
    @time = time
    @access = access
    @tags = Array.new
    @community_name = community_name
    @comments = Array.new
  end
  
  def add_tag(tag)
    @tags[@tags.length] = tag
  end
  
  def add_comment(comment)
    @comments[@comments.length] = comment
  end
end