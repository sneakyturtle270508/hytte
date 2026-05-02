<?php

class pluginHyttehubTools extends Plugin
{
	public function init()
	{
		$this->dbFields = array();
	}

	public function adminSidebar()
	{
		$html  = '<a class="nav-link" href="' . HTML_PATH_ADMIN_ROOT . 'new-content?hyttehub-cabin=1">';
		$html .= '<span class="fa fa-home"></span> New cabin';
		$html .= '</a>';
		$html .= '<a class="nav-link" href="' . HTML_PATH_ADMIN_ROOT . 'content">';
		$html .= '<span class="fa fa-list"></span> Manage cabins';
		$html .= '</a>';

		return $html;
	}

	public function adminBodyEnd()
	{
		$script = <<<'HTML'
<script>
(function () {
	var hyttehubInteriorInput = null;

	function ready(fn) {
		if (document.readyState !== 'loading') {
			fn();
			return;
		}
		document.addEventListener('DOMContentLoaded', fn);
	}

	function fieldByName(name) {
		return document.querySelector('[name="custom[' + name + ']"]');
	}

	function splitImages(value) {
		return (value || '').split(',').map(function (item) {
			return item.trim();
		}).filter(Boolean);
	}

	function joinImages(images) {
		return images.join(', ');
	}

	function mediaUrlFromClick(target) {
		var link = target.closest ? target.closest('a') : null;
		if (!link) {
			return '';
		}
		var onClick = link.getAttribute('onclick') || link.getAttribute('onClick') || '';
		var match = onClick.match(/editorInsertMedia\('([^']+)'\)/);
		return match ? match[1] : '';
	}

	function addImageToInteriorGallery(url) {
		if (!hyttehubInteriorInput || !url) {
			return false;
		}
		var images = splitImages(hyttehubInteriorInput.value);
		if (images.indexOf(url) === -1) {
			images.push(url);
		}
		hyttehubInteriorInput.value = joinImages(images);
		hyttehubInteriorInput = null;
		if (typeof closeMediaManager === 'function') {
			closeMediaManager();
		}
		var existingGallery = document.getElementById('hyttehub-interior-gallery');
		if (existingGallery) {
			existingGallery.remove();
		}
		addInteriorGallery();
		return true;
	}

	function addInteriorGallery() {
		var input = fieldByName('interior_image_1');
		var legacyInput = fieldByName('interior_image_2');
		if (!input || document.getElementById('hyttehub-interior-gallery')) {
			return;
		}

		var group = input.closest('.form-group, .mt-2') || input.parentNode;

		var images = splitImages(input.value);
		if (legacyInput && legacyInput.value) {
			splitImages(legacyInput.value).forEach(function (url) {
				if (images.indexOf(url) === -1) {
					images.push(url);
				}
			});
		}
		input.value = joinImages(images);

		input.type = 'hidden';
		if (legacyInput) {
			legacyInput.closest('.form-group, .mt-2').style.display = 'none';
			legacyInput.value = '';
		}

		var label = group.querySelector('label');
		if (label) {
			label.textContent = 'Interior photos';
		}

		var gallery = document.createElement('div');
		gallery.id = 'hyttehub-interior-gallery';
		gallery.className = 'hyttehub-interior-gallery';
		gallery.setAttribute('aria-label', 'Interior cabin photos');

		var slider = document.createElement('div');
		slider.className = 'hyttehub-gallery-slider';

		var previous = document.createElement('button');
		previous.type = 'button';
		previous.className = 'hyttehub-gallery-nav';
		previous.setAttribute('aria-label', 'Previous interior photos');
		previous.innerHTML = '&lsaquo;';

		var next = document.createElement('button');
		next.type = 'button';
		next.className = 'hyttehub-gallery-nav';
		next.setAttribute('aria-label', 'Next interior photos');
		next.innerHTML = '&rsaquo;';

		var help = document.createElement('small');
		help.className = 'form-text text-muted mb-2';
		help.textContent = 'Add multiple inside photos. The plus tile stays at the end.';

		function sync() {
			input.value = joinImages(images);
		}

		function render() {
			gallery.innerHTML = '';
			images.forEach(function (url, index) {
				var tile = document.createElement('div');
				tile.className = 'hyttehub-photo-tile';

				var image = document.createElement('img');
				image.src = url;
				image.alt = 'Interior photo ' + (index + 1);

				var remove = document.createElement('button');
				remove.type = 'button';
				remove.className = 'hyttehub-remove-photo';
				remove.setAttribute('aria-label', 'Remove interior photo');
				remove.innerHTML = '&times;';
				remove.addEventListener('click', function () {
					images.splice(index, 1);
					sync();
					render();
				});

				tile.appendChild(image);
				tile.appendChild(remove);
				gallery.appendChild(tile);
			});

			var add = document.createElement('button');
			add.type = 'button';
			add.className = 'hyttehub-add-photo';
			add.setAttribute('aria-label', 'Add interior photo');
			add.innerHTML = '<span>+</span>';
			add.addEventListener('click', function () {
				hyttehubInteriorInput = input;
				if (typeof openMediaManager === 'function') {
					openMediaManager();
				}
			});
			gallery.appendChild(add);
		}

		previous.addEventListener('click', function () {
			gallery.scrollBy({ left: -260, behavior: 'smooth' });
		});

		next.addEventListener('click', function () {
			gallery.scrollBy({ left: 260, behavior: 'smooth' });
		});

		group.appendChild(help);
		slider.appendChild(previous);
		slider.appendChild(gallery);
		slider.appendChild(next);
		group.appendChild(slider);
		render();
	}

	ready(function () {
		var title = document.getElementById('jstitle');
		var editor = document.getElementById('jseditor');
		var toolbar = document.getElementById('jseditorToolbar');

		if (window.location.search.indexOf('hyttehub-cabin=1') !== -1 && title) {
			title.placeholder = 'Cabin name';
		}
		if (window.location.search.indexOf('hyttehub-cabin=1') !== -1 && editor && !editor.value) {
			editor.placeholder = 'Write the full cabin description here.';
		}
		if (window.location.search.indexOf('hyttehub-cabin=1') !== -1 && toolbar && !document.getElementById('hyttehub-admin-note')) {
			var note = document.createElement('div');
			note.id = 'hyttehub-admin-note';
			note.className = 'alert alert-info mb-2';
			note.innerHTML = '<strong>New cabin:</strong> add the cabin photo in Options > General, write the description, then fill price, location, guests, bedrooms, amenities, inside photos, and owner email below the editor.';
			toolbar.parentNode.insertBefore(note, toolbar.nextSibling);
		}

		addInteriorGallery();

		document.addEventListener('click', function (event) {
			if (!hyttehubInteriorInput) {
				return;
			}
			var modal = document.getElementById('jsmediaManagerModal');
			if (!modal || !modal.contains(event.target)) {
				return;
			}
			var url = mediaUrlFromClick(event.target);
			if (!url) {
				return;
			}
			event.preventDefault();
			event.stopPropagation();
			event.stopImmediatePropagation();
			addImageToInteriorGallery(url);
		}, true);
	});

	var originalEditorInsertMedia = window.editorInsertMedia;
	window.editorInsertMedia = function (filename) {
		if (addImageToInteriorGallery(filename)) {
			return;
		}
		if (typeof originalEditorInsertMedia === 'function') {
			return originalEditorInsertMedia.apply(this, arguments);
		}
	};
})();
</script>
<style>
.hyttehub-interior-gallery {
	display: flex;
	flex-wrap: nowrap;
	gap: 10px;
	overflow-x: auto;
	padding: 4px 0 10px;
	scroll-snap-type: x mandatory;
	scrollbar-width: thin;
}

.hyttehub-gallery-slider {
	display: grid;
	grid-template-columns: 34px minmax(0, 1fr) 34px;
	align-items: center;
	gap: 8px;
}

.hyttehub-gallery-nav {
	width: 34px;
	height: 34px;
	border: 1px solid #d8dee4;
	border-radius: 50%;
	background: #fff;
	color: #425466;
	font-size: 24px;
	line-height: 1;
	cursor: pointer;
}

.hyttehub-gallery-nav:hover {
	background: #eef4ff;
	border-color: #80a8ff;
}

.hyttehub-photo-tile,
.hyttehub-add-photo {
	position: relative;
	flex: 0 0 112px;
	width: 112px;
	height: 112px;
	border-radius: 8px;
	overflow: hidden;
	border: 1px solid #d8dee4;
	background: #f6f8fa;
	scroll-snap-align: start;
}

.hyttehub-photo-tile img {
	width: 100%;
	height: 100%;
	object-fit: cover;
}

.hyttehub-add-photo {
	display: grid;
	place-items: center;
	color: #425466;
	font-size: 44px;
	line-height: 1;
	cursor: pointer;
}

.hyttehub-add-photo:hover {
	background: #eef4ff;
	border-color: #80a8ff;
}

.hyttehub-remove-photo {
	position: absolute;
	top: 5px;
	right: 5px;
	width: 26px;
	height: 26px;
	border: 0;
	border-radius: 50%;
	background: rgba(0, 0, 0, 0.72);
	color: #fff;
	font-size: 20px;
	line-height: 22px;
	cursor: pointer;
}
</style>
HTML;

		return $script;
	}
}
