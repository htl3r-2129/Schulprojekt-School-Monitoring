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
        role INT,
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
    CREATE TABLE IF NOT EXISTS media(
        PK_Media_ID INT AUTO_INCREMENT,
        post INT,
        file_name VARCHAR(255),
        file_path VARCHAR(255),
        alt_text VARCHAR(255),
        PRIMARY KEY (PK_Media_ID),
        FOREIGN KEY (post) REFERENCES post (PK_Post_ID)
    )
    ",
    "
    CREATE TABLE IF NOT EXISTS website(
        PK_Website_ID INT AUTO_INCREMENT,
        post INT,
        url VARCHAR(255),
        alt_text VARCHAR(255),
        PRIMARY KEY (PK_Website_ID),
        FOREIGN KEY (post) REFERENCES post (PK_Post_ID)
    )
    ",
    "
    CREATE TABLE IF NOT EXISTS untis(
        PK_Untis_ID INT AUTO_INCREMENT,
        post INT,
        file_path VARCHAR(255),
        PRIMARY KEY (PK_Untis_ID),
        FOREIGN KEY (post) REFERENCES post (PK_Post_ID)
    )
    "
];

foreach ($queries as $query){
    $db->getConn()->query($query);
}
