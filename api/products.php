<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/../config/database.php';

try {
    $db = getDBConnection();

    $featured = isset($_GET['featured']) && $_GET['featured'] === 'true';
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : null;
    $category = isset($_GET['categoria']) ? $_GET['categoria'] : null;
    $promocao = isset($_GET['promocao']) && $_GET['promocao'] === 'true';

    $query = "
        SELECT
            p.id,
            p.name,
            p.description,
            p.price,
            p.old_price,
            p.image_url,
            p.icon,
            p.badge,
            p.is_featured,
            p.is_promotion,
            p.is_new,
            p.stock_quantity,
            c.slug as category
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE 1=1
    ";

    $params = [];

    if ($featured) {
        $query .= " AND p.is_featured = true";
    }

    if ($category) {
        $query .= " AND c.slug = :category";
        $params['category'] = $category;
    }

    if ($promocao) {
        $query .= " AND p.is_promotion = true";
    }

    $query .= " ORDER BY p.created_at DESC";

    if ($limit) {
        $query .= " LIMIT :limit";
        $params['limit'] = $limit;
    }

    $stmt = $db->prepare($query);

    foreach ($params as $key => $value) {
        if ($key === 'limit') {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':' . $key, $value);
        }
    }

    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($products as &$product) {
        $product['price'] = (float)$product['price'];
        $product['old_price'] = $product['old_price'] ? (float)$product['old_price'] : null;
        $product['is_featured'] = (bool)$product['is_featured'];
        $product['is_promotion'] = (bool)$product['is_promotion'];
        $product['is_new'] = (bool)$product['is_new'];
        $product['stock_quantity'] = (int)$product['stock_quantity'];
        $product['promocao'] = (bool)$product['is_promotion'];
        $product['novidade'] = (bool)$product['is_new'];
    }

    echo json_encode($products);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch products', 'message' => $e->getMessage()]);
}
