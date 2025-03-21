### Requirements
Ubuntu with Docker installed.

### Run on local machine
1. Inside the `app` folder, create a `.env` file:
```
cp .env.example .env
```
2. Inside the `docker` folder, create the network, build the image, and run the project:
```
./create-network.sh
./build.sh
./up.local.sh
```

The project will be available at [https://localhost](https://localhost)

The login page for WordPress will be available at [https://localhost/wordpress/wp-admin/](https://localhost/wordpress/wp-admin/)

### Run on a remote server

1. Inside the `app` folder, create a `.env` file: 
```
cp .env.example .env
```
2. Set the `APP_URL` in the `.env` file: replace `localhost` with your domain name.
3. In the `docker/app/supervisord.conf` file, find `--host=localhost` and replace `localhost` with your domain name.
4. In the `docker/up.local.sh` file, find `WORDPRESS_URL="https://localhost/wordpress"` and replace `localhost` with your domain name.
5. Inside the `docker` folder, create the network, build the image, and run the project:
```
./create-network.sh
./build.sh
./up.local.sh
```

The project will be available at `https://your_domain_name`

The login page for WordPress will be available at `https://your_domain_name/wordpress/wp-admin/`
