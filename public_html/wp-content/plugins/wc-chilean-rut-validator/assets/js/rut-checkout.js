/**
 * WC Chilean RUT - Bank-style real-time validation with visual icons
 */

(function($) {
	'use strict';

	if (typeof wcChileanRUT === 'undefined') {
		return;
	}

	$(document).ready(function() {
		const $field = $('#billing_rut');
		if (!$field.length) {
			return;
		}

		const $wrapper = $field.closest('.form-row');

		// Agregar contenedor para el icono si no existe
		if (!$wrapper.find('.wc-rut-icon').length) {
			$wrapper.css('position', 'relative');
			$wrapper.append('<span class="wc-rut-icon"></span>');
		}
		const $icon = $wrapper.find('.wc-rut-icon');

		// Validar RUT usando Module 11
		function validateRUT(rut) {
			let clean = rut.replace(/[.\s-]/g, '').toUpperCase();
			
			if (clean.length < 8 || clean.length > 9) {
				return false;
			}

			const body = clean.slice(0, -1);
			const dv = clean.slice(-1);

			// Solo acepta números en el cuerpo
			if (!/^\d+$/.test(body)) {
				return false;
			}

			// Solo acepta números o K en el DV
			if (!/^[\dK]$/.test(dv)) {
				return false;
			}

			const bodyInt = parseInt(body);
			if (bodyInt < 1000000 || bodyInt > 99999999) {
				return false;
			}

			// Module 11
			let sum = 0;
			let factor = 2;
			for (let i = body.length - 1; i >= 0; i--) {
				sum += parseInt(body[i]) * factor;
				factor = factor === 7 ? 2 : factor + 1;
			}

			const remainder = sum % 11;
			const calc = 11 - remainder;

			let expectedDV;
			if (calc === 11) {
				expectedDV = '0';
			} else if (calc === 10) {
				expectedDV = 'K';
			} else {
				expectedDV = calc.toString();
			}

			return dv === expectedDV;
		}

		// Formatear RUT mientras escribe
		function formatRUTLive(value) {
			// Limpiar - solo acepta números y K
			let clean = value.replace(/[^\dKk]/g, '').toUpperCase();
			
			if (clean.length === 0) {
				return '';
			}

			// Limitar a 9 caracteres
			if (clean.length > 9) {
				clean = clean.substring(0, 9);
			}

			// Si tiene más de 1 carácter, separar cuerpo y DV
			if (clean.length > 1) {
				const body = clean.slice(0, -1);
				const dv = clean.slice(-1);

				// Formatear cuerpo con puntos
				let formatted = '';
				let bodyNum = body.replace(/\D/g, '');
				
				// Agregar puntos de miles
				for (let i = bodyNum.length - 1, j = 0; i >= 0; i--, j++) {
					if (j > 0 && j % 3 === 0) {
						formatted = '.' + formatted;
					}
					formatted = bodyNum[i] + formatted;
				}

				return formatted + '-' + dv;
			}

			return clean;
		}

		// Mostrar icono de error
		function showError(message) {
			$wrapper.removeClass('woocommerce-validated').addClass('woocommerce-invalid');
			$wrapper.find('.wc-rut-error').remove();
			$wrapper.append('<span class="wc-rut-error">' + message + '</span>');
			
			// Mostrar X roja
			$icon.removeClass('valid').addClass('invalid').text('✗').show();
		}

		// Mostrar icono de éxito
		function showSuccess() {
			$wrapper.removeClass('woocommerce-invalid').addClass('woocommerce-validated');
			$wrapper.find('.wc-rut-error').remove();
			
			// Mostrar ✓ verde
			$icon.removeClass('invalid').addClass('valid').text('✓').show();
		}

		// Limpiar mensajes e iconos
		function clearMessages() {
			$wrapper.removeClass('woocommerce-invalid woocommerce-validated');
			$wrapper.find('.wc-rut-error').remove();
			$icon.hide().removeClass('valid invalid');
		}

		// Formatear mientras escribe
		$field.on('input', function(e) {
			const cursorPos = this.selectionStart;
			const oldValue = $(this).val();
			const newValue = formatRUTLive(oldValue);
			
			// Actualizar valor
			$(this).val(newValue);

			// Ajustar posición del cursor
			let newCursorPos = cursorPos;
			if (newValue.length < oldValue.length) {
				newCursorPos = cursorPos - 1;
			} else if (newValue.length > oldValue.length) {
				if (newValue[cursorPos] === '.' || newValue[cursorPos] === '-') {
					newCursorPos = cursorPos + 1;
				}
			}
			
			this.setSelectionRange(newCursorPos, newCursorPos);

			// Validar en tiempo real si está completo
			const clean = newValue.replace(/[.\s-]/g, '');
			if (clean.length >= 8) {
				if (validateRUT(newValue)) {
					showSuccess();
				} else {
					showError(wcChileanRUT.messages.invalid);
				}
			} else {
				clearMessages();
			}
		});

		// También validar al salir
		$field.on('blur', function() {
			const value = $(this).val().trim();
			if (!value) {
				clearMessages();
				return;
			}

			if (!validateRUT(value)) {
				showError(wcChileanRUT.messages.invalid);
			}
		});

		// Prevenir pegado de texto inválido
		$field.on('paste', function(e) {
			e.preventDefault();
			const pastedText = (e.originalEvent.clipboardData || window.clipboardData).getData('text');
			const formatted = formatRUTLive(pastedText);
			$(this).val(formatted).trigger('input');
		});
	});

})(jQuery);
