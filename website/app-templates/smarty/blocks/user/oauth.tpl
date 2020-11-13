{if GK_OPAUTH_ACTIVE && $socialProviders && $user->isCurrentUser()}
    <div class="panel panel-default">
        <div class="panel-heading">
            {t}OAuth Connections{/t}
        </div>
        <div class="panel-body">
            <p>
                Manage the accounts you have linked to your GeoKrety account.
            </p>
            <p>
                These connections give you an opportunity to log in to our service with only one click.
            </p>
            <div class="row">
                {foreach from=$socialProviders item=provider}
                    {if $user->isConnectedWithProvider($provider)}
                        <div class="col-sm-12">
                            <button type="button" class="btn btn-danger btn-block" title="{t provider=$provider->name}Disconnect from %1{/t}" data-toggle="modal" data-target="#modal" data-type="user-oauth-connect" data-oauth-provider-name="{strtolower($provider->name)}">
                                {fa icon="{strtolower($provider->name)}"} {t}Disconnect{/t}
                            </button>
                        </div>
                    {else}
                        <div class="col-sm-12">
                            <a class="btn btn-default btn-block" href="{'opauth_login'|alias:sprintf('strategy=%s', strtolower($provider->name))}" title="{t provider=$provider->name}Connect with %1{/t}">{fa icon="{strtolower($provider->name)}"} {t}Connect{/t}</a>
                        </div>
                    {/if}
                {/foreach}
            </div>
        </div>
    </div>
{/if}
