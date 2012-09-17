require 'sinatra/base'
require 'semantic'

class Application < Sinatra::Base
  JQM_TO_JQUERY_MAPPINGS = {
    '1.0.1'       => '1.6.4',
    '1.1.0'       => '1.7.1',
    '1.1.1-rc.1'  => '1.7.1',
    '1.1.1'       => '1.7.1'
  }

  JQM_VERSIONS = Dir['public/jqm/*'].map { |d| Semantic::Version.new File.basename(d) }

  get '/' do
    @jqm_version = params[:ver] || JQM_VERSIONS.sort.last
    @jquery_version = JQM_TO_JQUERY_MAPPINGS[@jqm_version.to_s] || '1.6.4'
    @style_id = params[:style_id]

    erb :index
  end

  post '/kuler' do
    
  end
end

