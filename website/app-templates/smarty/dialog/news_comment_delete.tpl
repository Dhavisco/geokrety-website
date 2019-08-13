{block name=content}
<div class="modal-header alert-danger">
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
    <h4 class="modal-title" id="modalLabel">{t}Do you really want to delete this news comment?{/t}</h4>
</div>

<div class="modal-body">
    <form name="comment" action="{'news_comment_delete'|alias:sprintf('newscommentid=%d', $comment->id)}" method="post">
        {include file='elements/news_comment.tpl' hide_actions=true}

        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{t}Dismiss{/t}</button>
            <button type="submit" class="btn btn-danger">{t}Delete{/t}</button>
        </div>
    </form>
</div>
{/block}
