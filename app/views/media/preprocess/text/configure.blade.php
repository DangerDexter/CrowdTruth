@extends('media.preprocess.relex.layouts.default')

@section('head')
<script>
{{--
	Generate the DIV element which holds an individual document property. 
	This div element contains the name of the property,  the function to be applied 
	and the column such function should be applied to.
--}}
	function getPropertyDiv(parentGroupId, propName){
	    propId = parentGroupId + '_' + propName;
	    divStr = '' +
	        '		<div class="panel panel-default" id="' + propId + '_div">' +
	        '		<div class="panel-body">' +
	        '			Name: ' + propName +
	        '            <input type="hidden" name="' + propId + '_propName" id="' + propId + '_propName" value="' + propId + '" class="propertyName"/>' +
	        '            <input type="hidden" name="' + propId + '_propParent" id="' + propId + '_propParent" value="' + parentGroupId + '"/>' +	        
	        '            <input type="button" name="' + propId + '_close" id="' + propId + '_close" value="x"/><br/>' +
	        '			Value: <br>' +
	        '			Function: <br>' +
	        '           <select name="' + propId + '_function" id="' + propId + '_function">' +
{{-- Load the available functions --}}
	        @foreach ($functions as $function)
			'           <option value="{{ $function->getName() }}"> {{ $function->getName()	 }} </option>' +
			@endforeach
	        '           </select> ' +

	        '			<div id="' + propId + '_params">' +
	        '			</div>' +

	        '		</div>' +
	        '		</div>';
	    return divStr;
	}

{{--
	Create a new SELECT element listing all columns available. 
--}}
	function getColumnsSelector(selectorName) {
		colsSelect = '' +
        	'<select name="' + selectorName + '" id="' + selectorName + '">' +
{{-- Load the available columns --}}
        	@foreach ($columns as $colIdx => $colName)
        	'  <option value="{{ $colIdx }}"> {{ $colName }} </option>' +
        	@endforeach
        	'</select>';
        return colsSelect;
	}

	function getPropertySelector(selectorName) {
	    inputs = [];
	    $('.propertyName').each(function(i, obj) {
	         inputs.push($(this).val());
	    });

	    propSelect = '<select name="' + selectorName + '" id="' + selectorName + '">';
		for(idx in inputs) {
			inputValue = inputs[idx];
			inputName = inputValue.split("_").join(".");
			propSelect += '  <option value="' + inputValue + '">' + inputName + '</option>';
		}
		propSelect += '</select>';
		return propSelect;
	}

	function getGroupSelector(selectorName) {
	    inputs = [];
	    $('.groupName').each(function(i, obj) {
	         inputs.push($(this).val());
	    });

	    groupSelect = '<select name="' + selectorName + '" id="' + selectorName + '">';
		for(idx in inputs) {
			inputValue = inputs[idx];
			inputName = inputValue.split("_").join(".");
			groupSelect += '  <option value="' + inputValue + '">' + inputName + '</option>';
		}
		groupSelect += '</select>';
		return groupSelect;
	}
	
{{--
	Generate the DIV element for a group. This div element contains the group name
	and allows for the creation of subgroups and properties inside it. 
--}}
	function getGroupDiv(parentGroupId, groupName) {
		groupId = parentGroupId + "_" + groupName;
	    divStr = '' +
	        '		<div class="panel panel-default" id="' + groupId + '_div">' +
	        '		<div class="panel-body">' +
	        '			' + groupName + 

	        '           <input type="hidden" name="' + groupId + '_groupName" id="' + groupId + '_groupName" value="' + groupId + '" class="groupName"/>' +
	        '           <input type="hidden" name="' + groupId + '_groupParent" id="' + groupId + '_groupParent" value="' + parentGroupId + '"/>' +
	        '           <input type="button" name="' + groupId + '_close" id="' + groupId + '_close" value="x"/><br/>' +
	        '			Add new: <br>' +
	        '			<input type="text" value="" id="' + groupId + '_newName"/ >' +
	        '			<input type="button" value="New property" id="' + groupId + '_newProp"/>' +
	        '			<input type="button" value="New group" id="' + groupId + '_newGroup"/>' +
	        '		</div>' +
	        '		<div class="panel-body" id="' + groupId + '_props">' +
	        '		</div>' +
	        '		</div>';
	    return divStr;
	}

{{--
	Create a new property DIV as a child of the given parent group. 
--}}
	function newPropertyAction(parentGroupId) {
	  props = parentGroupId + "_props";
	  propName = $("#" + parentGroupId + "_newName").val();
	  propId = parentGroupId + "_" + propName;
	  // Add property div 
	  $("#" + props).append(getPropertyDiv(parentGroupId, propName));
	  // Add actions to buttons on the div 
	  $("#" + propId + "_close").click(function(){
	      $(this).parent().parent().remove();
	  });
	  $("#" + propId + "_function").change(function(){
		  functionName = $(this).val();
		  propId = this.id.replace("_function",  "");
	      selectFunction(functionName, propId);
	  });
	  $("#" + propId + "_function").change();
    }

