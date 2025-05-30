<?php
require_once '../logic/sql_querries.php';
require_once '../logic/db_connection.php';
session_start();
if (!isset($_SESSION['isLoggedIn'])){
    echo "<script>alert('You are not logged in!!'); window.location.href = 'index.php';</script>";
}
$student_id = $_SESSION['student_id'];

$stmt = $pdo->prepare(SQL_LIST_LOST_ITEMS_BY_STUDENT);
$stmt->execute([$student_id]);
$lost_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
$foundItems = [];
try {
    // Get all found items
    $stmt = $pdo->prepare("
        SELECT * from lost_items where student_id = ?
    ");
    $stmt->execute([$student_id]);
    $foundItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching found items: " . $e->getMessage());
    $foundItems = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lost Items</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .table-container {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border-radius: 1rem;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(135deg, #800000 0%, #a52a2a 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(128, 0, 0, 0.2);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #4a5568 0%, #2d3748 100%);
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(45, 55, 72, 0.2);
        }

        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-found {
            background-color: #dcfce7;
            color: #166534;
        }

        .item-card {
            transition: transform 0.2s ease-in-out;
        }
        .item-card:hover {
            transform: translateY(-2px);
        }
    </style>
</head>

<body class="min-h-screen">
    <?php include 'navigation.php'; ?>

    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-2xl md:text-3xl font-bold text-[#800000]">Your Lost Items</h1>
            <a href="lost-item-form.php" class="btn-primary text-white px-6 py-3 rounded-lg font-semibold">
                Report a Lost Item
            </a>
        </div>

        <div class="table-container">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Name</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Photo</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($lost_items)): ?>
                            <?php foreach ($lost_items as $item): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $item['id']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $item['item_name']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $item['category']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="status-badge status-<?php echo strtolower($item['status']); ?>">
                                            <?php echo ucfirst($item['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo $item['date']; ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if (!empty($item['photo']) && !empty($item['mime_type'])): ?>
                                            <img src="data:<?php echo $item['mime_type']; ?>;base64,<?php echo base64_encode($item['photo']); ?>" 
                                                 alt="Item Photo" 
                                                 class="w-16 h-16 object-cover rounded-lg shadow-sm" />
                                        <?php else: ?>
                                            <span class="text-sm text-gray-500">No Image</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <form action="lost-item-form.php" method="POST" class="inline">
                                            <input type="hidden" name="user" value="<?= $item['id'] ?>">
                                            <button type="submit" class="btn-secondary text-white px-4 py-2 rounded-lg text-sm">
                                                Edit
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">
                                    No lost items found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-8">
            <h2 class="text-3xl font-bold text-[#800000] mb-2">Found Items</h2>
            <p class="text-gray-600">Here are your lost items that has been found</p>
        </div>

        <?php if (empty($foundItems)): ?>
            <div class="bg-white rounded-lg shadow-lg p-8 text-center">
                <div class="bg-gray-50 rounded-full w-24 h-24 mx-auto mb-4 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No Found Items</h3>
                <p class="text-gray-500">There are currently no found items in the system.</p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($foundItems as $item): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden item-card">
                        <?php if (!empty($item['photo']) && !empty($item['mime_type'])): ?>
                            <div class="aspect-w-16 aspect-h-9 bg-gray-100">
                                <img src="data:<?php echo $item['mime_type']; ?>;base64,<?php echo base64_encode($item['photo']); ?>" 
                                     alt="<?php echo htmlspecialchars($item['item_name']); ?>" 
                                     class="w-full h-48 object-cover" />
                            </div>
                        <?php endif; ?>
                        
                        <div class="p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        <?php echo htmlspecialchars($item['item_name']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-500">
                                        Category: <?php echo htmlspecialchars($item['category']); ?>
                                    </p>
                                </div>
                                <span class="px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800">
                                    Found
                                </span>
                            </div>

                            <div class="space-y-3">
                                <div>
                                    <p class="text-sm font-medium text-gray-500">Description</p>
                                    <p class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($item['description'] ?? 'No description provided'); ?>
                                    </p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">Location Found</p>
                                    <p class="text-sm text-gray-900">
                                        <?php echo htmlspecialchars($item['location_found'] ?? 'Not specified'); ?>
                                    </p>
                                </div>

                                <div>
                                    <p class="text-sm font-medium text-gray-500">Date Found</p>
                                    <p class="text-sm text-gray-900">
                                        <?php echo date('F j, Y', strtotime($item['date'])); ?>
                                    </p>
                                </div>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function notifyStudent(itemId) {
            if (confirm('Are you sure you want to notify the student about this found item?')) {
                fetch('notify_student.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'item_id=' + itemId
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Student has been notified successfully!');
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while notifying the student.');
                });
            }
        }
    </script>
</body>

</html>