# Getting Started

I recommend installing using homebrew to manage different PHP versions
```bash
brew install php@8.1
brew link php@8.1
```

Make sure you are using composer > 2.2

You will need `docker` installed. To setup:

```bash
yarn setup
```

Once your DB is up, you can run the following:
```bash
cd web/
php artisan key:generate --ansi
php artisan passport:keys
```

You need your own personal `https://gitlab.com` account. You will use this environment to test Comforter.

Now setup an app for comforter in gitlab, and add keys to .env

1. Click applications on your user preferences
2. Click New Application. The Name should be `Comforter`, and Redirect URI should be `http://localhost:8010`. Check the box for Trusted and give it the `api` scope.
3. Take the keys you get, and set them in your `.env` for `GITLAB_OAUTH_ID` and `GITLAB_OAUTH_SECRET`
4. Create an access_token for your user for comforter, and choose API access, and copy and paste that into `GITLAB_ACCESS_TOKEN`


The comforter server itself is running at `http://localhost:8010`. To set up the DB in your DB GUI of choice, just take a look
at the `docker-compose.yml` file to grab the DB info you need.

## Development

Start the angular app with

```bash
cd client/
yarn start
```

You can now view the coverage app at `http://localhost:8010`. You will need to refresh the browser page after
making changes since this app needs to be served by Laravel.

## Getting Coverage to Work And Update Locally

Your best bet locally is to make a repo in gitlab, make some commits, and manually call
comforters endpoints with some data to see the updates in comforter, and the commit statuses in gitlab.

* Make a repo in gitlab.com and add an initial commit (a README or something)
* Use the comforter cli to send some data to comforter.
  * Run the tests here for comforters front-end to generate an LCOV and Zip (`yarn test:ci`)
  * Fill in the variable details in `package.json` `report-coverage-test` job.
    * The project ID is found on the main page of your gitlab.com repo.
    * The api key is the personal access token you made and put in your `.env`
    * Set the branch and commit to the branch and commit in your `gitlab.com` repo you made.
* Run the `report-coverage-test` job, and you should see a new entry in your `http://localhost:8010` comforter, as well as a green external job mark in gitlab.com showing the coverage ran.
* You can run the process again by adding a new commit to your repo, making a small change here in the actual comfoter client, regenerate the coverage, and then run the `report-coverage-test` command again but with an updated commit id.

