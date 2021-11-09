# Getting Started

I recommend installing using homebrew to manage different PHP versions
```bash
brew install php@7.4
brew link php@7.4
```

Make sure you are using composer > 2.0

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

