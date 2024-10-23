<?php

use App\Models\Comment;
use App\Models\Page;
use App\Models\Topic;
use App\Models\Video;
use App\Services\TempStorage;
use Illuminate\Database\Eloquent\Builder;
use Vesp\Services\Eloquent;

require dirname(__DIR__) . '/bootstrap.php';

$eloquent = new Eloquent();
$media = new TempStorage();
$dst = $media->getBaseFilesystem();

// Sanitize current content blocks
if (getenv('EDITOR_SANITIZATION_FORCE')) {
    try {
        /** @var Comment $comment */
        foreach (Comment::query()->cursor() as $comment) {
            $comment->content = Comment::sanitizeContent($comment->content);
            $comment->save();
        }
    } catch (\Throwable $e) {
        echo $e->getMessage() . PHP_EOL;
        echo print_r($comment->toArray(), true) . PHP_EOL;
    }

    try {
        /** @var Topic $topic */
        foreach (Topic::query()->cursor() as $topic) {
            $topic->content = Topic::sanitizeContent($topic->content);
            $topic->save();
        }
    } catch (\Throwable $e) {
        echo $e->getMessage() . PHP_EOL;
        echo print_r($topic->toArray(), true) . PHP_EOL;
    }

    try {
        /** @var Page $page */
        foreach (Page::query()->where('external', false)->cursor() as $page) {
            $page->content = Topic::sanitizeContent($page->content);
            $page->save();
        }
    } catch (\Throwable $e) {
        echo $e->getMessage() . PHP_EOL;
        echo print_r($page->toArray(), true) . PHP_EOL;
    }
}

// Add new features
if (getenv('EXTRACT_VIDEO_AUDIO_ENABLED') || getenv('EXTRACT_VIDEO_THUMBNAILS_ENABLED')) {
    $videos = Video::query()
        ->where(static function (Builder $c) {
            $c->whereNull('audio_id');
            $c->orWhereNull('thumbnail_id');
        })
        ->where('processed', true)
        ->orderByDesc('created_at');

    /** @var Video $video */
    foreach ($videos->cursor() as $video) {
        $mainFile = $video->id . '/video.mp4';
        if (!$dst->has($mainFile)) {
            $time = microtime(true);
            $path = $video->file->getFilePathAttribute();
            $src = $video->file->getFilesystem()->getBaseFilesystem();
            $size = round($src->fileSize($path) / 1024 / 1024 / 1024, 2);
            echo 'Downloading ' . $video->id . ' (' . $size . ' Gb)... ';
            $dst->writeStream($mainFile, $src->readStream($path));
            echo 'Done in ' . microtime(true) - $time . 's.' . PHP_EOL;
        }
        echo 'Processing ' . $mainFile . ' ...' . PHP_EOL;

        if (!$video->audio && getenv('EXTRACT_VIDEO_AUDIO_ENABLED')) {
            try {
                $time = microtime(true);
                echo 'Extracting audio... ';
                $audio = $media->getAudio($video->id);
                $video->audio_id = $audio->id;
                $video->save();
                echo 'Done in ' . microtime(true) - $time . ' s.' . PHP_EOL;
            } catch (\Exception $e) {
                echo $e->getMessage() . PHP_EOL;
            }
        }

        if (!$video->thumbnail && getenv('EXTRACT_VIDEO_THUMBNAILS_ENABLED')) {
            try {
                $time = microtime(true);
                echo 'Extracting thumbnails... ';
                $thumbnail = $media->getThumbnail($video->id);
                $video->thumbnail_id = $thumbnail->id;
                $video->save();
                echo 'Done in ' . microtime(true) - $time . ' s.' . PHP_EOL;
            } catch (\Exception $e) {
                echo $e->getMessage() . PHP_EOL;
            }
        }

        $dst->deleteDirectory($video->id);
        echo PHP_EOL;
    }
}

// Update video blocks
foreach (Video::query()->whereHas('topicFiles')->orWhereHas('pageFiles')->cursor() as $video) {
    /** @var Video $video */
    $video->updateContentBlocks();
}
