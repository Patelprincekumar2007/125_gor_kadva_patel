<?php
$pageTitle = 'Messages';
require_once 'includes/header.php';
requireLogin();
$user = getCurrentUser($pdo);
$chatWith = isset($_GET['user']) ? (int)$_GET['user'] : 0;

// Get chat contacts (accepted interests only)
$contacts = $pdo->prepare("SELECT u.id, u.full_name, u.profile_photo, u.last_active,
    (SELECT message FROM messages WHERE (sender_id=u.id AND receiver_id=?) OR (sender_id=? AND receiver_id=u.id) ORDER BY created_at DESC LIMIT 1) as last_msg,
    (SELECT created_at FROM messages WHERE (sender_id=u.id AND receiver_id=?) OR (sender_id=? AND receiver_id=u.id) ORDER BY created_at DESC LIMIT 1) as last_msg_time,
    (SELECT COUNT(*) FROM messages WHERE sender_id=u.id AND receiver_id=? AND seen_status=0) as unread
    FROM users u WHERE u.id IN (
        SELECT CASE WHEN sender_id=? THEN receiver_id ELSE sender_id END FROM interests WHERE (sender_id=? OR receiver_id=?) AND status='accepted'
    ) ORDER BY last_msg_time DESC");
$contacts->execute([$user['id'],$user['id'],$user['id'],$user['id'],$user['id'],$user['id'],$user['id'],$user['id']]);
$chatContacts = $contacts->fetchAll();

// Get chat partner info
$partner = null;
if ($chatWith) {
    $pStmt = $pdo->prepare("SELECT id,full_name,profile_photo,last_active FROM users WHERE id=?");
    $pStmt->execute([$chatWith]); $partner = $pStmt->fetch();
    // Mark messages as seen
    $pdo->prepare("UPDATE messages SET seen_status=1 WHERE sender_id=? AND receiver_id=? AND seen_status=0")->execute([$chatWith,$user['id']]);
}
?>

<section class="py-4" style="background:var(--cream);min-height:85vh">
<div class="container">
    <div class="chat-container" style="height:calc(100vh - 140px)">
        <!-- Sidebar -->
        <div class="chat-sidebar" id="chatSidebar">
            <div style="padding:16px 18px;border-bottom:1px solid #eee">
                <h5 class="mb-0" style="font-family:'Playfair Display',serif"><i class="fas fa-comment me-2" style="color:var(--pink)"></i>Chats</h5>
            </div>
            <?php if (empty($chatContacts)): ?>
            <div class="empty-state p-4"><i class="fas fa-comment-slash d-block" style="font-size:2rem;color:var(--pink-soft)"></i><small>No chats yet.<br>Accept an interest to start chatting!</small></div>
            <?php else: foreach ($chatContacts as $c): ?>
            <a href="?user=<?php echo $c['id']; ?>" class="chat-item d-flex align-items-center gap-3 <?php echo $chatWith==$c['id']?'active':''; ?>" style="text-decoration:none;color:inherit">
                <div class="position-relative">
                    <img src="<?php echo getProfilePhoto($c['profile_photo']); ?>" style="width:44px;height:44px;border-radius:50%;object-fit:cover">
                    <?php if (isUserOnline($c['last_active'])): ?><span class="online-dot position-absolute" style="bottom:0;right:0"></span><?php endif; ?>
                </div>
                <div class="flex-grow-1 overflow-hidden">
                    <div class="d-flex justify-content-between"><strong style="font-size:0.9rem"><?php echo sanitize($c['full_name']); ?></strong><?php if($c['last_msg_time']): ?><small class="text-muted"><?php echo date('h:i A',strtotime($c['last_msg_time'])); ?></small><?php endif; ?></div>
                    <small class="text-muted text-truncate d-block"><?php echo sanitize(substr($c['last_msg']??'Start chatting...',0,40)); ?></small>
                </div>
                <?php if ($c['unread'] > 0): ?><span class="notif-badge" style="position:static"><?php echo $c['unread']; ?></span><?php endif; ?>
            </a>
            <?php endforeach; endif; ?>
        </div>

        <!-- Chat Main -->
        <div class="chat-main" id="chatMain">
            <?php if ($partner): ?>
            <!-- Chat Header -->
            <div style="padding:12px 18px;border-bottom:1px solid #eee;display:flex;align-items:center;gap:12px;background:var(--white)">
                <button class="btn btn-sm d-lg-none" onclick="document.getElementById('chatSidebar').style.display='block';document.getElementById('chatMain').style.display='none'" aria-label="Back to contacts"><i class="fas fa-arrow-left"></i></button>
                <img src="<?php echo getProfilePhoto($partner['profile_photo']); ?>" style="width:40px;height:40px;border-radius:50%;object-fit:cover">
                <div>
                    <strong><?php echo sanitize($partner['full_name']); ?></strong><br>
                    <?php if (isUserOnline($partner['last_active'])): ?>
                    <small class="text-success"><span class="online-dot me-1"></span>Online</small>
                    <?php else: ?>
                    <small class="text-muted">Last seen <?php echo timeAgo($partner['last_active']); ?></small>
                    <?php endif; ?>
                </div>
                <a href="profile.php?id=<?php echo $partner['id']; ?>" class="ms-auto btn btn-outline-pink btn-sm">View Profile</a>
            </div>
            <!-- Messages -->
            <div class="chat-messages" id="chatMessages"></div>
            <!-- Typing Indicator -->
            <div id="typingIndicator" style="display:none;padding:5px 18px"><div class="typing-indicator"><span></span><span></span><span></span></div></div>
            <!-- Input -->
            <div class="chat-input">
                <input type="text" id="msgInput" placeholder="Type a message..." autocomplete="off" onkeypress="if(event.key==='Enter')sendMessage()">
                <button onclick="sendMessage()"><i class="fas fa-paper-plane"></i></button>
            </div>
            <?php else: ?>
            <div class="d-flex align-items-center justify-content-center h-100">
                <div class="empty-state"><i class="fas fa-comments d-block mb-3" style="font-size:4rem;color:var(--pink-soft)"></i><h5>Select a conversation</h5><p class="text-muted">Choose a contact from the left to start chatting</p></div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</section>

<?php if ($partner): ?>
<script>
const userId = <?php echo $user['id']; ?>;
const partnerId = <?php echo $partner['id']; ?>;
const csrfToken = '<?php echo generateCSRFToken(); ?>';
let lastMsgId = 0;

function loadMessages() {
    fetch('api/get-messages.php?partner_id='+partnerId+'&last_id='+lastMsgId)
    .then(r=>r.json()).then(data=>{
        if(data.messages && data.messages.length > 0) {
            const container = document.getElementById('chatMessages');
            data.messages.forEach(m => {
                if(m.id > lastMsgId) {
                    const div = document.createElement('div');
                    div.className = 'msg-bubble ' + (m.sender_id == userId ? 'msg-sent' : 'msg-received');
                    // message is already HTML-escaped by server
                    div.innerHTML = m.message + '<div class="msg-time">' + m.time + (m.sender_id == userId ? (m.seen_status == 1 ? ' <i class="fas fa-check-double"></i>' : ' <i class="fas fa-check"></i>') : '') + '</div>';
                    container.appendChild(div);
                    lastMsgId = m.id;
                }
            });
            container.scrollTop = container.scrollHeight;
        }
    });
}

function sendMessage() {
    const input = document.getElementById('msgInput');
    const msg = input.value.trim();
    if(!msg) return;
    input.value = '';
    fetch('api/send-message.php', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:'receiver_id='+partnerId+'&message='+encodeURIComponent(msg)+'&csrf_token='+csrfToken})
    .then(r=>r.json()).then(d=>{ if(d.success) loadMessages(); else showToast(d.error,'error'); });
}

loadMessages();
setInterval(loadMessages, 3000);

// Mobile: show chat panel, hide sidebar when conversation is open
if(window.innerWidth < 992) {
    document.getElementById('chatSidebar').style.display = 'none';
    document.getElementById('chatMain').style.display = 'flex';
}
</script>
<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>
