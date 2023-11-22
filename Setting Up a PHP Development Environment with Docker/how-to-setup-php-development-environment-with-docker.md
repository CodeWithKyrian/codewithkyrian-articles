---
title: "How to Setup the Ultimate PHP Environment with Docker"
description: "Discover how to streamline your PHP development environment using Docker with this comprehensive guide. Set up PHP containers, integrate MySQL and phpMyAdmin, manage front-end assets with Node.js and NPM, and optimize for Laravel-specific configurations"
category: Guides & Tutorials
tags: [ "Docker", "PHP" ]
keywords: "docker php development environment, docker php development, docker php development environment, docker php development environment windows"
---

## Introduction

Hey Hey Hey! Welcome. Remember the days when setting up a PHP development environment meant wrestling with XAMPP, LAMP,
or similar tools? Windows, Linux, Mac - we all had our versions. It's fine for a while but when we have different
projects with different environments, dependencies, and requirements, problems pile up easily. What about the infamous '
it works on my machine' ordeal 😅; those were not ideal.

The recent Laravel Herd is indeed a step in the right direction, but, let's be real, not everyone can casually toss a
MacBook into their shopping cart (unless you've got a money tree growing in the backyard, in which case, can we be
friends?), and frankly, not everyone wants to use a Mac for development. Unfortunately, we haven't stumbled upon a tool
with the same feature set
as Herd for the rest of us just yet.

Docker, on the other hand, offers a modern and versatile alternative that can be effortlessly replicated across all
sorts of machines. It streamlines application management, and it's about to become your new best friend in the
development world.

In this guide, I'm going to demonstrate how simple and efficient it is to set up a PHP environment that not only
embodies flexibility and robustness but is also remarkably customizable. This recipe has become the default for all my
projects, and I'm eager for you to experience its seamless workflow. Also, it's not limited to any specific PHP project;
with minor tweaks, it works beautifully with projects like Laravel or CodeIgniter.

Let's jump right in, step by step, beginning with our PHP container and slowly shaping our environment along the way.

## Step 1: Setting Up the PHP Container

Let's kick things off by crafting a Docker Compose file that defines our PHP service and tunes the essential settings. A
good practice is to have this file residing in the project's root directory with the name `docker-compose.yml` . While
the sea of available images may seem vast, through my own exploration and hands-on testing, I've found the webdevops
images to shine the brightest in terms of versatility and adaptability.

```yaml
version: '3'
services:
  php:
    image: 'webdevops/php-nginx-dev:8.2-alpine'
    working_dir: /var/www
    ports:
      - '80:80'
      - '443:443'
    volumes:
      - ./:/var/www
      - ./docker/nginx.conf:/opt/docker/etc/nginx/vhost.conf
    networks:
      - app_network

networks:
  app_network: 
```

In this configuration:

- `image`:  I've opted for the Alpine Linux variant. This choice helps us keep the container size in check, but as you
  might expect, it does come with some trade-offs—nothing we can't handle down the road.
- `working_dir`: This setting lets us define the working directory within the container.
- `ports`: We're mapping the host machine's ports to the container. In this case, port 80 is earmarked for Nginx, while
  port 443 is reserved for SSL.
- `volumes`: Here, we're making use of volumes to connect our local directories with the container. Don't worry if you
  don't see these files just yet; we'll create them shortly.
- `networks`: The `app_network` configuration ensures seamless communication between different services that we're going
  to create, enabling them to work together harmoniously.

Now let's create those files. In your root folder, create a new folder `docker`  to house all configurations and create
a new file named ( `nginx.conf`) for our PHP service.

```nginx
server {
    server_name localhost;
    listen      80;

    error_page 404 400 403 401  @php;
    error_page 500 502 503 504  @php;

    location / {
        root        /var/www/public;
        try_files   $uri $uri/ @php;
    }

    location @php {
        fastcgi_pass                php;
        include                     fastcgi_params;
        fastcgi_param SCRIPT_FILENAME /var/www/public/index.php;
        fastcgi_param PATH_INFO     $fastcgi_path_info;
        fastcgi_intercept_errors    off;
    }
}

```

This Nginx configuration ensures that incoming requests are directed to the appropriate PHP script. Feel free to
customize the `server_name` directive by adding your desired domain, eg \`server_name localhost example.com\`. Just
remember to set up the respective host entries accordingly. (If you're using WSL2, make sure you're setting the IP to
the actual IP of your WSL2 instance, not just '127.0.0.1,' when adding your domain to the hosts file.

## Step 2: Adding MySQL Support

Next, let's add MySQL support to our Docker Compose configuration. We'll use the official MySQL Docker image. Append
this service to your `docker-compose.yml`  after the PHP service.

```yaml
...
mysql:
  image: 'mysql/mysql-server:8.0'
  ports:
    - '${DB_PORT:-3306}:3306'
  environment:
    MYSQL_ROOT_PASSWORD: '${DB_PASSWORD}'
    MYSQL_ROOT_HOST: '%'
    MYSQL_DATABASE: '${DB_DATABASE}'
    MYSQL_USER: '${DB_USERNAME}'
    MYSQL_PASSWORD: '${DB_PASSWORD}'
    MYSQL_ALLOW_EMPTY_PASSWORD: 1
  volumes:
    - 'mysql_storage:/var/lib/mysql'
  networks:
    - app_network

