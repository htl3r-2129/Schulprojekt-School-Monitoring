## How to setup the project

First use the following command to **build** a custom docker image. This image includes an **Apache Web Server** as well
as **Composer**.


```shell
docker build -t ssm-php .
```

Next run docker compose up to pull a **mariadb** image and set up a working environment.
```shell
docker compose up -d
```