require 'base64'

class Icon
  attr_reader :username, :filename, :description, :data
  
  def initialize(username, filename, description, data)
    @username = username
    @filename = "/icons/#{username[0,1]}/#{username}/#{filename}"
    @description = description
    @data = Base64.encode64(File.open("/Users/alistair/Desktop/67.jpg",'rb') { |f| f.read })
  end
end