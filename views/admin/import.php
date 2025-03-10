<?php
/**
 * Admin - Batch Import Gift Suggestions
 */

// Security check - only admins should access this
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: index.php?page=dashboard');
    exit;
}

$pageTitle = 'Batch Import Gift Suggestions';

// Handle form submissions
$importResults = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'import') {
    // Check if file was uploaded
    if (isset($_FILES['import_file']) && $_FILES['import_file']['error'] === UPLOAD_ERR_OK) {
        $fileName = $_FILES['import_file']['tmp_name'];
        
        // Process CSV file
        if (($handle = fopen($fileName, 'r')) !== false) {
            // Initialize counters
            $totalRows = 0;
            $importedRows = 0;
            $skippedRows = 0;
            $errors = [];
            
            // Skip header row
            fgetcsv($handle);
            
            // Initialize predefined suggestion class
            $predefinedSuggestion = new PredefinedGiftSuggestion($db);
            
            // Process each row
            while (($data = fgetcsv($handle)) !== false) {
                $totalRows++;
                
                // Check if we have at least the required fields
                if (count($data) >= 5) {
                    $suggestionData = [
                        'product_title' => $data[0] ?? '',
                        'amazon_asin' => $data[1] ?? '',
                        'price' => (float)($data[2] ?? 0),
                        'category' => $data[3] ?? '',
                        'occasion_type' => $data[4] ?? '',
                        'product_description' => $data[5] ?? '',
                        'image_url' => $data[6] ?? '',
                        'gender' => $data[7] ?? null,
                        'age_min' => !empty($data[8]) ? (int)$data[8] : null,
                        'age_max' => !empty($data[9]) ? (int)$data[9] : null,
                        'relationship' => $data[10] ?? null,
                        'is_featured' => !empty($data[11]) && strtolower($data[11]) === 'yes'
                    ];
                    
                    // Validate required fields
                    if (empty($suggestionData['product_title']) || empty($suggestionData['amazon_asin']) || 
                        empty($suggestionData['category']) || empty($suggestionData['occasion_type'])) {
                        $skippedRows++;
                        $errors[] = "Row {$totalRows}: Missing required fields.";
                        continue;
                    }
                    
                    // Add the suggestion
                    if ($predefinedSuggestion->add($suggestionData)) {
                        $importedRows++;
                    } else {
                        $skippedRows++;
                        $errors[] = "Row {$totalRows}: Failed to import.";
                    }
                } else {
                    $skippedRows++;
                    $errors[] = "Row {$totalRows}: Insufficient columns.";
                }
            }
            
            fclose($handle);
            
            // Store import results
            $importResults = [
                'total' => $totalRows,
                'imported' => $importedRows,
                'skipped' => $skippedRows,
                'errors' => $errors
            ];
        } else {
            $errors[] = "Failed to open the uploaded file.";
        }
    } else {
        $errors[] = "No file uploaded or upload error occurred.";
    }
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Batch Import Gift Suggestions</h1>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="index.php?page=admin&section=suggestions" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Suggestions
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Import from CSV</h5>
                </div>
                <div class="card-body">
                    <?php if ($importResults): ?>
                        <div class="alert <?= $importResults['imported'] > 0 ? 'alert-success' : 'alert-warning' ?>">
                            <h5><i class="fas fa-info-circle me-2"></i> Import Results</h5>
                            <p>Total rows processed: <?= $importResults['total'] ?></p>
                            <p>Successfully imported: <?= $importResults['imported'] ?></p>
                            <p>Skipped rows: <?= $importResults['skipped'] ?></p>
                            
                            <?php if (!empty($importResults['errors'])): ?>
                                <div class="mt-3">
                                    <p><strong>Errors:</strong></p>
                                    <ul>
                                        <?php foreach ($importResults['errors'] as $error): ?>
                                            <li><?= h($error) ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <p>Upload a CSV file with gift suggestions to import them into the database.</p>
                    <p>The CSV file should have the following columns:</p>
                    
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Column</th>
                                    <th>Description</th>
                                    <th>Required</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Product Title</td>
                                    <td>The full name of the product</td>
                                    <td class="text-center">Yes</td>
                                </tr>
                                <tr>
                                    <td>Amazon ASIN</td>
                                    <td>10-character Amazon product ID</td>
                                    <td class="text-center">Yes</td>
                                </tr>
                                <tr>
                                    <td>Price</td>
                                    <td>Numeric price (e.g., 29.99)</td>
                                    <td class="text-center">Yes</td>
                                </tr>
                                <tr>
                                    <td>Category</td>
                                    <td>Product category</td>
                                    <td class="text-center">Yes</td>
                                </tr>
                                <tr>
                                    <td>Occasion Type</td>
                                    <td>Type of occasion (e.g., Birthday, Anniversary)</td>
                                    <td class="text-center">Yes</td>
                                </tr>
                                <tr>
                                    <td>Product Description</td>
                                    <td>Detailed description</td>
                                    <td class="text-center">No</td>
                                </tr>
                                <tr>
                                    <td>Image URL</td>
                                    <td>URL to product image</td>
                                    <td class="text-center">No</td>
                                </tr>
                                <tr>
                                    <td>Gender</td>
                                    <td>Target gender (Male, Female, or blank for Any)</td>
                                    <td class="text-center">No</td>
                                </tr>
                                <tr>
                                    <td>Minimum Age</td>
                                    <td>Minimum recommended age (numeric)</td>
                                    <td class="text-center">No</td>
                                </tr>
                                <tr>
                                    <td>Maximum Age</td>
                                    <td>Maximum recommended age (numeric)</td>
                                    <td class="text-center">No</td>
                                </tr>
                                <tr>
                                    <td>Relationship</td>
                                    <td>Target relationship (Friend, Family, etc.)</td>
                                    <td class="text-center">No</td>
                                </tr>
                                <tr>
                                    <td>Featured</td>
                                    <td>Whether to feature this suggestion (Yes/No)</td>
                                    <td class="text-center">No</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <form action="index.php?page=admin&section=import&action=import" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="import_file" class="form-label">Select CSV file to import</label>
                            <input class="form-control" type="file" id="import_file" name="import_file" accept=".csv" required>
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i> Import Suggestions
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Download Template</h5>
                </div>
                <div class="card-body">
                    <p>You can download a template CSV file to help you format your data correctly.</p>
                    
                    <div class="d-grid">
                        <a href="templates/gift_suggestions_template.csv" class="btn btn-outline-primary" download>
                            <i class="fas fa-download me-1"></i> Download CSV Template
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">Tips for Success</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Ensure ASINs are valid 10-character Amazon product IDs
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Double-check that prices are numeric values
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Use common categories and occasion types for better matching
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Include detailed descriptions for better gift matching
                        </li>
                        <li>
                            <i class="fas fa-check-circle text-success me-2"></i>
                            Use high-quality image URLs for a better user experience
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>