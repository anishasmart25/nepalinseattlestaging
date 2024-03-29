<?php
/**
 * Admin settings form
 *
 */

use Rtcl\Helpers\Functions;

?>
<div class="wrap rtcl-admin-main-settings rtcl-settings-active-<?php echo esc_attr( $this->active_tab ); ?>">
	<?php
	settings_errors();
	$this->show_messages();
	Functions::print_notices();
	?>
	<div class="rtcl-settings-nav-wrap">
		<ul class="nav-tab-wrapper">
			<?php
			foreach ( $this->tabs as $slug => $title ) {
				$class = "nav-tab nav-" . $slug;
				if ( $this->active_tab === $slug ) {
					$class .= ' nav-tab-active';
				}
				echo '<li>';
				echo '<a href="?post_type=' . rtcl()->post_type . '&page=rtcl-settings&tab=' . $slug . '" class="' . $class . '">' . $title . '</a>';
				/*if ( ! empty( $this->subtabs ) && $this->active_tab === $slug ) {
					echo '<ul class="sub-settings">';
					$array_keys = array_keys( $this->subtabs );
					foreach ( $this->subtabs as $id => $label ) {
						echo '<li><a href="' . admin_url( 'edit.php?post_type=' . rtcl()->post_type . '&page=rtcl-settings&tab=' . $this->active_tab
														  . '&section='
														  . sanitize_title( $id ) ) . '" class="nav-sub-'.strtolower($label) . ( $this->current_section == $id ? ' current' : '' ) . '">'
							 . $label
							 . '</a></li>';
					}
					echo '</ul>';
				}*/
				echo '</li>';
			}
			?>
		</ul>
	</div>
	<div class="rtcl-settings-form-wrap">
		<?php
		if ( ! empty( $this->subtabs ) ) {
			echo '<ul class="sub-settings">';
			$array_keys = array_keys( $this->subtabs );
			foreach ( $this->subtabs as $id => $label ) {
				echo '<li><a href="' . admin_url( 'edit.php?post_type=' . rtcl()->post_type . '&page=rtcl-settings&tab=' . $this->active_tab
												  . '&section='
												  . sanitize_title( $id ) ) . '" class="nav-sub-'.strtolower($label) . ( $this->current_section == $id ? ' current' : '' ) . '">'
					 . $label
					 . '</a></li>';
			}
			echo '</ul>';
		}
		?>
		<form method="post" action="">
			<?php
			do_action( 'rtcl_admin_settings_groups', $this->active_tab, $this->current_section );
			wp_nonce_field( 'rtcl-settings' );
			if ( $this->active_tab !== "addon_theme" ) {
				submit_button();
			}
			?>
		</form>
	</div>
</div>