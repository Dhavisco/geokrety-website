{extends file='base.tpl'}

{block name=css}
<link rel="stylesheet" href="{GK_CDN_LIBRARIES_PARSLEY_CSS_URL}">
<link rel="stylesheet" href="{GK_CDN_LIBRARIES_INSCRYBMDE_CSS_URL}">
{/block}

{block name=js}
<script type="text/javascript" src="{GK_CDN_LIBRARIES_PARSLEY_BOOTSTRAP3_JS_URL}"></script>
<script type="text/javascript" src="{GK_CDN_LIBRARIES_PARSLEY_JS_URL}"></script>
<script type="text/javascript" src="{GK_CDN_LIBRARIES_INSCRYBMDE_JS_URL}"></script>
{/block}

{block name=content}
{include 'elements/news.tpl' item=$news}

<div class="panel panel-default">
    <div class="panel-heading">
        {t}Leave a comment{/t}
    </div>
    <div class="panel-body">
        {if $f3->get('SESSION.IS_LOGGED_IN')}
        <form class="form-horizontal" action="" method="post" id="formNewsComment" data-parsley-validate data-parsley-priority-enabled=false data-parsley-ui-enabled=true>

            <div class="form-group">
                <label for="comment" class="col-sm-2 control-label">{t}Comment{/t}</label>
                <div class="col-sm-10">
                    <textarea class="form-control maxl" rows="5" id="comment" name="comment" placeholder="{t}Your comment{/t}" maxlength="1000" required>{$smarty.post.comment}</textarea>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" id="subscribe" name="subscribe" {if $news->isSubscribed()} checked{/if}> {t}Subscribe to this news post{/t}
                        </label>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-10">
                    <button type="submit" class="btn btn-primary">{t}Comment{/t}</button>
                </div>
            </div>

        </form>
        {else}
        <em>{t}Please login to post a new comment{/t}</em>
        {/if}
    </div>
</div>

<h3>{t}Comments{/t}</h3>
{foreach $news->comments as $comment}
{include file='elements/news_comment.tpl'}
{foreachelse}
{t}There are no comments for this post.{/t}
{/foreach}
{/block}

{block name=javascript}
{if $f3->get('SESSION.IS_LOGGED_IN')}
    // Bind SimpleMDE editor
    var inscrybmde = new InscrybMDE({
        element: $("#comment")[0],
        hideIcons: ['side-by-side', 'fullscreen', 'quote'],
        promptURLs: true,
        spellChecker: false,
        status: false,
        forceSync: true,
       renderingConfig: {
               singleLineBreaks: false,
       },
        minHeight: '100px',
    });

    // Bind modal
    {include 'js/dialog_news_subscription.js.tpl'}
    {include 'js/dialog_news_comment_delete.js.tpl'}
{/if}
{/block}