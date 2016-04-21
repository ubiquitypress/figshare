{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}

{literal}
<style>
	input[type="text"] 
	{
	 width: 230px;
	}
</style>
{/literal}

<p>In order to upload figures and data to figshare, we require a personal access token. We do not store these in or database, but use a session variable that will be deleted when you log out. You can generate a token on the <a href="https://figshare.com/account/applications" target="_blank">figshare</a> site.</p>

{if $message}
<div style="width: 100%; color: #a94442; background-color: #f2dede; padding: 5px; margin-bottom: 10px;">
	<p>{$message}</p>
</div>
{/if}

<form method="POST">
	<input type="text" name="token" class="textField" />
	<input type="submit" name="token_submit" id="token_submit" class="button" value="Submit Token"/>
</form>

{include file="common/footer.tpl"}