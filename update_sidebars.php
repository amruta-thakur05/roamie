<?php
$files = glob("*.php");
$count = 0;
foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Look for the standard messages link
    $search = '<i class="fas fa-comment-dots" style="position:relative;"><?php if(isset($unread_msg_count) && $unread_msg_count > 0) echo '<span style="position:absolute; top:-2px; right:-2px; width:8px; height:8px; background:#ef4444; border-radius:50%; box-shadow: 0 0 0 2px #0a223d;"></span>'; ?></i> Messages';
    $replace = '<i class="fas fa-comment-dots" style="position:relative;"><?php if(isset($unread_msg_count) && $unread_msg_count > 0) echo \'<span style="position:absolute; top:-2px; right:-2px; width:8px; height:8px; background:#ef4444; border-radius:50%; box-shadow: 0 0 0 2px #0a223d;"></span>\'; ?></i> Messages';
    
    if (strpos($content, $search) !== false) {
        $content = str_replace($search, $replace, $content);
        file_put_contents($file, $content);
        $count++;
        echo "Updated $file\n";
    }
}
echo "Total files updated: $count\n";
?>
