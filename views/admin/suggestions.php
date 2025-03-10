<?php
/**
 * Admin - Manage Predefined Gift Suggestions
 */

// Security check - only admins should access this
if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('Location: index.php?page=dashboard');
    exit;
}

$pageTitle = 'Manage Predefined Gift Suggestions';

// Initialize predefined suggestion class
$predefinedSuggestion = new PredefinedGiftSuggestion($db);

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $suggestionData = [
            'product_title' => $_POST['product_title'] ?? '',
            'product_description' => $_POST['product_description'] ?? '',
            'amazon_asin' => $_POST['amazon_asin'] ?? '',
            'price' => (float)($_POST['price'] ?? 0),
            'image_url' => $_POST['image_url'] ?? '',
            'category' => $_POST['category'] ?? '',
            'occasion_type' => $_POST['occasion_type'] ?? '',
            'age_min' => !empty($_POST['age_min']) ? (int)$_POST['age_min'] : null,
            'age_max' => !empty($_POST['age_max']) ? (int)$_POST['age_max'] : null,
            'gender' => $_POST['gender'] ?? null,
            'relationship' => $_POST['relationship'] ?? null,
            'is_featured' => isset($_POST['is_featured']) ? (bool)$_POST['is_featured'] : false
        ];
        
        if ($predefinedSuggestion->add($suggestionData)) {
            header('Location: index.php?page=admin&section=suggestions&added=1');
            exit;
        } else {
            $errors[] = "Failed to add gift suggestion. Please try again.";
        }
    }
}