networks:
  app_network:

volumes:
  mysql_storage:

```

In this configuration:

- `image`: Specifies the MySQL Docker image.
- `environment`: Sets environment variables for MySQL. The `${}` notation is used to fetch values from the environment
  or an `.env` file if available, with the value after `:-` serving as the default.
- `volumes`: Mounts a volume to ensure persistent MySQL data. We created the named volume as well.
- `ports`: Maps the host machine's port (defaulting to 3306) to the container's MySQL port.

After adding the MySQL service, don't forget to use the name of the MySQL service, which is `mysql` in our case, as the
connection host for MySQL in your PHP application. Since they belong to the same network `app_network` , the host name
will be resolved to the container's IP automatically for us. For Laravel projects, set `DB_CONNECTION` in the .env file
to 'mysql' and `DB_HOST` to 'mysql' as well.

## Step 3: Integrating phpMyAdmin

To manage our MySQL database easily, let's integrate phpMyAdmin into our Docker setup.

```yaml
...
phpmyadmin:
  image: 'phpmyadmin/phpmyadmin'
  links:
    - mysql:mysql
  ports:
    - '${PMA_PORT:-8080}:80'
  environment:
    PMA_HOST: mysql
    MYSQL_USERNAME: '${DB_USERNAME}'
    MYSQL_ROOT_PASSWORD: '${DB_PASSWORD}'
    PMA_USER: '${DB_USERNAME}'
    PMA_PASSWORD: '${DB_PASSWORD}'
  networks:
    - app_network
...
```

This configuration links phpMyAdmin to our MySQL service and exposes it on a specified port for easy database
management. You can now access your phpMyAdmin using `localhost:8080` , easy!

## Step 4: Configuring MailPit

Personally, I find integrating MailPit incredibly beneficial since it's easy to catch and analyze emails sent in my
development environment effortlessly. If you'd like to give it a try in your setup,add this under the `phpmyadmin`
service.

```yaml
...
mailpit:
  image: 'axllent/mailpit:latest'
  ports:
    - '${MAIL_PORT:-1025}:1025'
    - '${MAIL_DASHBOARD_PORT:-8025}:8025'
  networks:
    - app_network
...
```

By adding this service, we can test email functionality locally during development.

## Step 5: Incorporating NPM for Front-End Assets

For Laravel or projects using similar build processes, integrating Node.js and NPM is crucial. Let's extend our Docker
Compose configuration to include a Node.js service.

```yaml
...
npm:
  image: 'node:18-alpine'
  working_dir: /var/www
  ports:
    - '${VITE_PORT:-5173}:5173'
  volumes:
    - ./:/var/www
  networks:
    - app_network
  depends_on:
    - php
  command: sh -c "npm run dev"
...
```

In this configuration:

- `image`: We opt for the Alpine Linux variant of the Node.js image to keep our container size to a minimum.
- `ports`: We expose port 5173 for Vite by default, but you can adjust this to 3000 or any other port based on your
  project's specific setup.
- `volumes`: We link the same volume, facilitating a connection between our local project directory and the container
  for streamlined development.
- `command`: This command starts the development server with 'npm run dev' when the container starts.

This configuration sets up a Node.js service, essential for managing front-end assets in Laravel projects.

## Step 6: Laravel-Specific Configurations

### Adding the Laravel Scheduler

We can further extend our setup to accommodate Laravel-specific configurations. For example, let's add the Laravel
scheduler to our setup:

```yaml
...
php:
  ...
  volumes:
    ...
    - ./docker/scheduler.sh:/opt/docker/bin/service.d/cron.d/scheduler.sh
  ...
  ...
```

And then create the `scheduler.sh` script in the `docker` folder.

```bash
#!/bin/sh

# Create the Laravel scheduler log file
touch /var/www/storage/logs/scheduler.log

