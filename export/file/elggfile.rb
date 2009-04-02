class ElggFile
  # owner = person:username
  # community = community:username
  attr_accessor :unique_id, :owner, :community, :title, :original_name, :description, :access, :time_uploaded, :content, :mime_type
  
  def initialize(unique_id)
    @unique_id = unique_id
    @content = ""
  end
end