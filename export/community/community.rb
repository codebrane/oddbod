require 'utils'

class Community
  attr_reader :ident, :username, :name, :icon_filename, :icon_description, :icon_data, :active, :comm_alias, :file_quota, :owner, :moderation
  
  def initialize(ident, username, name, icon_filename, icon_description, active, comm_alias, file_quota, owner, moderation)
    @ident = ident
    @username = username
    @name = name
    @icon_filename = icon_filename
    @icon_description = icon_description
    @active = active
    @comm_alias = comm_alias
    @file_quota = file_quota
    @owner = owner
    @moderation = moderation
    
    if (@icon_filename != "")
      icon_path = Utils.find_icon_path(@username, @icon_filename)
      if (icon_path != "")
        @icon_data = Base64.encode64(File.open(icon_path,'rb') { |f| f.read })
      else
        @icon_data = ""
      end
    else
      @icon_data = ""
    end
  end
end