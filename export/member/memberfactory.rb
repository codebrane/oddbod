require 'member/membership'
require 'member/member'

class MemberFactory
  # friends table
  OWNER = 0
  # users table
  USERNAME = 0
  
  def initialize(db, users, comms)
    @db = db
    @users = users
    @comms = comms
    @memberships = Array.new
  end
  
  def load_members
    @comms.each do |comm|
      @memberships[@memberships.length] = Membership.new(comm)
      # For each community, find its members
      members_results = @db.query("select owner from #{@db.table_prefix}friends where friend = '#{comm.ident}'")
      members_results.each do |members_result|
        # need the username
        user_results = @db.query("select username from #{@db.table_prefix}users where ident = '#{members_result[OWNER]}'")
        user_result = @db.get_first_result(user_results)
        if (user_result != nil)
          @memberships[@memberships.length-1].add_member(Member.new(user_result[USERNAME]))
          puts "#{@memberships[@memberships.length-1].group.name} -> #{user_result[USERNAME]} -> #{user_result[USERNAME]}"
        end
      end
    end
    @memberships
  end
end