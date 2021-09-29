# Getting Started

**IMPORTANT NOTE:** Make sure you update your docker resources to increase your available RAM and CPU Cores. A recommended configuration is 6 CPU cores and 6 GB of RAM. If you do not do this, your GitLab container will not work.

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

It may take a while for it to finish setting up, but wait until `http://localhost:4000` is running and you can get to the login page for GitLab

The default username is `root`, password is `password`

Visit `http://localhost:4000` to login (May take a few minutes to be fully up and ready)

Now setup an app for comforter in gitlab, and add keys to .env

1. Visit the admin area by clicking the wrench in the toolbar
2. Click applications on the left panel
3. Click New Application. The Name should be `Comforter`, and Redirect URI should be `http://localhost:8010`. Check the box for Trusted and give it the `api` scope.
4. Take the keys you get, and set them in your `.env` for `GITLAB_OAUTH_ID` and `GITLAB_OAUTH_SECRET`
5. Create an access_token for your root user, and choose API access, and copy and paste that into `GITLAB_ACCESS_TOKEN`


The comforter server itself is running at `http://localhost:8010`. To set up the DB in your DB GUI of choice, just take a look
at the `docker-compose.yml` file to grab the DB info you need.

## Development

Once you have the app setup, start the client.

Because of some issues with the OAuth integration, you have to visit your GitLab over a jump tunnel URL, as
your GitLab needs to be visited both by you in the browser, and have endpoints called by our backend but you can
only register one URL. Since one would work with localhost but the other would need to be a docker URL
JumpTunnel is the only way around this for now.

So in your `.ssh/config` file register port `4000` to one of your available jump tunnel paths and configure that. An example is below, where you would replace `{JUMP_PORT}` with one of your available ports.

```
RemoteForward 1{JUMP_PORT} 127.0.0.1:4000
```

Once configured, set `GITLAB_DOMAIN` in your .env to `https://jump.goreact.com:<port you just chose>`

You can now view the coverage app at `http://localhost:8010`. You will need to refresh the browser page after
making changes since this app needs to be served by Laravel.

```bash
cd client/
yarn start
```
