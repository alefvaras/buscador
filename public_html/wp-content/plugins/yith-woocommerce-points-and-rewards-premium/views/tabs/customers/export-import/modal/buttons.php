<div class="wrap-buttons">
	<a href="#" class="ywpar-export-import-modal-button move-step previous"
	   data-step-to="1"><
		<?php
		echo esc_html_x(
			'Back',
			'Link to move to previous step',
			'yith-woocommerce-points-and-rewards'
		);
		?>
	</a>

	<button class="button yith-plugin-fw__button--primary move-step ywpar-submit-button"
	        id="submit" data-step-to="3">
		<?php
		echo esc_html_x(
			'Submit',
			'Start the import button',
			'yith-woocommerce-points-and-rewards'
		);
		?>
	</button>
</div>
