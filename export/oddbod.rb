#
# oddbod
#
# Exports various aspects of an Elgg 0.9.x database as modified
# openData Definition (ODD) files.
#
# @author Alistair Young alistair@codebrane.com
#

require 'rexml/document'
require 'user/userfactory'
require 'profile/profilefactory'
require 'friend/friendfactory'
require 'icon/iconfactory'
require 'community/communityfactory'
require 'blog/blogfactory'
require 'member/memberfactory'
require 'file/filefactory'
require 'generators/userbod'
require 'generators/profilebod'
require 'generators/friendbod'
require 'generators/iconbod'
require 'generators/communitybod'
require 'generators/blogbod'
require 'generators/membershipbod'
require 'generators/filebod'

if (ARGV.length != 2)
  puts "Usage:"
  puts "ruby oddbod.rb EXPORT_MODE OUTPUT_FILE"
  puts "EXPORT_MODE = users|communities|friends|user-profiles|community-profiles"
  puts "e.g."
  puts "export all users: ruby oddbod.rb users oddfiles/09_users.xml"
  puts "export all user profiles: ruby oddbod.rb user-profiles oddfiles/09_user_profiles.xml"
  exit
end

mode = ARGV[0]
output_file = ARGV[1]

# Connect to the database
puts "connecting..."
db = DB.new(Config::DB_HOST, Config::DB_USER, Config::DB_PASS, Config::DB_NAME, Config::DB_PREFIX)

# Load up all the users from the database
puts "loading users..."
users = UserFactory.new(db)
elgg_users = users.load_users

# Load up all the communities from the database
puts "loading communities..."
comms_factory = CommunityFactory.new(db)
elgg_comms = comms_factory.load_communities

if (mode == "users")
  puts "processing users..."
  odd_users = UserBod.new(elgg_users)
  odd_users.odd
  odd_users.odd_file(output_file)
  puts "processed " + odd_users.how_many.to_s + " users"
end

if (mode == "communities")
  puts "loading communities..."
  odd_comms = CommunityBod.new(elgg_comms)
  odd_comms.odd
  odd_comms.odd_file(output_file)
  puts "processed " + odd_comms.how_many.to_s + " users"
end

if (mode == "user-profiles") || (mode == "community-profiles")
  profiles = Array.new
  if (mode == "user-profiles")
    profile_factory = ProfileFactory.new(db, "person")
    elgg_users.each do |elgg_user|
      puts "loading : #{elgg_user.username}"
      profiles[profiles.length] = profile_factory.load_profile(elgg_user.ident, elgg_user.username)
    end
  end
  if (mode == "community-profiles")
    profile_factory = ProfileFactory.new(db, "community")
    elgg_comms.each do |elgg_comm|
      puts "loading : #{elgg_comm.name}"
      profiles[profiles.length] = profile_factory.load_profile(elgg_comm.ident, elgg_comm.username)
    end
  end
  odd_profiles = ProfileBod.new(profiles, mode)
  odd_profiles.odd
  odd_profiles.odd_file(output_file)
  puts "processed " + odd_profiles.how_many.to_s + " " + mode
end

if (mode == "friends")
  puts "loading friends..."
  friend_factory = FriendFactory.new(db)
  friends = Array.new
  elgg_users.each do |elgg_user|
    friends[friends.length] = friend_factory.load_friends(elgg_user.ident, elgg_user.username)
  end
  friend_bod = FriendBod.new(friends)
  friend_bod.odd
  friend_bod.odd_file(output_file)
  puts "processed " + friend_bod.how_many.to_s + " friends"
end

if ((mode == "user-blogs") || (mode == "community-blogs"))
  if (mode == "user-blogs")
    type = BlogBod::BOD_TYPE_USER_BLOGS
    blog_factory = BlogFactory.new(db, elgg_users)
  else
    type = BlogBod::BOD_TYPE_COMMUNITY_BLOGS
    blog_factory = BlogFactory.new(db, elgg_comms)
  end
  puts "loading blogs..."
  blog_bod = BlogBod.new(blog_factory.load_blogs, type)
  blog_bod.odd
  blog_bod.odd_file(output_file)
  puts "processed " + blog_bod.how_many.to_s + " blogs"
end

if (mode == "communities-members")
  member_factory = MemberFactory.new(db, elgg_users, elgg_comms)
  bod = MembershipBod.new(member_factory.load_members)
  bod.odd
  bod.odd_file(output_file)
  puts "processed " + bod.how_many.to_s + " memberships"
end

if (mode == "files")
  file_factory = FileFactory.new(db)
  file_bod = FileBod.new(file_factory.load_files)
  file_bod.odd
  file_bod.odd_file(output_file)
  puts "processed #{file_bod.how_many.to_s} files"
end

if (mode == "icons")
  icon_factory = IconFactory.new(db)
  icons = Array.new
  elgg_users.each do |elgg_user|
    icon = icon_factory.load_icon(elgg_user.ident, elgg_user.username)
    if (icon != nil)
      icons[icons.length] = icon
    end
  end
  odd_icon = IconBod.new(icons)
  odd_icon.odd
  odd_icon.odd_file(output_file)
end

db.close
