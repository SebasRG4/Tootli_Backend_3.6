<?php

use App\Models\Review;

$reviews = Review::whereNotNull('attachment')->get();
$count = 0;

foreach ($reviews as $review) {
    // Attachment is automatically cast to array by model
    $attachments = $review->attachment;

    // Handle case where cast might fail or it's still string (though model says array)
    if (is_string($attachments)) {
        $attachments = json_decode($attachments, true);
    }

    if (is_array($attachments) && !empty($attachments)) {
        $newAttachments = [];
        $changed = false;

        foreach ($attachments as $img) {
            // Check if it starts with 'review/'
            if (strpos($img, 'review/') === 0) {
                // Strip 'review/'
                $newImg = substr($img, 7); // 'review/' length is 7
                $newAttachments[] = $newImg;
                $changed = true;
                echo "Fixing ID {$review->id}: $img -> $newImg\n";
            } else {
                $newAttachments[] = $img;
            }
        }

        if ($changed) {
            $review->attachment = $newAttachments;
            $review->save();
            $count++;
        }
    }
}

echo "Fixed $count reviews.\n";
