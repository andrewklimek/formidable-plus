<div class="dynamic-table-options">
	<p class="description">
		<?php _e( 'This is a dynamic field that allows the user to add arbitrary rows.  You can set a few options below:', FRMPLUS_PLUGIN_NAME ); ?>
	</p>
	<div class="dynamic-options">
		<?php $dynamic_options = FrmPlusFieldsHelper::get_dynamic_options( $field ); $_d = & $dynamic_options; // shorthand?>
		<label for="frm_starting_rows_<?php echo $field['id']; ?>"><?php _e( 'Initial number of rows', FRMPLUS_PLUGIN_NAME ); ?>:</label>
		<input id="frm_starting_rows_<?php echo $field['id']; ?>" type="number" name="field_options[starting_rows_<?php echo $field['id']; ?>]" class="auto_width" value="<?php echo $_d->starting_rows; ?>" min="0" max="25" /><br/>
		<label for="frm_rows_sortable_<?php echo $field['id']; ?>"><?php _e( 'Rows are Sortable', FRMPLUS_PLUGIN_NAME ); ?>:</label>
		<input id="frm_rows_sortable_<?php echo $field['id']; ?>" type="checkbox" value="yes" name="field_options[rows_sortable_<?php echo $field['id']; ?>]" <?php checked( true, $_d->rows_sortable ); ?> /><br/>
		<label for="frm_add_row_text_<?php echo $field['id']; ?>"><?php _e( 'Add Row Label', FRMPLUS_PLUGIN_NAME ); ?>:</label>
		<input id="frm_add_row_text_<?php echo $field['id']; ?>" type="text" name="field_options[add_row_text_<?php echo $field['id']; ?>]" class="auto_width" value="<?php echo $_d->add_row_text; ?>" /><br/>
		<label for="frm_delete_row_text_<?php echo $field['id']; ?>"><?php _e( 'Delete Row Label', FRMPLUS_PLUGIN_NAME ); ?>:</label>
		<input id="frm_delete_row_text_<?php echo $field['id']; ?>" type="text" name="field_options[delete_row_text_<?php echo $field['id']; ?>]" class="auto_width" value="<?php echo $_d->delete_row_text; ?>" /><br/>
	</div>
</div>