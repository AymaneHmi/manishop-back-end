<?php

function filterHtmlTags($inputHtml) {
    $allowedTags = array('<p>', '<h1>', '<h2>', '<h3>', '<strong>', '<em>', '<a>', '<br>', '<ul>', '<li>', '<ol>', '<u>');
    $filteredHtml = strip_tags($inputHtml , $allowedTags);
            
    return $filteredHtml;
};

function createSlug($title, $table) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    return $slug;
};
