{assign var="pageTitleTranslated" value=$page_title}
{include file="common/header.tpl"}

<h3>{$article->getArticleTitle()}</h3>
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
	<td colspan="6" class="headseparator">&nbsp;</td>
</tr>
<tr class="heading" valign="bottom">
	<td width="5%">ID</td>
	<td width="30%">Title</td>
	<td width="10%">DOI</td>
	<td width="25%">Original file name</td>
	<td class="nowrap" width="15%">Date uploaded</td>
	<td width="15%" align="right">Action</td>
</tr>
<tr>
	<td colspan="6" class="headseparator">&nbsp;</td>
</tr>
<tr valign="top">
	<td colspan="6" class="nodata">No data files have been added to this submission.</td>
</tr>
</tbody></table>

<h4>Add a New File</h4>
<div class="separator"></div>
<table class="data" width="100%">
<tbody><tr>
	<td class="label" width="30%">
<label for="uploadSuppFile">
	Upload a file </label>
</td>
	<td class="value" width="70%">
	<form method="POST" enctype="multipart/form-data">
		<input name="articleId" value="{$article->getId()}" type="hidden">
		<input name="uploadFigFile" id="uploadFigFile" class="uploadField" type="file"> <input name="submitUploadSuppFile" class="button" value="Upload" type="submit">
			</td>
	</form>
</tr>
</tbody></table>

<div class="separator"></div>

{include file="common/footer.tpl"}