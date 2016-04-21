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

<p><strong>Warning: Uploading files to Figshare is intended for files which you wish to be published alongside your article. For all other files, you should use the supplementary files function.</strong></p>
<p>You can upload files directly to Figshare from here. The files will be marked as draft until such time as the article is published, at which point the Editor can choose to publish these data files alongside the article. This allows for:
<ul>
	<li>A Digital Object Identifier for the file</li>
	<li>The file to be cited</li>
	<li>Permanent storage of the file</li>
	</ul>
</p>

<h4>Current Files</h4>
<table class="listing" width="100%">
<tbody><tr>
	<td colspan="8" class="headseparator">&nbsp;</td>
</tr>
<tr class="heading" valign="bottom">
	<td width="5%">ID</td>
	<td width="25%">Title</td>
	<td width="30%">Description</td>
	<td width="10%">DOI</td>
	<td width="10%">Type</td>
	<td class="nowrap" width="30%">Original File Name</td>
	<td width="15%" align="right">Uploaded</td>
	<td></td>
</tr>
<tr>
	<td colspan="8" class="headseparator">&nbsp;</td>
</tr>
{foreach from=$figshare_files item=file}
<tr>
	<td>{$file.id}</td>
	<td>{$file.title}</td>
	<td>{$file.description}</td>
	<td>{$file.doi}</td>
	<td>{$file.type|capitalize}</td>
	<td>{$file.original_file_name|truncate:30}</td>
	<td>{$file.date_uploaded}</td>
	<td><a href="?remove_file={$file.id}&ojs_file={$file.file_id}">Remove</a></td>
{/foreach}
</tbody></table>
<form method="POST" enctype="multipart/form-data">
<h4>Add a New File</h4>
<div class="separator"></div>
<p><strong>Title</strong>:<br /><input type="text" name="title"></p>
<p><strong>Type</strong>:<br /><select name="type" style="width: 235px;"><option value="code">Code</option><option value="dataset">Data Set</option><option value="figure">Figure</option><option value="paper">Paper</option></select></p>
<p><strong>Description</strong>:<br /><textarea name="description" cols="38"></textarea>
<input name="articleId" value="{$article->getId()}" type="hidden">
<p><strong>Tags</strong>:<br /><input type="text" name="tags"><br /><small>Seperate with commas.</small></p>
<p><strong>Upload File</strong>:<br />
<input name="uploadFigFile" id="uploadFigFile" class="uploadField" type="file"> </p>

<input name="submitUploadSuppFile" class="button" value="Submit &amp; Upload" type="submit">
</form>

<div class="separator"></div>

{include file="common/footer.tpl"}