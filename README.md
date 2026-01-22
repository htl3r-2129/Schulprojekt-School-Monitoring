# How to set up the project

First use the following command to **build** a custom docker image. This image includes an **Apache Web Server** as well
as **Composer**.


```shell
docker build -t ssm-php .
```

Next run *docker compose up* to pull a **mariadb** image and set up a working environment.
```shell
docker compose up -d
```

To initialize a **admin user** use the folowing comands in the **database IDE** of your choice.

```shell
create database if not exists monitor;
use monitor;
```

Then after creating a user through the **sign up page** find out your **user id**.

```shell
select * from user;
```

Next run *update user* to grant the user **admin privileges**.

```shell
update user set role = 2 where PK_User_ID = '<user id goes here>';
```

Moderator and user roles can be assigned the **same way** (2 = admin; 1 = moderators; 0 = user).


---

# User Guide

This project uses a role-based system with three user roles: **Admin**, **Moderator**, and **User**. Each role has different permissions and responsibilities.


## Roles Overview

### Admin

The **Admin** has full access to the system and all moderator functions.

**Admin can:**

* Change display times via the **Admin Page**
* Manage users on the **ManageUser page**:

  * Block or unblock users
  * Change user roles (User ↔ Moderator)
* Access and use all **Moderator features**

---

### Moderator

The **Moderator** is responsible for managing and approving content.

**Moderator can:**

* Manage the display queue on the **Mod page**:

  * Add content to the queue
  * Remove content from the queue
* Review user-uploaded content on the **Content Approver page**:

  * Approve content
  * Delete content

**Important:**

* Approved content is added to the queue on the **Mod page**.
* Content is shown on the display **only after the queue is accepted**.

---

### User

A **User** can upload content to the system.

**User can upload:**

* Text-only content
* Images or videos with optional text

**Requirements:**

* A **title is required** for every upload.

---

## Display / Content Switcher

* The display that shows all queued content is called the **Content Switcher**.
* It must be opened **manually** on the target screens.

**URL:**

```
<URL>/contentSwitcher.php
```

This page displays all content that has been approved and accepted in the queue.

---

## Basic Workflow

1. A **User** uploads content (title required).
2. A **Moderator** or **Admin** reviews the content on the Content Approver page.
3. Approved content is added to the queue.
4. The **Moderator** or **Admin** accepts the queue.
5. The content becomes visible on the Content Switcher display.

