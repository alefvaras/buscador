<div id="step-completed" data-step="3" class="single-step completed">
    <div class="success">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="message"></p>
    </div>

    <div class="error">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
        <p class="message"></p>
    </div>

	<button class="yith-plugin-fw__button--close" id="close-modal">
		<?php
		echo esc_html_x(
			'Close',
			'button to close the import/export modal',
			'yith-woocommerce-points-and-rewards'
		);
		?>
	</button>
	<button class="yith-plugin-fw__button try-again" type="button"
	        data-step-to="2">
		<?php
		echo esc_html_x(
			'Try again',
			'button to try again a failed CSV import',
			'yith-woocommerce-points-and-rewards'
		);
		?>
	</button>
</div>
