{include file="header.tpl"}
{if ! $loggedin}
    <a class="btn" href="index.php?linkedinlogin=true">{str tag="login"}</a>
{else}
    <p>
        {$message|safe}
    </p>
    {if $send}
        {include file="formpost.tpl"}    
    {elseif $logout}
        <p>LinkedIn <a class="submit" href="index.php?linkedinlogout=true">{str tag="logout"}</a></p>
    {else}
        {if $usr}
            {include file="profilelinkedin.tpl"}
        {/if}
    {/if}
{/if}
{include file="footer.tpl"}