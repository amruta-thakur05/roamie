<?php
include 'includes/config.php';
$category = 'stay';
$location = 'rajasthan';

$query = "SELECT * FROM listings WHERE status = 'active'";
$params = [];
$types = "";

if (!empty($category)) {
    $query .= " AND (category = ? OR service_type = ?)";
    $params[] = $category;
    $params[] = $category;
    $types .= "ss";
}

if (!empty($location)) {
    $query .= " AND location LIKE ?";
    $params[] = "%$location%";
    $types .= "s";
}

echo "Query: $query\n";
echo "Params: " . implode(', ', $params) . "\n";
echo "Types: $types\n";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo "Count: " . count($results) . "\n";
foreach($results as $r) {
    echo $r['id'] . " - " . $r['title'] . " - " . $r['category'] . " - " . $r['location'] . "\n";
}
