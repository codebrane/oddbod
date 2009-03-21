class ProfileItem
  attr_reader :item_type, :username, :access, :name, :value
  
  def initialize(item_type, username, access, name, value)
    @item_type = item_type
    @username = username
    @access = access
    @name = name
    
    # Profile photo will initially contain the full path to the photo file.
    # It'll already have been checked for existence by the factory...
    if (name == "profilephoto")
      # ...load the binary data and store it in this field. The original filename
      # will be preserved in profilephoto_filename
      @value = Base64.encode64(File.open(value,'rb') { |f| f.read })
    else
      @value = value
    end
  end
end