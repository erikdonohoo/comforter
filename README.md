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

Setup [gitlab runner](https://docs.gitlab.com/runner/install/linux-repository.html) with ubuntu instructions.

Use the url `http://192.168.33.52` and the runner token found [here](http://192.168.33.52/admin/runners) after running vagrant up.

### Setup Comforter In Gitlab as an App
* Go [here](http://192.168.33.52/admin/applications/new)
* Name = Comforter
* Redirect URI http://192.168.33.51
* Use **api** and **read_user** scopes
* Grab App ID and Secret and put into `settings.json` file as token and secret respectively


### Update your URLs in settings.json and app.js for app
* Update `https://gitlab.goreact.com` to `http://192.168.33.52` in settings.json on both gitlab.host and gitlab.api.
* Update gitlabHost constant in `app/app.js` to be `http://192.168.33.52` as well.

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
