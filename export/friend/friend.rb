class Friend
  attr_reader :owner_username, :username, :status
  
  def initialize(owner_username, username, status)
    @owner_username = owner_username
    @username = username
    @status = status
  end
end