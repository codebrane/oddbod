require 'generators/genericbod'

class BlogBod < GenericBod
  BOD_TYPE_USER_BLOGS = "TYPE_USER_BLOGS"
  BOD_TYPE_COMMUNITY_BLOGS = "TYPE_COMMUNITY_BLOGS"
  
  def initialize(blogs, type)
    if (type == BOD_TYPE_USER_BLOGS)
      super(TYPE_USER_BLOGS)
    end
    if (type == BOD_TYPE_COMMUNITY_BLOGS)
      super(TYPE_COMMUNITY_BLOGS)
    end
    @blogs = blogs
  end
  
  def odd
    unique_uuid = ""
    
    @blogs.each do |blog|
      blog.posts.each do |post|
        unique_uuid = (Time.now.to_i + rand(100_000_000)).to_s(16)
        @root_node.add_element("entity", {"uuid" => UUID_BLOG + unique_uuid, "class" => BLOG_POST_CLASS})
        
        metadata = @root_node.add_element("metadata",
                                          { "uuid" => "", "entity_uuid" => UUID_BLOG + unique_uuid,
                                            "name" => "community-name" })
        metadata.text = post.community_name
        metadata = @root_node.add_element("metadata",
                                          { "uuid" => "", "entity_uuid" => UUID_BLOG + unique_uuid,
                                            "name" => "owner" })
        metadata.text = blog.owner
        metadata = @root_node.add_element("metadata",
                                          { "uuid" => "", "entity_uuid" => UUID_BLOG + unique_uuid,
                                            "name" => "post-owner" })
        metadata.text = post.owner
        metadata = @root_node.add_element("metadata",
                                          { "uuid" => "", "entity_uuid" => UUID_BLOG + unique_uuid,
                                            "name" => "title" })
        metadata.text = post.title
        metadata = @root_node.add_element("metadata",
                                          { "uuid" => "", "entity_uuid" => UUID_BLOG + unique_uuid,
                                            "name" => "body" })
        metadata.text = REXML::CData.new(post.body)
        metadata = @root_node.add_element("metadata",
                                          { "uuid" => "", "entity_uuid" => UUID_BLOG + unique_uuid,
                                            "name" => "time" })
        metadata.text = post.time
        metadata = @root_node.add_element("metadata",
                                          { "uuid" => "", "entity_uuid" => UUID_BLOG + unique_uuid,
                                            "name" => "access" })
        metadata.text = post.access
        metadata = @root_node.add_element("metadata",
                                          { "uuid" => "", "entity_uuid" => UUID_BLOG + unique_uuid,
                                            "name" => "tags" })
        tags = ""
        tag_count = 0
        post.tags.each do |tag|
          tags += tag.name
          if (tag_count < post.tags.length-1)
            tags += ","
          end
          tag_count += 1
        end
        metadata.text = tags
        
        post.comments.each do |comment|
          metadata = @root_node.add_element("metadata",
                                            { "uuid" => "", "entity_uuid" => UUID_BLOG + unique_uuid,
                                              "name" => "comment" })
          metadata.text = REXML::CData.new("#{comment.username}VVVVVVVVVVVVVV#{comment.body}VVVVVVVVVVVVVV#{comment.posted}")
        end
      end
    end
  end
  
  def how_many
    @blogs.length
  end
end