{{--
	Create a new group DIV as a child of the given parent group. 
--}}
	function newGroupAction(parentGroupId) {
	  props = parentGroupId + "_props";
	  groupName = $("#" + parentGroupId + "_newName").val();
	  // Add group div 
	  $("#" + props).append(getGroupDiv(parentGroupId, groupName));

	  // Add actions to buttons on the div
	  $("#" + groupId + "_newProp").click(function(){
	      groupId1 = this.id.replace("_newProp",  "");
	      newPropertyAction(groupId1);
	  });
	  $("#" + groupId + "_newGroup").click(function(){
	      groupId1 = this.id.replace("_newGroup",  "");
	      newGroupAction(groupId1);
	  });
	  $("#" + groupId + "_close").click(function(){
	      $(this).parent().parent().remove();
	  });
	}

	function selectFunction(functionName, propId) {
		divName = propId + "_params";
	    switch(functionName) {
  		  @foreach ($functions as $function)
	    	case '{{ $function->getName() }}': divHtml = {{ $function->getParameterJSFunctionName() }}(propId); break;
		  @endforeach
	    }
    	$('#' + divName).html(divHtml);
	}

	function doPreview() {
		$('#preview').val('true');
		formUrl = $("#theForm").attr("action");
		formData = $("#theForm").serialize();
		$.ajax({
			type: "POST",
			url: formUrl,
			data: formData,
			success: function(data) {
				$("#contentPreview").html(data);
			}
		});
	}
	</script>

<!-- START getParameterJSFunction's -->
@foreach ($functions as $function) 
{{ $function->getParameterJSFunction() }} 
@endforeach
<!-- FINISH getParameterJSFunction's -->
@stop

@section('relexContent')

<!-- START status messages -->
@if (isset($status['error']))
	<div class="panel panel-danger">
		<div class="panel-heading">
			<h4><i class="fa fa-exclamation-triangle fa-fw"></i>Error</h4>
		</div>
		<div class="panel-body CW_messages">
			<ul class="list-group">
				<li class="list-group-item"><span class='message'> {{ $status['error'] }} </li>
			</ul>
		</div>
	</div>
@endif

@if(isset($status['success']))
	<div class="panel panel-success">
		<div class="panel-heading">
			<h4><i class="fa fa-check fa-fw"></i>Success</h4>
		</div>
		<div class="panel-body CW_messages">
			<ul class="list-group">
			<li class="list-group-item"><span class='message'> {{ $status['success'] }} </li>
			</ul>
		</div>
	</div>
@endif
<!-- End status messages -->

<!-- START preprocess/text/configure -->
<div class="page-header">
	<p>
		Document: <b>{{$docTitle}}</b>
	</p>
</div>

Document preview:
<div style="height: 150px; overflow: auto;">
	<table class="table table-bordered">
		<thead>
			<tr>
				@foreach ($columns as $column)
				<th style="width: 10%">{{ $column }}</th> 
				@endforeach
			</tr>
		</thead>
		<tbody>
			@foreach ($dataTable as $row)
			<tr>
				@foreach ($row as $column)
				<td>{{ str_limit($column, 30) }}</td>
				@endforeach
			</tr>
			@endforeach
		</tbody>
	</table>
</div>

<!-- BEGIN DYNAMIC STRUCTURE FORM  -->
{{ Form::open(array('action' => 'preprocess\TextController@postConfigure', 'name' => 'theForm', 'id' => 'theForm' )) }}
{{ Form::hidden('URI', $URI) }}
{{ Form::hidden('preview', '', [ 'id' => 'preview' ]) }}

<div class="panel panel-default">
	<div class="panel-body">
		Entity content structure:
		<input type="hidden" name="root_groupName" id="root_groupName" value="root"/>

		<div class="row">
			<div class="col-md-6">
				Add new: <br>
				<input type="text" value="" id="root_newName" />
				<input type="button" value="New property" id="root_newProp"/>
				<input type="button" value="New group" id="root_newGroup" />
			</div>
	
			<div class="col-md-6">
				Load existing configuration
				<input type="button" value="Select...">
			</div>
		</div>

		<div class="panel panel-default">
			<div class="panel-body" id="root_props"></div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-6">
	<input type="button" onclick="doPreview();" value="Preview" />
	</div>
	<div class="col-md-6">
	<input type="submit" onClick="$('#preview').val('false');" value="Process ">
	</div>
</div>

{{ Form::close() }}	
<!-- END DYNAMIC STRUCTURE FORM  -->

<p>Preview: </p>
<pre name='contentPreview' id='contentPreview'>
</pre>
<!-- STOP preprocess/text/configure -->
@stop

@section('end_javascript')
<script>
	// TODO: Couldn't this be added dynamically? 
	$(document).ready(function(){
		$("#root_newProp").click(function(){
		  newPropertyAction("root");
		});
		$("#root_newGroup").click(function(){
		  newGroupAction("root");
		});
	});
</script>
@stop