// Get all predefined suggestions for listing
$searchQuery = $_GET['search'] ?? '';
$occasionFilter = $_GET['occasion'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$perPage = 20;

// Get filtered suggestions
// This would need a method in the PredefinedGiftSuggestion class to handle pagination and filtering
// For simplicity, we'll assume such a method exists
$suggestions = $predefinedSuggestion->getFiltered($searchQuery, $occasionFilter, $categoryFilter, $page, $perPage);
$totalSuggestions = $predefinedSuggestion->countFiltered($searchQuery, $occasionFilter, $categoryFilter);

$totalPages = ceil($totalSuggestions / $perPage);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">Manage Predefined Gift Suggestions</h1>
        </div>
        <div class="col-md-4 text-md-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addSuggestionModal">
                <i class="fas fa-plus me-1"></i> Add New Suggestion
            </button>
        </div>
    </div>
    
    <?php if (isset($_GET['added']) && $_GET['added'] == 1): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Gift suggestion added successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white">
            <h5 class="card-title mb-0">Filter Suggestions</h5>
        </div>
        <div class="card-body">
            <form action="index.php" method="get" class="row g-3">
                <input type="hidden" name="page" value="admin">
                <input type="hidden" name="section" value="suggestions">
                
                <div class="col-md-4">
                    <label for="search" class="form-label">Search</label>
                    <input type="text" class="form-control" id="search" name="search" value="<?= h($searchQuery) ?>" placeholder="Search by title, description...">
                </div>
                
                <div class="col-md-3">
                    <label for="occasion" class="form-label">Occasion</label>
                    <select class="form-select" id="occasion" name="occasion">
                        <option value="">All Occasions</option>
                        <option value="Birthday" <?= $occasionFilter === 'Birthday' ? 'selected' : '' ?>>Birthday</option>
                        <option value="Anniversary" <?= $occasionFilter === 'Anniversary' ? 'selected' : '' ?>>Anniversary</option>
                        <option value="Christmas" <?= $occasionFilter === 'Christmas' ? 'selected' : '' ?>>Christmas</option>
                        <option value="Valentine's Day" <?= $occasionFilter === "Valentine's Day" ? 'selected' : '' ?>>Valentine's Day</option>
                        <option value="Wedding" <?= $occasionFilter === 'Wedding' ? 'selected' : '' ?>>Wedding</option>
                        <option value="Graduation" <?= $occasionFilter === 'Graduation' ? 'selected' : '' ?>>Graduation</option>
                        <option value="Other" <?= $occasionFilter === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label for="category" class="form-label">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        <option value="Electronics" <?= $categoryFilter === 'Electronics' ? 'selected' : '' ?>>Electronics</option>
                        <option value="Home & Kitchen" <?= $categoryFilter === 'Home & Kitchen' ? 'selected' : '' ?>>Home & Kitchen</option>
                        <option value="Jewelry" <?= $categoryFilter === 'Jewelry' ? 'selected' : '' ?>>Jewelry</option>
                        <option value="Books" <?= $categoryFilter === 'Books' ? 'selected' : '' ?>>Books</option>
                        <option value="Fashion" <?= $categoryFilter === 'Fashion' ? 'selected' : '' ?>>Fashion</option>
                        <option value="Beauty" <?= $categoryFilter === 'Beauty' ? 'selected' : '' ?>>Beauty</option>
                        <option value="Toys & Games" <?= $categoryFilter === 'Toys & Games' ? 'selected' : '' ?>>Toys & Games</option>
                        <option value="Other" <?= $categoryFilter === 'Other' ? 'selected' : '' ?>>Other</option>
                    </select>
                </div>
                
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Gift Suggestions</h5>
                <span class="text-muted">Total: <?= $totalSuggestions ?> suggestions</span>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($suggestions)): ?>
                <div class="text-center py-5">
                    <p class="text-muted mb-0">No gift suggestions found matching your criteria.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Image</th>
                                <th>Title</th>
                                <th>ASIN</th>
                                <th>Price</th>
                                <th>Occasion</th>
                                <th>Category</th>
                                <th>Featured</th>
                                <th>Popularity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suggestions as $suggestion): ?>
                                <tr>
                                    <td><?= $suggestion['suggestion_id'] ?></td>
                                    <td>
                                        <?php if (!empty($suggestion['image_url'])): ?>
                                            <img src="<?= h($suggestion['image_url']) ?>" alt="Product Image" style="width: 50px; height: 50px; object-fit: contain;">
                                        <?php else: ?>
                                            <div class="bg-light text-center" style="width: 50px; height: 50px; line-height: 50px;">
                                                <i class="fas fa-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= h($suggestion['product_title']) ?></td>
                                    <td><?= h($suggestion['amazon_asin']) ?></td>
                                    <td><?= formatCurrency($suggestion['price']) ?></td>
                                    <td><?= h($suggestion['occasion_type']) ?></td>
                                    <td><?= h($suggestion['category']) ?></td>
                                    <td>
                                        <?php if ($suggestion['is_featured']): ?>
                                            <span class="badge bg-success"><i class="fas fa-check"></i></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><i class="fas fa-times"></i></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $suggestion['popularity'] ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="https://www.amazon.com/dp/<?= h($suggestion['amazon_asin']) ?>" target="_blank" class="btn btn-outline-primary" title="View on Amazon">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                            <a href="index.php?page=admin&section=suggestions&action=edit&id=<?= $suggestion['suggestion_id'] ?>" class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" title="Delete" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteSuggestionModal" 
                                                    data-id="<?= $suggestion['suggestion_id'] ?>"
                                                    data-title="<?= h($suggestion['product_title']) ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="p-3">
                        <nav aria-label="Gift suggestion pagination">
                            <ul class="pagination justify-content-center mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=admin&section=suggestions&p=<?= $page - 1 ?>&search=<?= urlencode($searchQuery) ?>&occasion=<?= urlencode($occasionFilter) ?>&category=<?= urlencode($categoryFilter) ?>">
                                            <i class="fas fa-angle-left"></i>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="fas fa-angle-left"></i></span>
                                    </li>
                                <?php endif; ?>
                                
                                <?php
                                $startPage = max(1, $page - 2);
                                $endPage = min($totalPages, $page + 2);
                                
                                for ($i = $startPage; $i <= $endPage; $i++):
                                ?>
                                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                                        <a class="page-link" href="index.php?page=admin&section=suggestions&p=<?= $i ?>&search=<?= urlencode($searchQuery) ?>&occasion=<?= urlencode($occasionFilter) ?>&category=<?= urlencode($categoryFilter) ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $totalPages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="index.php?page=admin&section=suggestions&p=<?= $page + 1 ?>&search=<?= urlencode($searchQuery) ?>&occasion=<?= urlencode($occasionFilter) ?>&category=<?= urlencode($categoryFilter) ?>">
                                            <i class="fas fa-angle-right"></i>
                                        </a>
                                    </li>
                                <?php else: ?>
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="fas fa-angle-right"></i></span>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Add Suggestion Modal -->
<div class="modal fade" id="addSuggestionModal" tabindex="-1" aria-labelledby="addSuggestionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="index.php?page=admin&section=suggestions&action=add" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSuggestionModalLabel">Add New Gift Suggestion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="product_title" class="form-label">Product Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="product_title" name="product_title" required>
                        </div>
                        <div class="col-md-4">
                            <label for="amazon_asin" class="form-label">Amazon ASIN <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="amazon_asin" name="amazon_asin" required pattern="[A-Z0-9]{10}" title="ASIN must be 10 characters (letters and numbers)">
                            <div class="form-text">10-character Amazon product ID</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="product_description" class="form-label">Product Description</label>
                        <textarea class="form-control" id="product_description" name="product_description" rows="3"></textarea>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="price" class="form-label">Price <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <label for="image_url" class="form-label">Image URL</label>
                            <input type="url" class="form-control" id="image_url" name="image_url" placeholder="https://...">
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="category" class="form-label">Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Select Category...</option>
                                <option value="Electronics">Electronics</option>
                                <option value="Home & Kitchen">Home & Kitchen</option>
                                <option value="Jewelry">Jewelry</option>
                                <option value="Books">Books</option>
                                <option value="Fashion">Fashion</option>
                                <option value="Beauty">Beauty</option>
                                <option value="Toys & Games">Toys & Games</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="occasion_type" class="form-label">Occasion <span class="text-danger">*</span></label>
                            <select class="form-select" id="occasion_type" name="occasion_type" required>
                                <option value="">Select Occasion...</option>
                                <option value="Birthday">Birthday</option>
                                <option value="Anniversary">Anniversary</option>
                                <option value="Christmas">Christmas</option>
                                <option value="Valentine's Day">Valentine's Day</option>
                                <option value="Wedding">Wedding</option>
                                <option value="Graduation">Graduation</option>
                                <option value="Mother's Day">Mother's Day</option>
                                <option value="Father's Day">Father's Day</option>
                                <!--<option value="Other"<option value="Father's Day">Father's Day</option>-->
                                <option value="Baby Shower">Baby Shower</option>
                                <option value="Housewarming">Housewarming</option>
                                <option value="Retirement">Retirement</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Any</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="age_min" class="form-label">Minimum Age</label>
                            <input type="number" class="form-control" id="age_min" name="age_min" min="0" max="120">
                        </div>
                        <div class="col-md-4">
                            <label for="age_max" class="form-label">Maximum Age</label>
                            <input type="number" class="form-control" id="age_max" name="age_max" min="0" max="120">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="relationship" class="form-label">Relationship</label>
                        <select class="form-select" id="relationship" name="relationship">
                            <option value="">Any</option>
                            <option value="Family">Family</option>
                            <option value="Friend">Friend</option>
                            <option value="Colleague">Colleague</option>
                            <option value="Partner">Partner</option>
                            <option value="Spouse">Spouse</option>
                            <option value="Child">Child</option>
                            <option value="Parent">Parent</option>
                            <option value="Sibling">Sibling</option>
                        </select>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1">
                        <label class="form-check-label" for="is_featured">Featured gift (shown on dashboard)</label>
                    </div>
                    
                    <div class="alert alert-info">
                        <p class="mb-0"><strong>Tip:</strong> Use an <a href="https://www.amazon.com/dp/B07PXGQC1Q" target="_blank">Amazon product page</a> to get the ASIN and other details. The ASIN is the 10-character ID in the URL (e.g., B07PXGQC1Q).</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Suggestion</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Suggestion Modal -->
