<?php
include_once 'includes/header.php';
include_once 'includes/sidebar.php';

// Fetch all sermons
$sermons = $conn->query("SELECT s.*, a.full_name AS admin_name FROM sermons s LEFT JOIN admins a ON s.posted_by=a.admin_id ORDER BY s.posted_at DESC");
?>

<h3 class="fw-bold mb-4">Sermons</h3>

<div class="row">
<?php if($sermons->num_rows>0): ?>
    <?php while($sermon=$sermons->fetch_assoc()): 
        $video_url = $sermon['video_link'];
        // Extract YouTube video ID if YouTube link
        $video_embed = '';
        if(strpos($video_url, 'youtube.com')!==false || strpos($video_url, 'youtu.be')!==false){
            parse_str(parse_url($video_url, PHP_URL_QUERY), $yt);
            $id = $yt['v'] ?? '';
            $video_embed = $id ? "https://www.youtube.com/embed/$id" : '';
        }
    ?>
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm h-100" style="border-left: 5px solid #1cc88a;">
            <div class="card-body">
                <h5 class="card-title"><?= htmlspecialchars($sermon['title']); ?></h5>
                <p class="card-text"><?= substr(htmlspecialchars($sermon['description']),0,100); ?>...</p>
                <p><small class="text-muted">Posted by <?= htmlspecialchars($sermon['admin_name']); ?> on <?= date("M d, Y", strtotime($sermon['posted_at'])); ?></small></p>
                <?php if($video_embed): ?>
                    <div class="ratio ratio-16x9 mb-2">
                        <iframe src="<?= $video_embed ?>" title="<?= htmlspecialchars($sermon['title']); ?>" allowfullscreen></iframe>
                    </div>
                <?php elseif($sermon['video_link']): ?>
                    <a href="<?= htmlspecialchars($sermon['video_link']); ?>" target="_blank" class="btn btn-sm btn-primary">Watch Video</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
<?php else: ?>
    <p class="text-muted">No sermons uploaded yet.</p>
<?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>
