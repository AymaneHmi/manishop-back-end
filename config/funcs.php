<?php

function filterHtmlTags($inputHtml) {
    $allowedTags = array('<p>', '<h1>', '<h2>', '<h3>', '<strong>', '<em>', '<a>', '<br>', '<ul>', '<li>', '<ol>', '<u>');
    $filteredHtml = strip_tags($inputHtml , $allowedTags);
            
    return $filteredHtml;
};

function createSlug($title, $table) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    // Check if the slug already exists in the database
    // $query = "SELECT * FROM $table WHERE slug = :slug";
    // $stmt = $connection->prepare($query);
    // $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
    // $stmt->execute();

    // $count = 1;
    // while ($stmt->rowCount() > 0) {
    //     // Slug already exists, add a unique identifier (e.g., -1, -2, etc.)
    //     $newSlug = $slug . '-' . $count;
        
    //     // Check if the new slug already exists
    //     $stmt->bindParam(':slug', $newSlug, PDO::PARAM_STR);
    //     $stmt->execute();
        
    //     $count++;
    // }

    // return $newSlug ?? $slug;
    return $slug;
};
