require 'config'

class Utils
  def Utils.find_icon_path(username, icon_filename)
    # Find out where the icon file is. The path can have upper and lower case combinations
    icon_path = ""
    upper_case_icon_path = "#{Config::ELGG_DATA_DIR}#{Config::ELGG_ICON_DIR}/#{username[0,1].upcase}/#{username}/#{icon_filename}"
    lower_case_icon_path = "#{Config::ELGG_DATA_DIR}#{Config::ELGG_ICON_DIR}/#{username[0,1].downcase}/#{username}/#{icon_filename}"
    if (File.exist?(upper_case_icon_path))
      icon_path = upper_case_icon_path
    end
    if (File.exist?(lower_case_icon_path))
      icon_path = lower_case_icon_path
    end
    icon_path
  end
end