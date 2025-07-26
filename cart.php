<?php  
$host = 'localhost';  
$db   = 'ecommerce';  
$user = 'root';  
$pass = '';  
$charset = 'utf8mb4';  
 
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";  
$options = [  
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,  
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,  
    PDO::ATTR_EMULATE_PREPARES => false,  
];  
 
try {  
    $pdo = new PDO($dsn, $user, $pass, $options);  
} catch (PDOException $e) {  
    die("Database connection failed: " . $e->getMessage());  
}  
 
function addToCart($customer_id, $product_id, $quantity) {  
    global $pdo;  
    try {  
        $stmt = $pdo->prepare("SELECT * FROM prativa.ShoppingCart WHERE customer_id = ? AND 
product_id = ?");  
        $stmt->execute([$customer_id, $product_id]);  
        $cartItem = $stmt->fetch();  
 
        if ($cartItem) {  
            $stmt = $pdo->prepare("UPDATE prativa.ShoppingCart SET quantity = quantity + ? WHERE cart_id = 
?");  
            if ($stmt->execute([$quantity, $cartItem['cart_id']])) {  
                echo "Product quantity updated in cart!";  
            } else {  
                echo "Error updating cart!";  
            }  
        } else {  
            $stmt = $pdo->prepare("INSERT INTO prativa.ShoppingCart (customer_id, product_id, quantity) 
VALUES (?, ?, ?)");  
            if ($stmt->execute([$customer_id, $product_id, $quantity])) {  
                echo "Product added to cart!";  
            } else {  
                echo "Error adding to cart!";  
            }  
        }  
    } catch (PDOException $e) {  
        echo "Error: " . $e->getMessage();  
    }  
}  
 
function updateCartItem($cart_id, $quantity) {  
    global $pdo;  
    try {  
        $stmt = $pdo->prepare("UPDATE prativa.ShoppingCart SET quantity = ? WHERE cart_id = ?");  
        if ($stmt->execute([$quantity, $cart_id])) {  
            echo "Cart item updated successfully!";  
        } else {  
            echo "Error updating cart item!";  
        }  
    } catch (PDOException $e) {  
        echo "Error: " . $e->getMessage();  
    }  
}  
 
function removeFromCart($cart_id) {  
    global $pdo;  
    try {  
        $stmt = $pdo->prepare("DELETE FROM prativa.ShoppingCart WHERE cart_id = ?");  
        if ($stmt->execute([$cart_id])) {  
            echo "Item removed from cart!";  
        } else {  
            echo "Error removing item from cart!";  
        }  
    } catch (PDOException $e) {  
        echo "Error: " . $e->getMessage();  
    }  
}  
 
function listCartItems($customer_id) {  
    global $pdo;  
    try {  
        $stmt = $pdo->prepare("SELECT sc.cart_id, p.product_name, p.price, sc.quantity, (p.price * sc.quantity) 
AS total_price  
                               FROM prativa.ShoppingCart sc  
                               JOIN prativa.products p ON sc.product_id = p.product_id  
                               WHERE sc.customer_id = ?");  
        $stmt->execute([$customer_id]);  
        $cartItems = $stmt->fetchAll();  
 
        if ($cartItems) {  
            echo "<h2>Shopping Cart</h2>";  
            echo "<table border='1'>";  
            echo "<tr><th>Product Name</th><th>Product Price</th><th>Quantity</th><th>Total 
Price</th><th>Actions</th></tr>";  
            foreach ($cartItems as $item) {  
                echo "<tr>";  
                echo "<td>" . $item['product_name'] . "</td>";  
                echo "<td>" . $item['price'] . "</td>";  
                echo "<td>" . $item['quantity'] . "</td>";  
                echo "<td>" . $item['total_price'] . "</td>";  
                echo "<td>  
                        <form method='post' action='cart.php'>  
                            <input type='hidden' name='cart_id' value='" . $item['cart_id'] . "'>  
                            <input type='number' name='quantity' value='" . $item['quantity'] . "' min='1'>  
                            <button type='submit' name='update_cart'>Update</button>  
                            <button type='submit' name='remove_from_cart'>Remove</button>  
                        </form>  
                      </td>";  
                echo "</tr>";  
            }  
            echo "</table>";  
        } else {  
            echo "No items in cart!";  
        }  
    } catch (PDOException $e) {  
        echo "Error: " . $e->getMessage();  
    }  
}  
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {  
    if (isset($_POST['add_to_cart'])) {  
        $customer_id = 1;  
        $product_id = $_POST['product_id'];  
        $quantity = $_POST['quantity'];  
        addToCart($customer_id, $product_id, $quantity);  
    } elseif (isset($_POST['update_cart'])) {  
        $cart_id = $_POST['cart_id'];  
        $quantity = $_POST['quantity'];  
        updateCartItem($cart_id, $quantity);  
    } elseif (isset($_POST['remove_from_cart'])) {  
        $cart_id = $_POST['cart_id'];  
        removeFromCart($cart_id);  
    }  
}  
 
function fetchProducts() {  
    global $pdo;  
    try {  
        $stmt = $pdo->query("SELECT * FROM prativa.products");  
        return $stmt->fetchAll();  
    } catch (PDOException $e) {  
        echo "Error: " . $e->getMessage();  
        return [];  
    }  
}  
 
$products = fetchProducts();  
?>  
 
<!DOCTYPE html>  
<html>  
<head>  
    <title>Shopping Cart</title>  
    <style> 
        body { 
            font-family: Arial, sans-serif; 
        } 
        label { 
            display: inline-block; 
            width: 100px; 
            margin-bottom: 10px; 
        } 
        select, input[type="number"] { 
            width: 250px; 
            padding: 5px; 
        } 
        button { 
            margin-left: 5px; 
        } 
        form { 
            margin-bottom: 20px; 
        } 
    </style> 
</head>  
<body>  
    <h1>Add Product to Cart</h1>  
    <form method="post" action="cart.php">  
        <label for="product_id">Product:</label>  
        <select id="product_id" name="product_id" required>  
            <option value="">Select a product</option>  
            <?php foreach ($products as $product): ?>  
                <option value="<?= $product['product_id'] ?>"><?= $product['product_name'] ?></option>  
            <?php endforeach; ?>  
        </select><br>   
 
        <label for="quantity">Quantity:</label>  
<input type="number" id="quantity" name="quantity" value="1" min="1" required><br>   
<button type="submit" name="add_to_cart">Add to Cart</button>  
</form>  
<?php listCartItems(1); ?>  
</body>  
</html>