<div class="modal fade" id="deleteSuggestionModal" tabindex="-1" aria-labelledby="deleteSuggestionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteSuggestionModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete "<span id="deleteItemTitle"></span>"?</p>
                <p class="text-danger"><small>This action cannot be undone.</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteSuggestionForm" action="index.php?page=admin&section=suggestions&action=delete" method="post">
                    <input type="hidden" id="deleteItemId" name="suggestion_id" value="">
                    <button type="submit" class="btn btn-danger">Delete Permanently</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle delete modal data
    var deleteSuggestionModal = document.getElementById('deleteSuggestionModal');
    if (deleteSuggestionModal) {
        deleteSuggestionModal.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var id = button.getAttribute('data-id');
            var title = button.getAttribute('data-title');
            
            document.getElementById('deleteItemId').value = id;
            document.getElementById('deleteItemTitle').textContent = title;
        });
    }
    
    // Validate age range
    var ageMinInput = document.getElementById('age_min');
    var ageMaxInput = document.getElementById('age_max');
    
    if (ageMinInput && ageMaxInput) {
        ageMaxInput.addEventListener('change', function() {
            if (ageMinInput.value !== '' && ageMaxInput.value !== '' && parseInt(ageMinInput.value) > parseInt(ageMaxInput.value)) {
                alert('Maximum age must be greater than or equal to minimum age.');
                ageMaxInput.value = ageMinInput.value;
            }
        });
        
        ageMinInput.addEventListener('change', function() {
            if (ageMinInput.value !== '' && ageMaxInput.value !== '' && parseInt(ageMinInput.value) > parseInt(ageMaxInput.value)) {
                alert('Minimum age must be less than or equal to maximum age.');
                ageMinInput.value = ageMaxInput.value;
            }
        });
    }
});
</script>