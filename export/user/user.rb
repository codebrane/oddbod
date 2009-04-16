require 'utils'

class User
  attr_reader :ident, :username, :email, :name, :icon_filename, :icon_data, :active, :user_alias, :admin
  
  def initialize(ident, username, email, name, icon_filename, icon_data, active, user_alias, admin)
    @ident = ident
    @username = username
    @email = email
    @name = name
    @icon_filename = icon_filename
    @active = active
    @user_alias = user_alias
    @admin = admin
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