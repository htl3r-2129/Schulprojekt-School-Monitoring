## How to set up the project

First use the following command to **build** a custom docker image. This image includes an **Apache Web Server** as well
as **Composer**.


```shell
docker build -t ssm-php .
```

Next run *docker compose up* to pull a **mariadb** image and set up a working environment.
```shell
docker compose up -d
```

To initialize a admin User use the folowing comands in the **database IDE** of your choice.

```shell
create database if not exists monitor;
use monitor;
```

Then after creating a user through the sign up function find out your **user id**.

```shell
select * from user;
```

Next run *update user* to grant the user **admin privileges**.

```shell
update user set role = 2 where PK_User_ID = '<user id goes here>';
```

Moderator and user roles can be assigned the same way (2 = admin; 1 = moderators; 0 = user).
