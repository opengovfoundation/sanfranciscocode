
namespace :world do
  apps = [
    'administrative',
    'building',
    'business',
    'campaign',
    'charter',
    'elections',
    #'electrical',
    'environment',
    'fire',
    'health',
    #'housing',
    #'mechanical',
    'park',
    'planning',
    #'plumbing',
    'police',
    'port',
    'public-works',
    'subdivision',
    'transportation',
    'www'
  ]

  task :deploy do
    env = ENV['ENV'] || 'staging'
    apps.each do |app|
      # www only should ever deploy www.
      branch = ENV['BRANCH'] || 'master'
      if app == 'www'
        branch = 'www'
      end
      Capistrano::CLI.ui.say "deploying #{app}:#{env}"
      system("cap #{app}:#{env} branch='#{branch}' deploy")
    end
  end


  task :setup do
    env = ENV['ENV'] || 'staging'
    apps.each do |app|
      Capistrano::CLI.ui.say "setting up #{app}:#{env}"
      system("cap #{app}:#{env} deploy:setup")
    end
  end

end

