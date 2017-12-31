# Comforter
When your code is covered, you feel warm and cozy inside

### Purpose
Comforter helps you feel good that the changes you made to a project didn't cause anyone any pain.  It makes sure that any change you make is a positive one, which gives everyone the warm fuzzies.

### Setup Dev Environment
Make sure you have `npm` and `node` installed, as well as `vagrant` and virtual box.

```shell
vagrant up
vagrant ssh gitlab
sudo nano /etc/gitlab/gitlab.rb
```

Modify `external_url` to be `http://192.168.33.52` and then

```shell
sudo gitlab-ctl reconfigure
exit
```

### Visit GitLab and update admin password
Visit `http://192.168.33.52` and you will be prompted to modify password.  Choose anything.  Then signin as username `root` and your password.

### Register the runner

`vagrant ssh runner` and then follow [these](https://docs.gitlab.com/runner/register/index.html) steps.

Choose shell as executor and use `http://192.168.33.52` as the coordinator URL.  To get a token,
visit `http://192.168.33.52/admin/runners` and grab the registration token off the page.

Add nvm to runner
```
sudo su gitlab-runner
curl -o- https://raw.githubusercontent.com/creationix/nvm/v0.33.8/install.sh | bash
```

### Setup Comforter In Gitlab as an App
* Go [here](http://192.168.33.52/admin/applications/new)
* Name = Comforter
* Redirect URI https://comforterdev.localtunnel.me
* Use **api** and **read_user** scopes
* Grab App ID and Secret and put into `settings.json` file as token and secret respectively

### Update your URLs in settings.json and app.js for app
* Update `https://gitlab.goreact.com` to `http://192.168.33.52` in settings.json on both gitlab.host and gitlab.api.
* Update gitlabHost constant in `app/app.js` to be `http://192.168.33.52` as well.

### Add the test project to gitlab
The folder in this repo called `test-project` can be moved out, git inited, and then added to your local gitlab instance.  It has a test job that runs and generates coverage and sends it to gitlab.

Generate a token to use for your project by visiting your admin users profile page, and then click access tokens.  Generate one with `api` and `read_user` scope and then go back to your project and visit `settings/ci_cd` off your projects main url.  Add a secret variable called `GITLAB_API_KEY` and use the token you just made.

## IMPORTANT NOTE
* **DO NOT** commit changes to the strings you changed to `http://192.168.33.52`.  Until we have this slightly better, just be sure not to commit that.

Now you can start the app
```
yarn && yarn start
```

Then you can spin up the site with `yarn serve`

Start deving away.

Follow the documentation for [generator-ng-gulp](https://github.com/erikdonohoo/generator-ng-gulp) to add front-end components.

You can add a project to your new gitlab instance and attach comforter to it.
It should run

### TODOs

* [x] handle uploading lcov and zipped html coverage
* [x] npm tool for sending lcov and zipped html after tests run
* [x] connect to gitlab api and update commit statuses through process
* [ ] save unzipped html coverage to folder for a repo on branch basis
* [x] add endpoints and collection in mongo for branch coverage
* [ ] delete uploaded zip/lcov after processing
