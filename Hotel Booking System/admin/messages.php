<?php
/**
 * Royal Stay - Admin Inquiries & Messages Management (admin/messages.php)
 */

$adminPageTitle = 'Concierge Inquiries';
$activeAdminNav = 'messages';

require_once __DIR__ . '/includes/admin-header.php';

$pdo = get_db();
$statusFilter = sanitize($_GET['status'] ?? '');

$sql = "SELECT * FROM contact_messages WHERE 1=1";
$params = [];

if (!empty($statusFilter)) {
    $sql .= " AND status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$messages = $stmt->fetchAll();

$unreadCnt = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'unread'")->fetchColumn();
$readCnt = (int)$pdo->query("SELECT COUNT(*) FROM contact_messages WHERE status = 'read'")->fetchColumn();
$totalCnt = $unreadCnt + $readCnt;
?>

<div class="admin-card">
    <div class="admin-card-header">
        <h3 class="admin-card-title">
            <i class="fa-solid fa-envelope" style="color: var(--admin-accent);"></i> Guest Inquiries & Messages (<?= count($messages) ?>)
        </h3>

        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="<?= base_url('admin/messages.php') ?>" class="btn <?= empty($statusFilter) ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                All (<?= $totalCnt ?>)
            </a>
            <a href="<?= base_url('admin/messages.php?status=unread') ?>" class="btn <?= $statusFilter === 'unread' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                Unread (<?= $unreadCnt ?>)
            </a>
            <a href="<?= base_url('admin/messages.php?status=read') ?>" class="btn <?= $statusFilter === 'read' ? 'btn-primary' : 'btn-outline' ?> btn-sm">
                Read (<?= $readCnt ?>)
            </a>
        </div>
    </div>

    <div class="admin-card-body">
        
        <div class="admin-filter-bar">
            <input type="text" class="search-bar-input data-table-search" data-target="messagesTable" placeholder="Filter by sender name, subject, or email...">
        </div>

        <div class="table-responsive">
            <table class="admin-table" id="messagesTable">
                <thead>
                    <tr>
                        <th>Sender</th>
                        <th>Subject</th>
                        <th>Message Preview</th>
                        <th>Date Received</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($messages)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 3rem; color: var(--admin-text-muted);">
                                No inquiries found in this mailbox.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <tr style="<?= $msg['status'] === 'unread' ? 'background-color: #FFFDF5; font-weight: 500;' : '' ?>">
                                <td>
                                    <div style="font-weight: 600; color: var(--admin-sidebar);"><?= e($msg['name']) ?></div>
                                    <div style="font-size: 0.75rem; color: var(--admin-text-muted);">
                                        <a href="mailto:<?= e($msg['email']) ?>" style="color: inherit; text-decoration: none;"><?= e($msg['email']) ?></a>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 600; color: var(--admin-text);"><?= e($msg['subject']) ?></div>
                                </td>
                                <td style="max-width: 320px;">
                                    <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 0.85rem; color: var(--admin-text-muted);">
                                        <?= e($msg['message']) ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem; color: var(--admin-text-muted);"><?= date('M d, Y h:i A', strtotime($msg['created_at'])) ?></div>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= e($msg['status']) ?>">
                                        <?= ucfirst(e($msg['status'])) ?>
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div class="action-btns" style="justify-content: flex-end;">
                                        <!-- View Modal -->
                                        <button type="button" class="btn-icon" title="Read Full Inquiry" onclick="openMessageModal(<?= htmlspecialchars(json_encode($msg), ENT_QUOTES, 'UTF-8') ?>)">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        <!-- Toggle Read -->
                                        <form action="<?= base_url('admin/actions/message-actions.php') ?>" method="POST" style="display: inline;">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="toggle_read">
                                            <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                            <button type="submit" class="btn-icon" title="<?= $msg['status'] === 'unread' ? 'Mark as Read' : 'Mark as Unread' ?>">
                                                <i class="fa-solid <?= $msg['status'] === 'unread' ? 'fa-envelope-open' : 'fa-envelope' ?>"></i>
                                            </button>
                                        </form>

                                        <!-- Delete -->
                                        <form action="<?= base_url('admin/actions/message-actions.php') ?>" method="POST" style="display: inline;" data-confirm="Permanently delete this inquiry?">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="message_id" value="<?= $msg['id'] ?>">
                                            <button type="submit" class="btn-icon btn-icon-error" title="Delete Inquiry">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>
</div>

<!-- Modal: Read Message -->
<div id="readMessageModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 1.5rem;">
    <div style="background: #ffffff; border-radius: 12px; max-width: 580px; width: 100%; box-shadow: 0 20px 40px rgba(0,0,0,0.25); overflow: hidden;">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--admin-border); display: flex; justify-content: space-between; align-items: center; background: #F8FAFC;">
            <h3 style="font-size: 1.15rem; font-weight: 600; color: var(--admin-sidebar); margin: 0;">
                <i class="fa-solid fa-envelope-open-text" style="color: var(--admin-accent);"></i> Inquiry Details
            </h3>
            <button type="button" onclick="closeModal('readMessageModal')" style="background: none; border: none; font-size: 1.5rem; color: var(--admin-text-muted); cursor: pointer;">&times;</button>
        </div>

        <div style="padding: 2rem;">
            <div style="margin-bottom: 1.25rem; border-bottom: 1px solid var(--admin-border); padding-bottom: 1rem;">
                <div style="font-size: 1.2rem; font-weight: 700; color: var(--admin-sidebar);" id="modalMsgSubject"></div>
                <div style="color: var(--admin-text-muted); font-size: 0.85rem; margin-top: 0.35rem;">
                    From: <strong style="color: var(--admin-text);" id="modalMsgSender"></strong> &lt;<span id="modalMsgEmail"></span>&gt;
                    &bull; <span id="modalMsgDate"></span>
                </div>
            </div>

            <div style="background: var(--admin-bg); padding: 1.25rem; border-radius: 8px; font-size: 0.95rem; line-height: 1.7; color: var(--admin-text); white-space: pre-wrap; min-height: 120px;" id="modalMsgBody"></div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem;">
                <a href="#" id="modalReplyBtn" class="btn btn-gold btn-sm">
                    <i class="fa-solid fa-reply"></i> Reply via Email
                </a>
                <button type="button" class="btn btn-outline btn-sm" onclick="closeModal('readMessageModal')">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function openMessageModal(msg) {
    document.getElementById('modalMsgSubject').textContent = msg.subject;
    document.getElementById('modalMsgSender').textContent = msg.name;
    document.getElementById('modalMsgEmail').textContent = msg.email;
    document.getElementById('modalMsgDate').textContent = msg.created_at;
    document.getElementById('modalMsgBody').textContent = msg.message;
    document.getElementById('modalReplyBtn').href = 'mailto:' + encodeURIComponent(msg.email) + '?subject=' + encodeURIComponent('Re: ' + msg.subject);

    openModal('readMessageModal');
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
