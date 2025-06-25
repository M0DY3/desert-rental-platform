<?php
session_start();

// Initialize basket if it doesn't exist
if (!isset($_SESSION['basket'])) {
    $_SESSION['basket'] = [];
}

function addToBasket($item_id, $price) {
    $item_exists = false;
    
    // Check if the item is already in the basket
    if (isset($_SESSION['basket'][$item_id])) {
        $_SESSION['basket'][$item_id]['quantity'] += 1;  // Increment quantity if the item is already in the basket
        $item_exists = true;
    }
    
    // If the item is not already in the basket, add it
    if (!$item_exists) {
        $_SESSION['basket'][$item_id] = [
            'item_id' => $item_id,
            'price' => $price,      // Set price
            'quantity' => 1,        // Initialize quantity to 1
            'added_at' => time(),   // Track when the item was added
        ];
    }
}


// Remove item from basket
function removeFromBasket($item_id) {
    foreach ($_SESSION['basket'] as $key => $basket_item) {
        if ($basket_item['item_id'] == $item_id) {
            unset($_SESSION['basket'][$key]);
            $_SESSION['basket'] = array_values($_SESSION['basket']); // Reindex
            break;
        }
    }
}

// Get total price of basket
function getBasketTotal() {
    $total_price = 0;
    
    // Loop through the basket and calculate the total price
    if (isset($_SESSION['basket'])) {
        foreach ($_SESSION['basket'] as $item) {
            $total_price += $item['price'] * $item['quantity']; // Price * Quantity
        }
    }

    return $total_price; // Return the total price
}


// Example: Add item to basket via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id']; // item ID to add
    $price = $_POST['price']; // item price
    addToBasket($item_id, $price);
    echo "Item added to basket!";
}
?>
