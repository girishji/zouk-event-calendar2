# zouk-event-calendar

web/ has the code
--

create .gitignore and put vendor/ in it
vendor dir is where you download dependencies in case to test locally

sudo heroku local // run locally

composer update
--
to deply apps (after each change):
git add .
git commit -m "message"
git push heroku master
git add .;git commit -m "message";git push heroku master

====
starting from scratch:

// initialize git for your project, add the changes and perform a commit

git init
git add .
git commit -m "first commit"
// create heroku app and push to heroku

heroku create
git push heroku master

--

start app: (it will give you web address)
heroku login
heroku create // start a vm (app) to receive your git code

 //start app (can kill it or view it by logging into heroku on web)
--

heroku logs
heroku ps
--
heroku open // opens website
--

Use chrome debugger. views->developer tools
see console log tab (always keep open)

also use heroku logs to debug
--

