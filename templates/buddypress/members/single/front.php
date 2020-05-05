<?php
/**
 * Block based front template.
 *
 * @package   reception
 * @subpackage \templates\buddypress\members\single\front
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div id="reception-member-front" class="entry-content">
	<?php reception_content(); ?>
</div>

<?php if ( reception_get_edit_template_link() ) : ?>
	<footer class="entry-footer">
		<?php
		reception_edit_post_link(
			__( 'Modifier le gabarit', 'reception' ),
			'<span class="edit-link">',
			'</span>'
		);
		?>
	</footer><!-- .entry-footer -->
<?php endif; ?>
