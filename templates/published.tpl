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

<div id="submissions">
<table class="listing" width="100%">
	<tr valign="bottom" class="heading">
		<td width="5%">{sort_heading key="common.id" sort="id"}</td>
		<td width="5%">Submission Date</td>
		<td width="5%">{sort_heading key="submissions.sec" sort="section"}</td>
		<td width="23%">{sort_heading key="article.authors" sort="authors"}</td>
		<td width="32%">{sort_heading key="article.title" sort="title"}</td>
		{if $statViews}<td width="5%">{sort_heading key="submission.views" sort="views"}</td>{/if}
		<td width="25%" align="right">Select</td>
	</tr>
	<tr><td class="headseparator" colspan="{if $statViews}7{else}6{/if}">&nbsp;</td></tr>
{iterate from=submissions item=submission}
	{assign var="articleId" value=$submission->getId()}
	<tr valign="top">
		<td>{$articleId|escape}</td>
		<td>{$submission->getDateSubmitted()|date_format:$dateFormatTrunc}</td>
		<td>{$submission->getSectionAbbrev()|escape}</td>
		<td>{$submission->getAuthorString(true)|truncate:40:"..."|escape}</td>
		<td><a href="{url op="submission" path=$articleId}" class="action">{$submission->getLocalizedTitle()|strip_tags|truncate:60:"..."}</a></td>
		{assign var="status" value=$submission->getSubmissionStatus()}
		{if $statViews}
			<td>
				{if $status==STATUS_PUBLISHED}
					{assign var=viewCount value=0}
					{foreach from=$submission->getGalleys() item=galley}
						{assign var=thisCount value=$galley->getViews()}
						{assign var=viewCount value=$viewCount+$thisCount}
					{/foreach}
					{$viewCount|escape}
				{else}
					&mdash;
				{/if}
			</td>
		{/if}
		<td align="right">
			<a href="?article_id={$submission->getId()}">Select for Depositing</a>
		</td>
	</tr>

	<tr>
		<td colspan="{if $statViews}7{else}6{/if}" class="{if $submissions->eof()}end{/if}separator">&nbsp;</td>
	</tr>
{/iterate}
{if $submissions->wasEmpty()}
	<tr>
		<td colspan="{if $statViews}7{else}6{/if}" class="nodata">{translate key="submissions.noSubmissions"}</td>
	</tr>
	<tr>
		<td colspan="{if $statViews}7{else}6{/if}" class="endseparator">&nbsp;</td>
	</tr>
{else}
	<tr>
		<td colspan="{if $statViews}5{else}4{/if}" align="left">{page_info iterator=$submissions}</td>
		<td colspan="2" align="right">{page_links anchor="submissions" name="submissions" iterator=$submissions sort=$sort sortDirection=$sortDirection}</td>
	</tr>
{/if}
</table>

{if $article}
	<h2>Depositing Article: {$article->getArticleTitle()}</h2>
	<p>Galleys for this article are listed below. You can select the galleys that you want to export and send back to your OSF project.</p>
	<p>In order to upload figures and data to figshare, we require a personal access token. We do not store these in or database, but use a session variable that will be deleted when you log out. You can generate a token on the <a href="https://figshare.com/account/applications" target="_blank">figshare</a> site.</p>
	<form method="POST">
		<strong>Figshare Token</strong><br />
		<input type="text" name="token" class="textField" /><br /><br />
		{foreach item=item from=$galleys}
		<input type="checkbox" value="{$item.file_id}" id="{$item.galley_id}" name="galleys[]"><label for="{$item.galley_id}">{$item.label}</label><br />
		{/foreach}
		<br />
		<div class="separator"></div>
		<br />
		<input type="submit" class="button-secondary" value="Select Galleys">
	</form>
{/if}

{include file="common/footer.tpl"}