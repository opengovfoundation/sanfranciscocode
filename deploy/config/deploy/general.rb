
namespace :world do
  apps = [
    'bic-codes',
    'business',
    'campaign',
    'charter',
    'elections',
    'environment',
    'fire',
    'health',
    'park',
    'planning',
    'port',
    'public-works',
    'subdivision',
    'transportation',
    'zoning'
  ]

  task :deploy do
    env = ENV['ENV'] || 'staging'
    apps.each do |app|
      Capistrano::CLI.ui.say "deploying #{app}:#{env}"
      system("cap #{app}:#{env} deploy")
    end;
  end


  task :setup do
    env = ENV['ENV'] || 'staging'
    apps.each do |app|
      Capistrano::CLI.ui.say "setting up #{app}:#{env}"
      system("cap #{app}:#{env} deploy:setup")
    end;
  end

end

