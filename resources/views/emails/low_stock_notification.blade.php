<html>
<body>
    <p>Dear Admin,</p>

    <p>The stock level for the following product is low:</p>

    <ul>
        <li>Product ID: {{ $product->id }}</li>
        <li>Name: {{ $product->name }}</li>
        <li>Current Stock: {{ $product->current_stock }}</li>
    </ul>

    <p>Please restock this product as soon as possible.</p>

    <p>Best regards,<br>Your Inventory System</p>
</body>
</html>
