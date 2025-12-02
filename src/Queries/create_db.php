<?php
namespace Insi\Ssm\Queries;
require_once '../vendor/autoload.php';
use Insi\Ssm\DB;

$db = new DB;

$queries = [
    "
    CREATE TABLE IF NOT EXISTS user(
        PK_User_ID INT AUTO_INCREMENT,
        username VARCHAR(16),
        email VARCHAR(255),
        password VARCHAR(255),
        rank INT,
        isLocked BOOLEAN,
        2faSuccess BOOLEAN,
        PRIMARY KEY (PK_User_ID)
    )
    ",
    "
    CREATE TABLE IF NOT EXISTS post(
        PK_Post_ID INT AUTO_INCREMENT,
        submittedBy INT,
        approvedBy INT,
        title VARCHAR(64),
        text TEXT,
        qPosition INT,
        isInstant BOOLEAN,
        date DATE,
        FOREIGN KEY (submittedBy) REFERENCES user (PK_User_ID),
        FOREIGN KEY (approvedBy) REFERENCES user (PK_User_ID),
        PRIMARY KEY (PK_Post_ID)
    )
    ",
    "
    CREATE TABLE IF NOT EXISTS post_contentobject(
        PK_Post_Content_ID INT AUTO_INCREMENT,
        FK_Post_ID INT DEFAULT 0,
        FK_Media_ID INT DEFAULT 0,
        FK_Website_ID INT,
        FOREIGN KEY FK_Post_ID references post (PK_Post_ID),
        FOREIGN KEY FK_Media_ID references media (PK_Media_ID),
        FOREIGN KEY FK_Website_ID references website (PK_Website_ID),
        PRIMARY KEY (PK_Post_Content_ID) --must be combination of foreign keys
    )
    ",
    "
    CREATE TABLE IF NOT EXISTS media(
        PK_Media_ID INT AUTO_INCREMENT,
        file_name VARCHAR(255),
        file_path VARCHAR(255),
        alt_text VARCHAR(255),
        PRIMARY KEY (PK_Media_ID)
    )
    ",
    "
    CREATE TABLE IF NOT EXISTS website(
        PK_Website_ID INT AUTO_INCREMENT,
        url VARCHAR(255),
        alt_text VARCHAR(255),
        PRIMARY KEY (PK_Website_ID)
    )
    "
];

foreach ($queries as $query){
    $db->getConn()->query($query);
}
