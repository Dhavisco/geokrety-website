<?php

namespace GeoKrety\Controller;

use Event;
use Flash;
use GeoKrety\Service\Smarty;
use MoveCommentLoader;

class MoveCommentDelete extends Base {
    use MoveCommentLoader;

    public function get(\Base $f3) {
        Smarty::render('dialog/move_comment_delete.tpl');
    }

    public function post(\Base $f3) {
        $comment = $this->comment;
        $gkid = $comment->geokret->gkid;

        if ($comment->valid()) {
            $comment->erase();
            Event::instance()->emit('move-comment.deleted', $comment);
            Flash::instance()->addMessage(_('Comment removed.'), 'success');
        } else {
            Flash::instance()->addMessage(_('Failed to delete comment.'), 'danger');
        }
        // TODO redirect to the right page/move/anchor…
        $f3->reroute("@geokret_details(@gkid=$gkid)");
    }
}