# Add the Laravel scheduler to the root user's crontab
echo "*  *  *  *  *  php /var/www/artisan schedule:run >> /dev/null 2>&1" >> /etc/crontabs/root
```

In this script:

- We created a log file at `/var/www/storage/logs/scheduler.log`  to capture scheduler activity, making it easier to
  track and troubleshoot scheduled tasks.
- Next, we append the schedule run command to the root user's crontab. The `>> /dev/null 2>&1` part suppresses any
  output or error messages from this command.

> The location where you add scheduling commands varies based on the Linux distribution used in your Docker image. Since
> we're using an Alpine Linux-based image, we'll directly insert the scheduling command into the `/etc/crontabs/root`
> file. If you chose to use the Debian-based variant, instead, then the process is a bit different. Create a separate
> crontab file with your scheduling command and place that file in the `/etc/cron.d` folder to schedule tasks.

For scheduling tasks outside Laravel scheduler (non-Laravel users perhaps), you can utilize the `/etc/periodic` folder,
which contains subfolders like '15min,' 'daily,' and 'monthly.' These subfolders are quite intuitive, allowing you to
schedule scripts to run at different intervals. This flexibility simplifies the automation of tasks within your Docker
environment.

To make the `scheduler.sh` script executable, run the following command in your project's root folder:

```bash
chmod +x ./docker/scheduler.sh
```

### Adding the Laravel Queue Worker

A lot of people recommend using a separate container to handle queue workers, but we'll keep it simple and manage everything
within the same container. Fortunately, the webdevops image we're using already run Supervisor, and it manages PHP-FPM, Nginx, and Cron. Adding our queue workers to be managed by Supervisor is a breeze.

First, let's create a `queue-worker.conf` file in the docker folder with the following content:

```ini
[group:queue-worker]
programs = queue-workerd
priority = 10

[program:queue-workerd]
process_name = %(program_name)s_%(process_num)02d
command = php /var/www/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart = true
autorestart = true
stopasgroup = true
killasgroup = true
user = application
numprocs = 3
redirect_stderr = true
stdout_logfile = /var/www/storage/logs/queue-worker.log
stopwaitsecs = 3600
```

Let's break down what this configuration does:

- `[group:queue-worker]` defines a Supervisor group for our queue workers.
- `[program:queue-workerd]` sets up the Supervisor program for our queue workers.
- `command` specifies the command to run Laravel's queue worker with some parameters.
- `numprocs` is set to 3, meaning that only 3 workers will run concurrently. You can adjust this number based on your
  needs.
- `stdout_logfile` is set to /var/www/storage/logs/queue-worker.log, allowing us to monitor the queue worker's activity.

Now, let's integrate this configuration into our Docker Compose file by adding it to the volumes section of the php
service:

```yaml
...
php:
  ...
  volumes:
    ...
    - ./docker/queue-worker.conf:/opt/docker/etc/supervisor.d/queue-worker.conf
  ...
  ...
```

There are so many other Laravel-specific configurations you can add to your setup, but I'll leave that for you to
explore.

## Step 7: Running the Containers

Congratulations! You've successfully configured your PHP development environment using Docker. Now, let's fire up the
containers and witness the magic. Navigate to the root directory of your project where the docker-compose.yml file
resides and execute the following command:

```bash
docker-compose up -d
```

The -d flag instructs Docker to run the containers in the background, allowing you to continue using your terminal.
Initially, Docker will pull the necessary images if they're not already available on your system. Now this might take a
bit of time during the first run, but subsequent runs will be lightning-fast.

Once everything is up and running, you can access your application via `http://localhost` (or the domain name you set
up) in your web browser. Isn't it amazing how simple and efficient it is to spin up your project with a single command?
Docker and this setup make development a breeze.

### Why didn't we use Dockerfiles in this setup?

Well, picture this scenario: You've got a bunch of projects, each with its unique requirements. If you were to create a
Dockerfile for every single project we have, you'd easily end up with a hefty collection of images cluttering your
system! Each Dockerfile created a new image, and that can get overwhelming, especially in a development environment. So,
instead of going down that rabbit hole, I went for a different approach.

I opted for using base images that were pretty close to what we needed and still quite optimized. Then, we fine-tuned
those images using the Docker Compose file. Voilà! One-size-fits-all… well, most.

Now, when it's showtime and we're talking production environments, Dockerfiles are the stars of the show. They're more
streamlined and can be smoothly deployed across various setups. We'll definitely dive into that topic in future updates,
so stay tuned for more Docker best practices.

For now, enjoy the efficiency of this setup, and keep an eye out for more insights into Docker coming your way!

## Conclusion

To recap our journey, We:

- Set up a PHP container and configure it to our needs.
- Added MySQL support for our database requirements.
- Integrated phpMyAdmin for easy database management.
- Explored the benefits of MailPit for email testing during development.
- Incorporated Node.js and NPM for managing front-end assets in projects like Laravel.
- Tailored the setup for Laravel-specific configurations, including the all-important scheduler.
- Ran the containers and witnessed the magic.
- Discussed why we didn't use Dockerfiles in this setup.

In conclusion, I hope this guide has helped you streamline your PHP development environment using Docker. If you have
any
questions or need assistance, feel free to reach out to me via email. You can also find the complete setup
on [GitHub](https://github.com/CodeWithKyrian/dockerphp-setup). For an even quicker setup (Linux, Mac and WSL users),
you can run the command in your project's root folder.

```bash
curl -s https://codewithkyrian.com/dockerphp-setup | bash
```

And this copies over the necessary files we discussed in this article for you. Chikena! Happy coding, and may your
projects thrive in this efficient and flexible environment friends!   
