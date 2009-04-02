require 'config'
require 'file/elggfile'
# gem install mime-types
require 'mime/types'

class FileFactory
  OWNER = 1
  FILE_OWNER = 2
  FOLDER = 3
  TITLE = 5
  ORIGINAL_NAME = 6
  DESCRIPTION = 7
  LOCATION = 8
  ACCESS = 9
  TIME_UPLOADED = 11
  
  def initialize(db)
    @db = db
  end
  
  def load_files
    files = Array.new
    file_results = @db.query("select * from #{@db.table_prefix}files")
    file_results.each do |file_result|
      puts "processing file: #{file_result[ORIGINAL_NAME]}"
      
      files[files.length] = ElggFile.new((Time.now.to_i + rand(100_000_000)).to_s(16))
      
      files[files.length-1].title = file_result[TITLE]
      files[files.length-1].original_name = file_result[ORIGINAL_NAME]
      files[files.length-1].description = file_result[DESCRIPTION]
      files[files.length-1].access = file_result[ACCESS]
      files[files.length-1].time_uploaded = file_result[TIME_UPLOADED]
      files[files.length-1].mime_type = MIME::Types.type_for(file_result[ORIGINAL_NAME])[0]
      
      # The actual owner of the file
      user_results = @db.query("select username from #{@db.table_prefix}users where ident = '#{file_result[OWNER]}'")
      user_result = @db.get_first_result(user_results)
      if (user_result != nil)
        files[files.length-1].owner = user_result[0]
      end
      
      # The community that contains the file
      if (file_result[OWNER] != file_result[FILE_OWNER])
        user_results = @db.query("select username from #{@db.table_prefix}users where ident = '#{file_result[FILE_OWNER]}'")
        user_result = @db.get_first_result(user_results)
        if (user_result != nil)
          files[files.length-1].community = user_result[0]
        end
      end
      
      # The file contents
      file_path = Config::ELGG_DATA_DIR + file_result[LOCATION]
      if (File.exist?(file_path))
        files[files.length-1].content = Base64.encode64(File.open(file_path,'rb') { |f| f.read })
      end
    end
    
    files
  end
end