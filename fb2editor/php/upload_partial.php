<?
 //upload_partial.php
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
  <title>Partial</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <meta name="HandheldFriendly" content="true">
  <meta http-equiv="cache-control" content="no-cache"/>
  <meta http-equiv="expires" content="0"/>
  <meta http-equiv="pragma" content="no-cache"/>
  <meta charset="utf-8" /> 

   
  <link rel="stylesheet" href="/css/bootstrap.min.css" />
  <link rel="stylesheet" href="/css/bootstrap-dialog.min.css" />
  <link rel="stylesheet" href="/css/sqlite.css" />
  
  <script type="text/javascript" src="/js/jquery.min.js"></script>
  <script type="text/javascript" src="/js/bootstrap.min.js"></script>
  <script type="text/javascript" src="/js/bootstrap-dialog.min.js"></script>
  <script type="text/javascript" src="/js/loader.js"></script>
  <script type="text/javascript" src="/js/upload_partial.js"></script>
</head>
<body>

<!--#fm-upload-form-->
<div class="panel panel-default">
  <p class="panel-heading text-center">Upload files to server</p>
  <div class="row">
    <div class="col-xs-2"></div>
    <div class="col-xs-8">
      <button class="btn btn-success visible" onclick="uploader.select_for_upload()" id="select-button">Select files</button>
      <button class="btn btn-success hidden" onclick="uploader.upload()" id="upload-button">Upload</button>
      <span id="upload-files-count">No selected files</span>
    </div>
    <div class="col-xs-2"></div>
  </div>

</div>

<input type="file"
       onchange="uploader.show_selected_files();"
       id="control_upload_files" multiple style="visibility:hidden;position:absolute;top:-50px;left:-50px" />

<div id="control_progress" class="container-fluid"></div>

<!--#fm-upload-progress-header-->

<!--#fm-upload-progress-body
{{*
   var sz =coba_format_bytes(item.size);
   var nm = encodeURIComponent(fm.join_path() + item.name);
}}
  <div class="panel panel-default" id="upload-row-{{index}}">
    <div class="panel-heading">
      <b>{{item.name}}</b>
    </div>
    <div class="row">
      <div class="col-xs-6">size <b>{{sz}}</b></div>
      <div class="col-xs-6" id="upload-progress-{{index}}">0.000%</div>
    </div>
    <div class="row">
      <div class="col-xs-6" id="upload-error-{{index}}"></div>
      <div class="col-xs-6" >
        <button class="btn btn-danger hidden" id="upload-delete-{{index}}"
            onclick="uploader.delete_file('{{nm}}','{{item.name}}',this);">Удалить {{item.name}}</button></div>
    </div>
  </div>
  -->
  <!--#fm-upload-progress-footer-->

  <!--#fm-upload-complete-->
  <div class="container">
    <p class="text-primary"> Upload complete successfully</p>
    <p> <b data-method="fm_refresh">Refresh folder content</b></p>
    <p> <b data-method="create_upload_form">Upload More files</b></p>
  </div>


</body>
</html>