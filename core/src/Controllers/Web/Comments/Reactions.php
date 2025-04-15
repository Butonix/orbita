<?php

namespace App\Controllers\Web\Comments;

use App\Controllers\Traits\ReactionModelController;
use App\Models\Comment;
use Psr\Http\Message\ResponseInterface;
use Vesp\Controllers\Controller;

class Reactions extends Controller
{
    use ReactionModelController;

    public function checkScope(string $method): ?ResponseInterface
    {
        if ($method === 'options') {
            return null;
        }

        /** @var Comment $comment */
        $comment = Comment::query()->find($this->getProperty('id'));
        if (!$comment || !$comment->active || $comment->topic->hide_reactions) {
            return $this->failure('Not Found', 404);
        }
        $this->model = $comment;

        return null;
    }
}