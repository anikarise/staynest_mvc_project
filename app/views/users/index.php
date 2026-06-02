<?php
/*
|--------------------------------------------------------------------------
| User Management View
|--------------------------------------------------------------------------
| Presents Main Admin user search, account approval actions, and protected
| delete controls.
|
*/
require APP_ROOT . '/app/views/layouts/header.php';
?>
<section class="container section">
    <div class="section-heading">
        <div>
            <h1>User Management</h1>
            <p class="muted">Main Admin can view users and approve or reject pending Host and Staff registration requests.</p>
        </div>
    </div>

    <form class="management-filter-panel user-filter-panel" method="get" action="<?= URL_ROOT; ?>/user">
        <div class="booking-filter-field management-filter-search">
            <label for="userSearch">Search users</label>
            <input id="userSearch" type="text" name="search" value="<?= htmlspecialchars($search ?? '', ENT_QUOTES, 'UTF-8'); ?>" placeholder="User ID, name, email, phone, role, status">
        </div>
        <div class="booking-filter-field">
            <label for="userRole">Role</label>
            <select id="userRole" name="role">
                <option value="">All roles</option>
                <?php foreach ($roleOptions as $roleOption): ?>
                    <option value="<?= htmlspecialchars($roleOption, ENT_QUOTES, 'UTF-8'); ?>" <?= ($role ?? '') === $roleOption ? 'selected' : ''; ?>>
                        <?= htmlspecialchars(Auth::roleLabel($roleOption), ENT_QUOTES, 'UTF-8'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="booking-filter-field">
            <label for="userAccountStatus">Account Status</label>
            <select id="userAccountStatus" name="account_status">
                <option value="">All statuses</option>
                <option value="active" <?= ($accountStatus ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="pending" <?= ($accountStatus ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="rejected" <?= ($accountStatus ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                <option value="inactive" <?= ($accountStatus ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <div class="booking-filter-actions">
            <button class="btn booking-search-btn" type="submit"><span class="search-icon" aria-hidden="true"></span>Search</button>
            <a class="btn btn-secondary booking-reset-btn" href="<?= URL_ROOT; ?>/user">Reset</a>
        </div>
    </form>

    <?php if (empty($users)): ?>
        <div class="card center booking-empty-state">
            <h2>No matching records found.</h2>
            <p class="muted">Try a different user, role, or account status filter.</p>
        </div>
    <?php else: ?>
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Account Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <?php $userStatusClass = ($user['status'] ?? '') === 'inactive' ? 'badge-account-inactive' : 'badge-green'; ?>
                    <tr>
                        <td><?= (int) $user['user_id']; ?></td>
                        <td><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($user['phone'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><span class="badge"><?= Auth::roleLabel($user['role']); ?></span></td>
                        <td><span class="badge <?= htmlspecialchars($userStatusClass, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                        <?php $accountStatus = $user['account_status'] ?? 'active'; ?>
                        <td><span class="badge badge-account-<?= htmlspecialchars($accountStatus, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars(ucfirst($accountStatus), ENT_QUOTES, 'UTF-8'); ?></span></td>
                        <td><?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td>
                            <?php
                                $isCurrentUser = (int) $user['user_id'] === Auth::userId();
                                $canModerate = ($user['account_status'] ?? 'active') === 'pending' && in_array($user['role'], ['host', 'staff'], true);
                                $canDelete = !$isCurrentUser;
                            ?>
                            <?php if ($canModerate || $canDelete): ?>
                                <div class="row-actions">
                                    <?php if ($canModerate): ?>
                                    <form method="post" action="<?= URL_ROOT; ?>/user/approve/<?= (int) $user['user_id']; ?>" class="inline-form">
                                        <?= Auth::csrfField(); ?>
                                        <button class="btn btn-mini" type="submit">Approve</button>
                                    </form>
                                    <form method="post" action="<?= URL_ROOT; ?>/user/reject/<?= (int) $user['user_id']; ?>" class="inline-form">
                                        <?= Auth::csrfField(); ?>
                                        <button class="btn btn-mini btn-secondary" type="submit">Reject</button>
                                    </form>
                                    <?php endif; ?>

                                    <?php if ($canDelete): ?>
                                    <form method="post" action="<?= URL_ROOT; ?>/user/delete/<?= (int) $user['user_id']; ?>" class="inline-form">
                                        <?= Auth::csrfField(); ?>
                                        <button class="btn btn-mini btn-danger" type="submit" data-confirm="Are you sure you want to delete this user?">Delete</button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <span class="muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
