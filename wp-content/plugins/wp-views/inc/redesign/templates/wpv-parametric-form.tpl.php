<div class="wpv-dialog wpv-dialog-parametric-filter" id="js-parametric-form-dialog-container">

	<div class="wpv-dialog-header">
		<h2 id="parametric-box-title"><?php _e('Insert a filter field', 'wpv-views');?></h2>
		<i class="icon-remove js-dialog-close"></i>
	</div>

<!--	<div class="wpv-dialog-sidebar filter-preview">
		<p><?php _e('Preview', 'wpv-views');?></p>
		<p class="toolset-alert toolset-alert-info" id="js_wpv_prm_preview" data-bind="html:setPreview">

			<?php _e('Select what to filter to see the preview here', 'wpv-views');?>
		</p>

	</div> -->

	<div id="js-parametric-form-container-inner" class="wpv-dialog-content">

		<form id="js-parametric-form" >

			<fieldset id="js-dialog-form-visible-paramteric" class="fieldset-no-border">

			<!--	<legend id="js_legend_default"><?php _e('Defaults', 'wpv-views');?></legend> -->

				<p>
					<label for="selectFilter" id="js_select_parametric_filter"><?php _e('Select what to filter by :', 'wpv-views');?></label>


					<!-- Main select. We do it this way to group values with <optgroup> not supported otherwise-->
					<select data-bind="foreach: selectFilter, value:fieldRaw">
					<!-- Set a caption for the select only once-->
					<!-- ko ifnot: $index -->
					   <option value="" id="js-parametric-option-default"><?php _e('Choose what to filter by:', 'wpv-views');?></option>
					 <!-- /ko -->
					    <optgroup data-bind="attr: {label: label}, foreach: children">
					        <option data-bind="text: name,
											   option: $data">
							</option>
					    </optgroup>
					</select>


				</p>

				<p data-bind="visible:fieldRaw">
					<label for="selectInputKind" id="js-parametric-input-default">Select input kind:</label>
					<select data-bind="options: selectInputKind, value:type, optionsCaption: 'Choose input type:'"id="selectInputKind"></select>
				</p>

				<div data-bind="visible:fieldRaw">

					<p data-bind="visible:default_label_visible">
							<label for="default_label" id="js_parametric_default_label"><?php _e('Default label:', 'wpv-views');?></label>
							<input data-bind='value: default_label, valueUpdate:"afterkeydown"' id="default_label" type="text" />
					</p>

					<div data-bind="visible:taxonomy_order_visible">
					<p>
							<label for="taxonomy_order" id="js_parametric_default_label"><?php _e('Taxonomy order:', 'wpv-views');?></label>
							<select data-bind='options: order_array, value:order' id="taxonomy_order" type="text" />
					</p>

					<p>
							<label for="taxonomy_order_by" id="js_parametric_default_label"><?php _e('Taxonomy order by:', 'wpv-views');?></label>
							<select data-bind='options: order_by_array, value:order_by' id="taxonomy_order_by" type="text" />
					</p>
					</div>
					
					<div data-bind="visible:show_values_settings">

						<p id="js_default_values_from"><?php _e('Default values from:', 'wpv-views');?></p>

						<ul>
							<li>
								<input type="radio" value="1" data-bind="checked:auto_fill" id="auto_fill_yes" />
								<label for="auto_fill_yes" id="js_parametric_label_existing"><?php _e('Use existing custom field values', 'wpv-views');?> </label>
							</li>

							<li>
								<input type="radio" value="0" data-bind="checked:auto_fill" id="auto_fill_no"/>
								<label for="auto_fill_no" id="js_parametric_manually_entered"><?php _e('Use manually entered values', 'wpv-views');?></label>
							</li>
						</ul>

					</div>

					<table data-bind="visible:userValuesVisible">
						<thead>
							<tr>
								<th id="js_parametric_values"><?php _e('Values:', 'wpv-views');?></th>
								<th id="js_parametric_possible_values"><?php _e('Display values:', 'wpv-views');?> </th>
								
							</tr>
						</thead>
						<tbody data-bind="foreach:values">
							<tr>
								<td>
									<input data-bind='value: $data.values, valueUpdate:"afterkeydown"' class="values js-user-values" type="text" />
								</td>
								<td>
									<input data-bind='value: $data.display_values, valueUpdate:"change"' class="display_values js-user-display-values" type="text" />
								</td>
								<td class="remove-sign-wrap">
									<i class="icon-remove-sign js-action-name" data-bind="click:$root.remove_user_values_box"></i>
								</td>
							</tr>
						</tbody>
					</table>

					<p data-bind="visible:auto_fill_default_visible">
						<label for="values" id="js_parametric_possibile_inputs"><?php _e('Default value:', 'wpv-views');?></label>
						<input data-bind='value: auto_fill_default, valueUpdate:"afterkeydown"' id="auto_fill_default" type="text" />
					</p>
					
					<p data-bind="visible:checkbox_title_visible">
						<label for="values" id="js_parametric_checkbox_title"><?php _e('Title:', 'wpv-views');?></label>
						<input data-bind='value: title, valueUpdate:"afterkeydown"' id="title" type="text" />
					</p>

					<p data-bind="visible:userValuesVisible" class="prm-button-holder">
						<button data-bind="visible:userValuesVisible, click:add_user_values_box" class="button-secondary"><i class="icon-plus"></i id="js_parametric_another_value"> <?php _e('Add another value', 'wpv-views');?></button>
					</p>

				</div>
				
				<div id="auto_fill_sort_container" data-bind="visible:auto_fill_sort_visible">
					<label for="auto_fill_sort" id="auto_fill_sort_label"><?php _e('Sort values:', 'wpv-views');?></label>
					<select data-bind="foreach: selectAutoFillSort, value:auto_fill_sort">
						<option data-bind="text: title,
										   option: value">
						</option>
					</select>
				</div>

			</fieldset>

			<fieldset id="js-dialog-form-visibles-hidden" class="dialog-form-hidden" data-bind="visible:fieldRaw">

				<legend class="is-toggle-viz-legend">
					<span id="js_parametric_advanced"><?php _e('Advanced:', 'wpv-views');?></span>
					<span id="js-toggle-advanced-paramentric-form-fields" class="toggle-visibility-fieldset">
						<?php _e('Expand', 'wpv-views'); ?>
						<i class="icon-caret-down"></i>
					</span>
				</legend>

				<div class="hidden js-hidden-fields-container">
					<p>
						<label for="selectCompare" id="js_parametric_comparison"><?php _e('Comparison function:', 'wpv-views');?></label>
						<select data-bind="options: selectCompare, value: compare" id="selectCompare"></select>
					</p>

					<div data-bind="foreach:url_param">
						<p>
						<label for="field" id="js_parametric_url_param"><?php _e('Refer to this field as:', 'wpv-view');?></label>
						<input data-bind='value: $data.value, valueUpdate:"afterkeydown"' id="url_param" type="text">
						</p>
					</div>

					<p>
						<label for="selectDataType" id="js_parametric_compare_as"><?php _e('Compare this values as:', 'wpv-views');?></label>
						<select data-bind="options: selectDataType, value:field_data_type" id="selectDataType"></select>
					</p>
				</div>

			</fieldset>

		</form>

		<div class="js-errors errors-in-parametric-box"></div>

	</div> <!-- .wpv-dialog-content -->

    <div class="wpv-dialog-footer wp-core-ui">
        <button class="button js-dialog-close" id="js_parametric_cancel"><?php _e('Cancel', 'wpv-views');?></button>
        <button class="button button-primary" id="js_parametric_form_button" disabled><?php _e('Insert input', 'wpv-views');?></button>
    </div>

</div>  <!-- .wpv-dialog -->