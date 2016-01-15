# Comforter
When your code is covered, you feel warm and cozy inside

### Purpose
Comforter helps you feel good that the changes you made to a project didn't cause anyone any pain.  It makes sure that any change you make is a positive one, which gives everyone the warm fuzzies.

### Setup Dev Environment
Make sure you have `npm` and `node` installed, as well as `vagrant` and virtual box.

```shell
vagrant up
npm install -g generator-ng-gulp gulp-cli yo
npm install
npm start # This command will stay running and watching your server components
```

Then you can spin up the site with `gulp serve`

Start deving away.

Follow the documentation for [generator-ng-gulp](https://github.com/erikdonohoo/generator-ng-gulp) to add front-end components.

### TODOs

* [x] handle uploading lcov and zipped html coverage
* [x] npm tool for sending lcov and zipped html after tests run
* [x] connect to gitlab api and update commit statuses through process
* [ ] save unzipped html coverage to folder for a repo on branch basis
* [x] add endpoints and collection in mongo for branch coverage
* [ ] delete uploaded zip/lcov after processing
