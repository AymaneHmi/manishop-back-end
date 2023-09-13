<?php

include '../../config/headers.php';

if (isset($postdata) && !empty($postdata)) {

    $response = array();

    $product_slug = filter_var($postdata['product_slug'], FILTER_SANITIZE_STRING);

    if(empty($product_slug)) {
        $response = array('error' => 'product slug is requiered');
        echo json_encode($response);
        exit;
    }

    $sql = "SELECT * FROM products WHERE slug = '$product_slug'";
    $result = mysqli_query($conn, $sql);

    if (mysqli_num_rows($result) == 1) {
        $product = mysqli_fetch_assoc($result);

        $product_id = $product['product_id'];
        $category_id = $product['category_id'];
        $subcategory_id = $product['subcategory_id'];

        $categoryQuery = "SELECT * FROM categories WHERE category_id='$category_id'";
        $categoryResult = mysqli_query($conn, $categoryQuery);
        $category = mysqli_fetch_assoc($categoryResult);

        $subcategoryQuery = "SELECT * FROM subcategories WHERE subcategory_id='$subcategory_id'";
        $subcategoryResult = mysqli_query($conn, $subcategoryQuery);
        $subcategory = mysqli_fetch_assoc($subcategoryResult);

        $product_object = array(
            'id' => $product_id,
            'subcategory' => $subcategory['name'],
            'category' => $category['name'],
            'title' => $product['title'],
            'description' => $product['description'],
            'price' => $product['price'],
            'images' => json_decode($product['images'], true),
            'sizes' => json_decode($subcategory['size']),
            'available' => ($product['available'] === '1' ? true : false),
        );

        $related_product = array();

        $sql = "SELECT * FROM products WHERE subcategory_id = '$subcategory_id' AND product_id != '$product_id'";
        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) == 0){
            $sql = "SELECT * FROM products WHERE category_id = '$category_id' AND product_id != '$product_id'";
            $result = mysqli_query($conn, $sql);
        }

        if (mysqli_num_rows($result) > 0) {
            while ($product = mysqli_fetch_assoc($result)) {
                $subcategory_id = $product['subcategory_id'];

                $subcategoryQuery = "SELECT * FROM subcategories WHERE subcategory_id='$subcategory_id'";
                $subcategoryResult = mysqli_query($conn, $subcategoryQuery);
                $subcategory = mysqli_fetch_assoc($subcategoryResult);

                $post = array(
                    'id' => $product['product_id'],
                    'subcategory' => $subcategory['name'],
                    'category' => $category['name'],
                    'slug' => $product['slug'],
                    'price' => $product['price'],
                    'title' => $product['title'],
                    'description' => $product['description'],
                    'images' => json_decode($product['images']),
                    'sizes' => json_decode($subcategory['size']),
                    'available' => ($product['available'] === '1' ? true : false),
                    'createdAt' => $product['created_at']
                );
                array_push($related_product, $post);
            }
        } else {
            $related_product = null;
        }

        $response = array(
            'product' => $product_object,
            'related_products' => $related_product
        );
        
    } else {
        $response = array('error' => 'Product not found');
        echo json_encode($response);
        exit;
    }

    echo json_encode($response);
}