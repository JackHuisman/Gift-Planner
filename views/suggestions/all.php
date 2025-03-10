<?php
/**
 * View All Suggestions (Across All Occasions)
 */
?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php?page=dashboard">Dashboard</a></li>
        <li class="breadcrumb-item active" aria-current="page">All Suggestions</li>
    </ol>
</nav>

<div class="row mb-4">
    <div class="col-md-8">
        <h1 class="h3 mb-0">All Gift Suggestions</h1>
    </div>
    <div class="col-md-4 text-md-end">
        <a href="index.php?page=dashboard" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white">
        <h5 class="card-title mb-0">All Suggestions (Across All Occasions)</h5>
    </div>
    <div class="card-body p-0">
        <?php if (empty($suggestions)): ?>
            <div class="p-4 text-center">
                <p class="text-muted mb-3">No suggestions available yet.</p>
                <a href="index.php?page=dashboard" class="btn btn-outline-primary">
                    <i class="fas fa-arrow-left me-1"></i> Return to Dashboard
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Price</th>
                            <th>Occasions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($suggestions as $suggestion): ?>
                            <tr>
                                <!--<td>-->
<td>
    <div class="d-flex align-items-center">
        <?php if (!empty($suggestion['image_url'])): ?>
        <div class="suggestion-thumbnail me-3">
    <?php if (!empty($suggestion['image_url'])): ?>
        <img src="<?= h($suggestion['image_url']) ?>" 
             alt="" 
             class="img-thumbnail" 
             style="width: 50px; height: 50px; object-fit: cover;"
             onerror="this.onerror=null; this.src='images/placeholder.jpg'; this.alt='';">
    <?php else: ?>
        <!-- Fallback for when image_url is empty -->
        <div class="bg-light text-center" style="width: 50px; height: 50px;">
            <i class="fas fa-gift fa-lg mt-2"></i>
        </div>
    <?php endif; ?>
</div>
        <?php endif; ?>
        <div>
            <h6 class="mb-0"><?= h($suggestion['product_title']) ?></h6>
            <?php if (!empty($suggestion['product_description'])): ?>
                <small class="text-muted"><?= h(substr($suggestion['product_description'], 0, 50)) ?>...</small>
            <?php endif; ?>
        </div>
    </div>
</td>
                                </td>
<td>
    <?php if (!empty($suggestion['price'])): ?>
        <?php
        // Handle possible different price formats
        if ($suggestion['price'] > 1000) { // Likely in cents
            echo formatCurrency($suggestion['price'] / 100);
        } else {
            echo formatCurrency($suggestion['price']);
        }
        ?>
    <?php else: ?>
        <span class="text-muted">—</span>
    <?php endif; ?>
</td>
                                <td>
                                    <?php if (!empty($suggestion['occasions'])): ?>
                                        <?php foreach (explode(',', $suggestion['occasions']) as $occasion): ?>
                                            <span class="badge bg-info rounded-pill me-1"><?= trim(h($occasion)) ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
<td>
    <a href="index.php?page=suggestions&action=view&id=<?= $suggestion['suggestion_id'] ?>" 
       class="btn btn-sm btn-outline-primary me-1" title="View Details">
        <i class="fas fa-eye"></i>
    </a>
    <?php if (!empty($suggestion['amazon_url'])): ?>
        <a href="<?= h($suggestion['amazon_url']) ?>" class="btn btn-sm btn-outline-success" 
           target="_blank" title="View Product">
            <i class="fas fa-shopping-cart"></i>
        </a>
    <?php endif; ?>
</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>