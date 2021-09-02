# Getting Started

**IMPORTANT NOTE:** Make sure you update your docker resources to increase your available RAM and CPU Cores. A recommended configuration is 6 CPU cores and 6 GB of RAM. If you do not do this, your GitLab container will not work.

I recommend installing (phpbrew)[https://github.com/phpbrew/phpbrew] to help manage different PHP versions
on your machine as this project uses php 7.4.*. Follow the instructions and make sure you update your
`.bashrc` and then `phpbrew install 7.4 +default +mysql +pdo`

Install xdebug with `phpbrew ext install xdebug`

Make sure you follow the log as it installs and use the `tail` command it gives you.
If you encounter any errors with missing packages, just install them via `brew`.

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

The default username is `root`, feel free to set any password.

If you don't get the page that allows you to set the root password, you can update it manually like so

```bash
yarn docker shell gitlab
gitlab-rake "gitlab:password:reset"
Enter username: root
```

Choose whatever password you want, and then re-visit `http://localhost:4000` to login

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

Now you can register your runner. Go to the runners page and find the registration token. Then, open a shell in the runner and run `gitlab-runner register` and follow the steps. Make sure you give it your jump tunnel URL for the base.

You can now view the coverage app at `http://localhost:8010`. You will need to refresh the browser page after
making changes since this app needs to be served by Laravel.

```bash
cd client/
yarn start
```
