<?php
// slides.php

// JSON-Daten (kannst du hier anpassen)
$slides = [
    [
        "title" => "VIDTEST",
        "type"  => "video",
        "media" => "./media/Videos/WALKWAY0025-0220.mp4",
        "text"  => "So ein schönes Vid."
    ],
    [
        "title" => "IMGTEST mit Text",
        "type"  => "image",
        "media" => "./media/Images/Houser.jpg",
        "text"  => "So ein schönes Bild"
    ],
    [
        "title" => "Text Only",
        "type"  => "",
        "media" => "",
        "text"  => "Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum Lorem Ipsum."
    ],
    [
        "title" => "IMGTEST ohne Text",
        "type"  => "image",
        "media" => "./media/Images/AWWWWWWWWWWWW.jpg",
        "text"  => ""
    ]
];

// Header setzen, damit JS das als JSON erkennt
header('Content-Type: application/json');

// JSON ausgeben
echo json_encode($slides, JSON_PRETTY_PRINT);
