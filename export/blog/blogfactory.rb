require 'blog/blog'
require 'blog/blogpost'
require 'blog/blogtag'
require 'blog/blogcomment'

class BlogFactory
  # db fields
  POST_IDENT = 0
  POST_OWNER = 1
  POST_ACCESS = 4
  POST_POSTED = 5
  POST_TITLE = 6
  POST_BODY = 7
  TAG_TAG = 1
  TAG_ACCESS = 4
  
  USER_USERNAME = 0
  
  COMMENT_OWNER = 2
  COMMENT_BODY = 4
  COMMENT_POSTED = 5
  
  attr_reader :blogs
  
  def initialize(db, entities)
    @db = db
    @entities = entities
    @blogs = Array.new
  end
  
  def load_blogs
    @entities.each do |entity|
      puts "processing #{entity.username}"
      @blogs[@blogs.length] = Blog.new(entity.username)
      blog_posts = @db.query("select * from " + @db.table_prefix + "weblog_posts where weblog = '" + entity.ident + "'")
      
      # Need the name of the entity for the import if it's a community
      comms_query = @db.query("select name from " + @db.table_prefix + "users where user_type = 'community' and ident = '" + entity.ident + "'")
      comm = @db.get_first_result(comms_query)
      if (comm != nil)
        community_name = comm[0]
      else
        community_name = "N/A"
      end
      
      blog_posts.each do |blog_post|
        puts "#{blog_post[POST_TITLE]}"
        owner_result =  @db.query("select username from " + @db.table_prefix + "users where ident = '" + blog_post[POST_OWNER] + "'")
        owner = @db.get_first_result(owner_result)
        
        # Sort out the access for the post
        access_mode = blog_post[POST_ACCESS]
        community_name_for_access = ""
        if (access_mode.match(/^community/))
          community_ident = access_mode.gsub(/community/, "")
          comm_result =  @db.query("select name from #{@db.table_prefix}users where ident = '#{community_ident}'")
          comm = @db.get_first_result(comm_result)
          if (comm != nil)
            access_mode = "community::#{comm[0]}"
          end
        end
        
        # Add the blog post...
        post = BlogPost.new(owner[USER_USERNAME], blog_post[POST_TITLE], blog_post[POST_BODY],
                            blog_post[POST_POSTED], access_mode, community_name)
        @blogs[@blogs.length-1].add_post(post)
        
        # ...and the tags for the post...
        post_tags = @db.query("select * from " + @db.table_prefix + "tags where ref = '" + blog_post[POST_IDENT] + "'")
        post_tags.each do |post_tag|
          post.add_tag(BlogTag.new(post_tag[TAG_TAG], post_tag[TAG_ACCESS]))
        end
        
        # ...and the comments for the post...
        post_comments = @db.query("select * from #{@db.table_prefix}weblog_comments where post_id = '#{blog_post[POST_IDENT]}'")
        if (post_comments != nil)
          post_comments.each do |post_comment|
            comment_owner_result =  @db.query("select username from #{@db.table_prefix}users where ident = '#{post_comment[COMMENT_OWNER]}'")
            comment = @db.get_first_result(comment_owner_result)
            if (comment != nil)
              comment_owner = comment[0]
              post.add_comment(BlogComment.new(comment_owner, post_comment[COMMENT_BODY], post_comment[COMMENT_POSTED]))
              puts "------- comment from #{comment_owner}"
            end
          end
        end
      end
    end
    blogs
  end
end
