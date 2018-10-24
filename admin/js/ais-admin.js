(function() {
	'use strict';

/** Front validation */

	var forms = document.querySelectorAll('form');

	function validateElements(form) {
		var formElements = form.querySelectorAll('input:not([type="hidden"]):not([type="submit"]), textarea, select');
	
		for (var j = formElements.length - 1; j >= 0; j--) {
			if (formElements[j].getAttribute('type') !== 'checkbox') {
				formElements[j].insertAdjacentHTML('afterend', '<p class="form__error"></p>')
				formElements[j].addEventListener('invalid', function(e) {
					this.nextSibling.textContent = this.validationMessage;
				});

				formElements[j].addEventListener('blur', function(e) {
					this.nextSibling.textContent = '';
					this.checkValidity();
				});
			}
		}
	}

	function enableSubmit() {
		this.querySelector('button[type="submit"]').removeAttribute('disabled');
	}

	for (var i = forms.length - 1; i >= 0; i--) {
		forms[i].noValidate = true;
		forms[i].addEventListener('submit', function(e) {
			if (!this.checkValidity()) {
				e.preventDefault();
				e.stopImmediatePropagation()
			}
		});

		validateElements(forms[i]);

		forms[i].addEventListener('change', enableSubmit);
	}

/** Tabs */

	var cache = {}, last;

	Array.prototype.forEach.call(document.querySelectorAll('[role="tablist"]'), function (tablist) {
		Array.prototype.forEach.call(tablist.querySelectorAll('[href^="#"][role="tab"]'), function (tab, index, tabs) {
			cache[tab.hash] = [tab, document.getElementById(tab.getAttribute('aria-controls'))];

			if (tab.getAttribute('aria-selected') === 'true') {
				last = cache[''] = cache[tab.hash];
			}

			tab.addEventListener('keydown', function (event) {
				var next = event.keyCode === 37 ? tabs[index - 1] : event.keyCode === 39 ? tabs[index + 1] : null;

				if (next) {
					location.hash = next.hash;

					next.focus();
				}
			});
		});
	});

	function onhashchange() {
		var tab = cache[location.hash];

		if (tab) {
			if (last) {
				last[0].removeAttribute('aria-selected');
				last[0].setAttribute('tabindex', -1);
				last[1].setAttribute('hidden', '');
			}

			tab[0].setAttribute('aria-selected', 'true');
			tab[0].removeAttribute('tabindex');
			tab[1].removeAttribute('hidden', '');

			last = tab;
		}
	}

	window.addEventListener('hashchange', onhashchange);
	onhashchange();

/** Custom sizes */

	var customSizes = document.getElementById('custom-sizes');
	var resultMessage = document.getElementById('result-message');

	function setMessage(message) {
		resultMessage.innerHTML = message || '';
	}

	function post(args, callback, isFormData) {
		var isFormData = isFormData || false;

		var request = new XMLHttpRequest();

		if (isFormData) {
			var params = new FormData(args['form']);

			params.append('action', args['action']);
			params.append('nonce', args['nonce']);
		} else {
			var params = '';

			for (var key in args)
				params += key + '=' + args[key] + '&';
			params = params.substring(0, params.length - 1);
		}

		request.onload = function() {
			if (request.status >= 200 && request.status < 400) {
				callback(JSON.parse(request.responseText));
			} else {
				setMessage(AIS.messages.ajaxFailure.server);
			}
		};

		request.onerror = function() {
			setMessage(AIS.messages.ajaxFailure.connection);
		};

		setMessage();

		request.open('POST', AIS.ajaxUrl, true);
		if (!isFormData)
			request.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		request.send(params);
	}

	function applyListeners() {
		updateForm = document.getElementById('update-form');
		removeButtons = document.getElementsByClassName('remove-button');

		if (updateForm) {
			updateForm.addEventListener('submit', updateSizes);
			validateElements(updateForm);
			updateForm.addEventListener('change', enableSubmit);
		}

		if (removeButtons) {
			for(var i = 0, l = removeButtons.length; i < l; i++)
				removeButtons[i].addEventListener('click', displayRemoveModal);
		}
	}

	function updateContent(content) {
		customSizes.innerHTML = content;
		applyListeners();
	}

/** Default sizes */

	var defaultForm = document.getElementById('default-form');
	var defaultSubmit = document.getElementById('default-submit');

	function disableDefault(e) {
		e.preventDefault();

		var args = {
			'form' : defaultForm,
			'action' : AIS.actions.default,
			'nonce' : AIS.nonce
		};

		post(args, postResponse, true);

		function postResponse(response) {
			setMessage(response.data.message)
		}
	}

	if (defaultSubmit)
		defaultSubmit.addEventListener('click', disableDefault);

/** Add size */

	var addForm = document.getElementById('add-form');
	var addSizeArgs = {
		'form' : addForm,
		'action' : AIS.actions.add,
		'nonce' : AIS.nonce
	};

	function addSize(e) {
		e.preventDefault();

		post(addSizeArgs, postResponse, true);

		function postResponse(response) {
			if (response.success) {
				updateContent(response.data.content);
				setMessage(response.data.message);
			} else {
				setMessage(response.data.message)
			}
		}
	}

	if (addForm)
		addForm.addEventListener('submit', addSize);

/** Update sizes */

	var updateForm = document.getElementById('update-form');
	var updateSizesArgs = {
		'form' : updateForm,
		'action' : AIS.actions.update,
		'nonce' : AIS.nonce
	};

	function updateSizes(e) {
		e.preventDefault();

		post(updateSizesArgs, postResponse, true);

		function postResponse(response) {
			if (response.success) {
				updateContent(response.data.content);
				setMessage(response.data.message);
			} else {
				setMessage(response.data.message)
			}
		}
	}

	if (updateForm)
		updateForm.addEventListener('submit', updateSizes);

/** Logs */

	var logsContainer = document.getElementById('logs-container');
	var logsBar = document.getElementById('logs-bar');
	var logsStatus = document.getElementById('logs-status');
	var logs = document.getElementById('logs');
	var logsCounter = 0;

	function resetLogs(logsLength) {
		logsBar.max = logsLength || 0;
		logsCounter = 0;
		logs.innerHTML = '';
		logsBar.value = logsCounter;
	}

	function addLog(logHtml) {
		logsCounter++;
		logsBar.value = logsCounter;
		logs.innerHTML += logHtml;
		logsStatus.textContent = logsCounter + '/' + logsBar.max;
	}

	resetLogs();

/** Remove size */

	var removeButtons = document.getElementsByClassName('remove-button');
	var removeModal = document.getElementById('remove-modal');
	var removeImagesCheckbox = document.getElementById('remove-images-checkbox');
	var cancelRemoveButton = document.getElementById('cancel-remove-button');
	var confirmRemoveButton = document.getElementById('confirm-remove-button');
	var currentRemoveIndex = 9999;
	var sizeAttachments = [];

	function displayRemoveModal(e) {
		e.preventDefault();

		currentRemoveIndex = this.getAttribute('data-index');

		if (!removeModal)
			removeModal = document.getElementById('remove-modal');
		removeModal.setAttribute('aria-hidden', false);
	}

	function closeRemoveModal() {
		removeModal.setAttribute('aria-hidden', true);
	}

	function removeAttachmentSize(attachmentId, sizeName) {
		var removeAttachmentSizeArgs = {
			'attachment_id' : attachmentId,
			'size_name' : sizeName,
			'action' : AIS.actions.removeSizeFile,
			'nonce' : AIS.nonce
		};

		post(removeAttachmentSizeArgs, removeAttachmentSizeResponse);

		function removeAttachmentSizeResponse(response) {
			addLog(response.data);

			if (sizeAttachments.length) {
				removeAttachmentSize(sizeAttachments.shift(), sizeName);
			}
		}
	}

	function removeSize(e) {
		var args = {
			'index' : currentRemoveIndex,
			'remove_images' : removeImagesCheckbox.checked,
			'action' : AIS.actions.remove,
			'nonce' : AIS.nonce
		};

		post(args, postResponse);

		function postResponse(response) {
			if (response.success) {
				updateContent(response.data.content);
				setMessage(response.data.message);
				closeRemoveModal();
				if (response.data.attachments_ids) {
					if (Array.isArray(response.data.attachments_ids)) {
						sizeAttachments = response.data.attachments_ids;
						resetLogs(sizeAttachments.length);
						removeAttachmentSize(sizeAttachments.shift(), response.data.size_name);
					} else {
						setMessage(response.data.attachments_ids);
					}
				}
			} else {
				setMessage(response.data.message)
			}
		}
	}

	for (var i = 0, l = removeButtons.length; i < l; i++)
		removeButtons[i].addEventListener('click', displayRemoveModal);
	cancelRemoveButton.addEventListener('click', closeRemoveModal);
	confirmRemoveButton.addEventListener('click', removeSize);

/** Regeneration */

	var regenerate = document.getElementById('regenerate');
	var getAttachmentsArgs = {
		'action' : AIS.actions.getAllAttachments,
		'nonce' : AIS.nonce
	};
	var regenerateArgs = {
		'action' : AIS.actions.regenerate,
		'nonce' : AIS.nonce
	};
	var regenerateAttachments = [];

	function getAllAttachments() {
		post(getAttachmentsArgs, getAttachmentsResponse);

		function getAttachmentsResponse(response) {
			if (response.success) {
				regenerateAttachments = response.data;
				regenerateAll();
			} else {
				setMessage(response.data);
			}
		}
	}

	function regenerateAll() {
		resetLogs(regenerateAttachments.length);
		regenerateAttachment(regenerateAttachments.shift());
	}

	function regenerateAttachment(attachmentId) {
		regenerateArgs.attachment_id = attachmentId;
		post(regenerateArgs, postResponse);

		function postResponse(response) {
			addLog(response.data);

			if (regenerateAttachments.length) {
				regenerateAttachment(regenerateAttachments.shift());
			}
		}
	}

	if (regenerate)
		regenerate.addEventListener('click', getAllAttachments);
})();