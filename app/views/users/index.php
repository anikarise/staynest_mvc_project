<?php require APP_ROOT . '/app/views/layouts/header.php'; ?>
<section class="container section">
    <div class="section-heading">
        <div>
            <h1>User Management</h1>
            <p class="muted">Main Admin can view all registered users. Full user CRUD will be expanded in the next phase.</p>
        </div>
    </div>

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
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= (int) $user['user_id']; ?></td>
                        <td><?= htmlspecialchars($user['name'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><?= htmlspecialchars($user['phone'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                        <td><span class="badge"><?= Auth::roleLabel($user['role']); ?></span></td>
                        <td><span class="badge badge-green"><?= htmlspecialchars($user['status'], ENT_QUOTES, 'UTF-8'); ?></span></td>
                        <td><?= htmlspecialchars($user['created_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require APP_ROOT . '/app/views/layouts/footer.php'; ?>
