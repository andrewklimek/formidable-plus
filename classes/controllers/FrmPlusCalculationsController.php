<?php
class FrmPlusCalculationsController{
	
	var $available_calculations = array(
		'sum',
		'average',
		'count',
	);

	public function __construct(){
		add_action( 'frmplus_register_types', array( &$this, 'register_type' ) );
	}
	
	function register_type(){
		FrmPlusFieldsHelper::register_type( array( 
			'type' => 'calculation',
			'has_options' => true,
			'options_callback' => array( &$this, 'options_callback' ),
			'render_callback' => array( &$this, 'render_callback' )
		));
	}
	
	public function massageOptions( $options ){
		if ( isset( $options ) && is_object( $options ) ){
			$options = (array)$options;
		}
		elseif( !isset( $options ) ){
			$options = array();
		}
		
		if ( !isset( $options['on'] ) ){
			$options['on'] = array();
		}
		if ( !isset( $options['precision'] ) ){
			$options['precision'] = 2;
		}
		else{
			$options['precision'] = intval( $options['precision'] );
		}
		if ( !isset( $options['include_empty'] ) ){
			$options['include_empty'] = true;
		}
		else{
			$options['include_empty'] = $options['include_empty'] == 'yes';
		}
		if ( isset( $options['forced'] ) ){
			$options['forced'] = $options['forced'] == 'on';
		}
		return $options;
	}
	
	public function options_callback( $options, $field, $opt_key ){
		$options = $this->massageOptions( $options );
		$id = "calculation-options-" . substr( md5( time() ), 0, 5 ); // random id for the DOM element
		
	    list($columns,$rows) = FrmPlusFieldsHelper::get_table_options( maybe_unserialize($field->options) );
		$is_a = substr($opt_key,0,3); // 'row' or 'col'
		?>
<div id="<?php echo $id; ?>">
	<p class="description">
		<?php printf( __( 'Blah blah blah' ) ); ?>
	</p>
	<div class="calculation-option">
		<label><?php _e( 'Function', FRMPLUS_PLUGIN_NAME ); ?>:</label>
		<select name="frmplus_options[function]">
			<?php foreach ( $this->available_calculations as $option ) : ?>
				<option value="<?php echo $option; ?>" <?php selected( $option, $options['function'] ); ?>><?php echo __( ucwords( $option ), FRMPLUS_PLUGIN_NAME ); ?></option>
			<?php endforeach; ?>
		</select>
		<div>
			<label><?php _e( 'Precision:', FRMPLUS_PLUGIN_NAME ); ?></label>
			<select name="frmplus_options[precision]">
				<?php for( $p = 0; $p < 5; $p ++) : ?>
					<option value="<?php echo $p; ?>" <?php selected( $p, $options['precision'] ); ?>><?php echo $p; ?></option>
				<?php endfor; ?>
			</select>
			<?php _e( 'decimal places', FRMPLUS_PLUGIN_NAME ); ?>
			<label>
				<input type="checkbox" name="frmplus_options[forced]" value="on" <?php checked( true, $options['forced'] ); ?>> <?php _e( 'forced', FRMPLUS_PLUGIN_NAME ); ?>
				<span class="frm_help frm_icon_font frm_tooltip_icon" title="" data-original-title="<?php echo esc_attr( __( 'If forced, then this number of decimals will always show.  Otherwise, they only show when non-zero.', FRMPLUS_PLUGIN_NAME ) ); ?>"></span>
			</label>
		</div>
		<div>
			<?php _e( 'Include empty inputs in calculation?', FRMPLUS_PLUGIN_NAME ); ?>
			<label><input type="radio" value="yes" name="frmplus_options[include_empty]" <?php checked( true, $options['include_empty'] ); ?>><?php _e( 'Yes', FRMPLUS_PLUGIN_NAME ); ?></label>
			<label><input type="radio" value="no" name="frmplus_options[include_empty]" <?php checked( false, $options['include_empty'] ); ?>><?php _e( 'No', FRMPLUS_PLUGIN_NAME ); ?></label>
		</div>
		<div>
			<label><?php _e( 'Calculate these', FRMPLUS_PLUGIN_NAME ); ?> <?php $is_a == 'row' ? _e( 'Columns', FRMPLUS_PLUGIN_NAME ) : _e( 'Rows', FRMPLUS_PLUGIN_NAME ); ?></label>
			<?php foreach ( ($is_a == 'row' ? $columns : $rows) as $target => $opt ) : $label = FrmPlusFieldsHelper::parse_option( $opt, 'name' ); ?>
				<div>
					<label><input type="checkbox" name="frmplus_options[on][]" value="<?php echo $target; ?>" <?php checked( true, empty( $options['on'] ) || in_array( $target, $options['on'] ) ); ?>> <?php echo $label; ?></label>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>
		<?php
	}
	
	public function render_callback( $args ){
		if ( !$this->enqueued ){
			$this->enqueued = true;
		    wp_enqueue_script( 'frm-plus-calculations', plugins_url( 'formidable-plus/js/frm-plus-calculations.js' ), array( 'jquery' ) );
			add_action( ( is_admin() && !defined( 'DOING_AJAX' ) ) ? 'admin_footer' : 'wp_footer', array( &$this, 'localize_script' ) );
		}
				
		extract( $args );

		if ( !isset( $this->particulars[ $field['id'] ] ) ){
			$this->particulars[ $field['id'] ] = array();
		}

		$key = ( $precedence == 'column' ? "column-$col_num" : "row-$row_num" );
		$this->particulars[ $field['id'] ][$key] = $this->massageOptions($options);

		echo '<input type="text" size="10" id="'.$this_field_id.'" name="'.$this_field_name.'['.$col_num.']" value="'.esc_attr($value).'" class="auto_width table-cell calculation" readonly />';
	}
	
	public function localize_script(){
		wp_localize_script( 'frm-plus-calculations', 'FRM_PLUS_CALCULATIONS', 
			array( 
				'particulars' => $this->prepareForLocalization( $this->particulars ),
				'__' => array(
					'error' => __( 'Error', FRMPLUS_PLUGIN_NAME )
				)
			)
		);
	}
	
	public function prepareForLocalization( $particulars ){
		global $frm_field;
		foreach ( $particulars as $field_id => $table_fields ){
			$field = $frm_field->getOne( $field_id );
			list( $columns, $rows ) = FrmPlusFieldsHelper::get_table_options( maybe_unserialize($field->options) );
			foreach ( $table_fields as $key => $settings ){
				foreach ( $settings['on'] as $index => $target ){
					$particulars[ $field_id ][ $key ]['on'][ $index ] = substr( $target, 0, 3 ) == 'row' ? 'row-' . array_search( $target, array_keys( $rows ) ) : 'column-' . array_search( $target, array_keys( $columns ) );
				}
			}
		}
		return $particulars;
	}
	
}

new FrmPlusCalculationsController();